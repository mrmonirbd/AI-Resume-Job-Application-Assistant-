<?php

namespace App\Services;

use App\Models\CoverLetter;
use App\Models\Resume;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfExportService
{
    public function resume(Resume $resume): Response
    {
        $resume->loadMissing(['user', 'template', 'experiences', 'educations', 'skills', 'projects', 'certifications', 'languages', 'links']);

        return Pdf::loadView('pdf.resume', ['resume' => $resume])
            ->setPaper('letter')
            ->download(str($resume->title)->slug().'.pdf');
    }

    public function coverLetter(CoverLetter $coverLetter): Response
    {
        $coverLetter->loadMissing(['user', 'job']);

        return Pdf::loadView('pdf.cover-letter', ['coverLetter' => $coverLetter])
            ->setPaper('letter')
            ->download(str($coverLetter->title)->slug().'.pdf');
    }
}
