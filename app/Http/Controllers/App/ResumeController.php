<?php

namespace App\Http\Controllers\App;

use App\Actions\Resumes\DuplicateResume;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResumeRequest;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use App\Services\PdfExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ResumeController extends Controller
{
    public function index(Request $request): View
    {
        return view('app.resumes.index', [
            'resumes' => $request->user()->resumes()->with('template')->latest('last_edited_at')->paginate(12),
            'masterResume' => $request->user()->masterResume()->first(),
        ]);
    }

    public function create(): View
    {
        return view('app.resumes.create', [
            'templates' => ResumeTemplate::where('is_active', true)->orderBy('category')->get(),
        ]);
    }

    public function store(StoreResumeRequest $request): RedirectResponse
    {
        $template = ResumeTemplate::find($request->input('template_id')) ?: ResumeTemplate::first();

        $resume = $request->user()->resumes()->create([
            ...$request->validated(),
            'template_id' => $template?->id,
            'type' => 'general',
            'accent_color' => $request->input('accent_color', $template?->accent_color ?? '#2563eb'),
            'font_family' => $request->input('font_family', $template?->font_family ?? 'Inter'),
            'spacing' => $request->input('spacing', 'comfortable'),
            'last_edited_at' => now(),
        ]);

        $this->createDefaultSections($resume);

        return to_route('resumes.edit', $resume)->with('status', 'Resume created.');
    }

    public function edit(Resume $resume): View
    {
        $this->authorize('view', $resume);

        return view('app.resumes.edit', [
            'resume' => $resume->load(['template', 'sections', 'experiences', 'educations', 'skills', 'projects', 'certifications', 'languages', 'links']),
            'templates' => ResumeTemplate::where('is_active', true)->get(),
        ]);
    }

    public function update(StoreResumeRequest $request, Resume $resume): RedirectResponse
    {
        $this->authorize('update', $resume);

        $resume->update([
            ...$request->validated(),
            'last_edited_at' => now(),
        ]);

        return back()->with('status', 'Resume settings saved.');
    }

    public function duplicate(Resume $resume, DuplicateResume $duplicate): RedirectResponse
    {
        $this->authorize('view', $resume);

        $copy = $duplicate->handle($resume->load([
            'sections', 'experiences', 'educations', 'skills', 'projects', 'certifications',
            'languages', 'awards', 'publications', 'volunteerExperiences', 'links', 'customSections',
        ]));

        return to_route('resumes.edit', $copy)->with('status', 'Resume duplicated.');
    }

    public function destroy(Resume $resume): RedirectResponse
    {
        $this->authorize('delete', $resume);

        $resume->delete();

        return to_route('resumes.index')->with('status', 'Resume archived.');
    }

    public function master(Request $request): RedirectResponse
    {
        $resume = $request->user()->masterResume()->first();

        if (! $resume) {
            $resume = $request->user()->resumes()->create([
                'title' => 'Master Resume',
                'type' => 'master',
                'is_master' => true,
                'last_edited_at' => now(),
            ]);

            $this->createDefaultSections($resume);
        }

        return to_route('resumes.edit', $resume);
    }

    public function export(Resume $resume, PdfExportService $pdf): Response
    {
        $this->authorize('view', $resume);

        return $pdf->resume($resume);
    }

    private function createDefaultSections(Resume $resume): void
    {
        $sections = [
            'personal' => 'Personal Information',
            'summary' => 'Professional Summary',
            'experience' => 'Work Experience',
            'education' => 'Education',
            'skills' => 'Skills',
            'projects' => 'Projects',
            'certifications' => 'Certifications',
            'languages' => 'Languages',
        ];

        foreach ($sections as $index => $title) {
            $resume->sections()->firstOrCreate(
                ['type' => $index],
                ['title' => $title, 'sort_order' => array_search($index, array_keys($sections), true)]
            );
        }
    }
}
