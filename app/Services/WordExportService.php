<?php

namespace App\Services;

use App\Models\Resume;
use Illuminate\Http\Response;
use RuntimeException;
use ZipArchive;

class WordExportService
{
    public function resume(Resume $resume): Response
    {
        $resume->loadMissing([
            'user', 'template', 'experiences', 'educations', 'skills', 'projects', 'certifications',
            'languages', 'links',
        ]);

        $temporaryDocumentPath = tempnam(sys_get_temp_dir(), 'resume-word-');

        if ($temporaryDocumentPath === false) {
            throw new RuntimeException('Unable to prepare the Word export.');
        }

        try {
            $archive = new ZipArchive;

            if ($archive->open($temporaryDocumentPath, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Unable to create the Word export.');
            }

            $archive->addFromString('[Content_Types].xml', $this->contentTypesXml());
            $archive->addFromString('_rels/.rels', $this->relationshipsXml());
            $archive->addFromString('docProps/app.xml', $this->appPropertiesXml());
            $archive->addFromString('docProps/core.xml', $this->corePropertiesXml());
            $archive->addFromString('word/document.xml', $this->documentXml($resume));
            $archive->addFromString('word/_rels/document.xml.rels', $this->documentRelationshipsXml());
            $archive->addFromString('word/styles.xml', $this->stylesXml());
            $archive->close();

            $document = file_get_contents($temporaryDocumentPath);

            if ($document === false) {
                throw new RuntimeException('Unable to read the Word export.');
            }

            $filename = str($resume->title)->slug()->append('.docx')->toString();

            return response($document, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        } finally {
            @unlink($temporaryDocumentPath);
        }
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            .'</Types>';
    }

    private function relationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            .'</Relationships>';
    }

    private function documentRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function appPropertiesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
            .'<Application>Resume Assistant</Application></Properties>';
    }

