<?php

namespace App\Services;

use App\Models\Resume;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Process;
use RuntimeException;

class ImageExportService
{
    public function resume(Resume $resume): Response
    {
        $resume->loadMissing([
            'user', 'template', 'experiences', 'educations', 'skills', 'projects', 'certifications',
            'languages', 'links',
        ]);

        $temporaryPdfPath = tempnam(sys_get_temp_dir(), 'resume-export-');

        if ($temporaryPdfPath === false) {
            throw new RuntimeException('Unable to prepare the resume image export.');
        }

        $imagePrefix = $temporaryPdfPath.'-page';
        $imagePath = $imagePrefix.'-1.png';

        try {
            file_put_contents(
                $temporaryPdfPath,
                Pdf::loadView('pdf.resume', ['resume' => $resume])->setPaper('a4')->output()
            );

            Process::run(['pdftoppm', '-png', '-r', '150', $temporaryPdfPath, $imagePrefix])->throw();

            $image = file_get_contents($imagePath);

            if ($image === false) {
                throw new RuntimeException('Unable to create the resume image export.');
            }

            $filename = str($resume->title)->slug()->append('.png')->toString();

            return response($image, 200, [
                'Content-Type' => 'image/png',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        } finally {
            @unlink($temporaryPdfPath);
            @unlink($imagePath);
        }
    }
}
