<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\CoverLetter;
use App\Services\PdfExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CoverLetterController extends Controller
{
    public function index(Request $request): View
    {
        return view('app.cover-letters.index', [
            'coverLetters' => $request->user()->coverLetters()->with('job')->latest()->paginate(12),
        ]);
    }

    public function export(CoverLetter $coverLetter, PdfExportService $pdf): Response
    {
        $this->authorize('view', $coverLetter);

        return $pdf->coverLetter($coverLetter);
    }
}