    private function corePropertiesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/">'
            .'<dc:title>Resume</dc:title><dc:creator>Resume Assistant</dc:creator></cp:coreProperties>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Arial" w:hAnsi="Arial"/></w:rPr></w:rPrDefault></w:docDefaults>'
            .'</w:styles>';
    }

    private function documentXml(Resume $resume): string
    {
        $accent = strtoupper(ltrim($resume->accent_color ?: '#0f766e', '#'));
        $settings = $resume->template?->settings ?? [];
        $dark = strtoupper(ltrim(data_get($settings, 'dark', '#1f2937'), '#'));
        $title = $resume->professional_title ?: $resume->user->professional_title;
        $contact = collect([$resume->user->email, $resume->user->location])->filter()->join(' | ');
        $sidebar = [
            $this->paragraph($resume->user->name, ['bold' => true, 'color' => 'FFFFFF', 'size' => 28, 'spaceAfter' => 100]),
            $this->paragraph($title, ['bold' => true, 'color' => 'E5E7EB', 'spaceAfter' => 280]),
            $this->heading('Contact', 'D1D5DB'),
            $this->paragraph($contact, ['color' => 'D1D5DB', 'spaceAfter' => 280]),
        ];

        if ($resume->skills->isNotEmpty()) {
            $sidebar[] = $this->heading('Skills', 'D1D5DB');
            $sidebar[] = $this->paragraph($resume->skills->pluck('name')->join(' | '), ['color' => 'FFFFFF', 'spaceAfter' => 280]);
        }

        if ($resume->educations->isNotEmpty()) {
            $sidebar[] = $this->heading('Education', 'D1D5DB');

            foreach ($resume->educations as $education) {
                $sidebar[] = $this->paragraph($education->school, ['bold' => true, 'color' => 'FFFFFF']);
                $sidebar[] = $this->paragraph(trim($education->degree.' '.$education->field), ['color' => 'D1D5DB', 'spaceAfter' => 170]);
            }
        }

        if ($resume->languages->isNotEmpty()) {
            $sidebar[] = $this->heading('Languages', 'D1D5DB');
            $sidebar[] = $this->paragraph($resume->languages->map(fn ($language): string => $language->name.($language->proficiency ? ' ('.$language->proficiency.')' : ''))->join(' | '), ['color' => 'FFFFFF']);
        }

        $main = [];

        if ($resume->professional_summary) {
            $main[] = $this->heading('Professional Summary', $accent);
            $main[] = $this->paragraph($resume->professional_summary, ['spaceAfter' => 220]);
        }

        if ($resume->experiences->isNotEmpty()) {
            $main[] = $this->heading('Work Experience', $accent);

            foreach ($resume->experiences as $experience) {
                $main[] = $this->paragraph(trim($experience->position.($experience->company ? ' - '.$experience->company : '')), ['bold' => true]);
                $main[] = $this->paragraph($this->experienceMeta($experience), ['color' => '6B7280']);

                if ($experience->description) {
                    $main[] = $this->paragraph($experience->description);
                }

                foreach ($experience->bullets ?? [] as $bullet) {
                    $main[] = $this->bullet($bullet);
                }

                $main[] = $this->paragraph('', ['spaceAfter' => 140]);
            }
        }

        if ($resume->projects->isNotEmpty()) {
            $main[] = $this->heading('Projects', $accent);

            foreach ($resume->projects as $project) {
                $main[] = $this->paragraph(trim($project->name.($project->role ? ' - '.$project->role : '')), ['bold' => true]);
                $main[] = $this->paragraph($project->description, ['spaceAfter' => 140]);
            }
        }

        if ($resume->certifications->isNotEmpty()) {
            $main[] = $this->heading('Certifications', $accent);

            foreach ($resume->certifications as $certification) {
                $main[] = $this->paragraph(trim($certification->name.($certification->issuer ? ' - '.$certification->issuer : '')), ['bold' => true]);
            }
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'
            .'<w:tbl><w:tblPr><w:tblW w:w="10466" w:type="dxa"/><w:tblLayout w:type="fixed"/>'
            .'<w:tblBorders><w:top w:val="nil"/><w:left w:val="nil"/><w:bottom w:val="nil"/><w:right w:val="nil"/><w:insideH w:val="nil"/><w:insideV w:val="nil"/></w:tblBorders>'
            .'</w:tblPr><w:tblGrid><w:gridCol w:w="3300"/><w:gridCol w:w="7166"/></w:tblGrid><w:tr>'
            .'<w:tc><w:tcPr><w:tcW w:w="3300" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="'.$dark.'"/>'
            .'<w:tcMar><w:top w:w="360" w:type="dxa"/><w:left w:w="360" w:type="dxa"/><w:bottom w:w="360" w:type="dxa"/><w:right w:w="360" w:type="dxa"/></w:tcMar></w:tcPr>'
            .implode('', $sidebar).'</w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="7166" w:type="dxa"/>'
            .'<w:tcMar><w:top w:w="360" w:type="dxa"/><w:left w:w="420" w:type="dxa"/><w:bottom w:w="360" w:type="dxa"/><w:right w:w="420" w:type="dxa"/></w:tcMar></w:tcPr>'
            .implode('', $main).'</w:tc>'
            .'</w:tr></w:tbl>'
            .'<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="720" w:right="720" w:bottom="720" w:left="720"/></w:sectPr>'
            .'</w:body></w:document>';
    }

    private function heading(string $text, string $color): string
    {
        return $this->paragraph(str($text)->upper()->toString(), [
            'bold' => true,
            'color' => $color,
            'size' => 18,
            'spaceBefore' => 180,
            'spaceAfter' => 90,
        ]);
    }

    private function paragraph(?string $text, array $options = []): string
    {
        $paragraphProperties = '';

        if (isset($options['spaceBefore']) || isset($options['spaceAfter'])) {
            $paragraphProperties .= '<w:spacing w:before="'.($options['spaceBefore'] ?? 0).'" w:after="'.($options['spaceAfter'] ?? 0).'"/>';
        }

        if (isset($options['indentLeft'])) {
            $paragraphProperties .= '<w:ind w:left="'.$options['indentLeft'].'" w:hanging="180"/>';
        }

        $runProperties = '';

        if (($options['bold'] ?? false) === true) {
            $runProperties .= '<w:b/>';
        }

        if (isset($options['color'])) {
            $runProperties .= '<w:color w:val="'.$options['color'].'"/>';
        }

        if (isset($options['size'])) {
            $runProperties .= '<w:sz w:val="'.$options['size'].'"/>';
        }

        return '<w:p>'
            .($paragraphProperties !== '' ? '<w:pPr>'.$paragraphProperties.'</w:pPr>' : '')
            .'<w:r>'.($runProperties !== '' ? '<w:rPr>'.$runProperties.'</w:rPr>' : '')
            .'<w:t xml:space="preserve">'.$this->escape($text ?? '').'</w:t></w:r></w:p>';
    }

    private function bullet(string $text): string
    {
        return $this->paragraph('• '.$text, ['indentLeft' => 360, 'spaceAfter' => 45]);
    }

    private function experienceMeta(object $experience): string
    {
        return collect([
            trim((string) $experience->start_date.' - '.($experience->end_date ?: 'Present')),
            $experience->location,
        ])->filter()->join(' | ');
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
