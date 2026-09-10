<?php

namespace App\Http\Controllers\App;

use App\Actions\AI\AnalyzeJobDescription;
use App\Actions\AI\CompareResumeToJob;
use App\Actions\AI\GenerateCoverLetter;
use App\Actions\AI\TailorResumeForJob;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJobRequest;
use App\Models\JobPosting;
use App\Models\Resume;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        return view('app.jobs.index', [
            'jobs' => $request->user()->jobs()->with(['analysis', 'matches', 'applications'])->latest()->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('app.jobs.create');
    }

    public function store(StoreJobRequest $request, AnalyzeJobDescription $analyze): RedirectResponse
    {
        $job = $request->user()->jobs()->create([
            ...$request->validated(),
            'stage' => 'saved',
            'status' => 'saved',
        ]);

        $request->user()->applications()->create([
            'job_id' => $job->id,
            'status' => 'saved',
            'activity_timeline' => [
                ['label' => 'Job saved', 'at' => now()->toDateTimeString()],
            ],
        ]);

        $analyze->handle($job);

        return to_route('jobs.show', $job)->with('status', 'Job saved and analyzed.');
    }

    public function show(JobPosting $job): View
    {
        $this->authorize('view', $job);

        return view('app.jobs.show', [
            'job' => $job->load(['analysis', 'matches.resume', 'applications', 'coverLetters']),
            'resumes' => $job->user->resumes()->where('status', 'active')->latest('last_edited_at')->get(),
            'masterResume' => $job->user->masterResume()->first(),
        ]);
    }

    public function update(StoreJobRequest $request, JobPosting $job): RedirectResponse
    {
        $this->authorize('update', $job);

        $job->update($request->validated());

        return back()->with('status', 'Job updated.');
    }

    public function analyze(JobPosting $job, AnalyzeJobDescription $analyze): RedirectResponse
    {
        $this->authorize('update', $job);

        $analyze->handle($job);

        return back()->with('status', 'Job analysis refreshed.');
    }

    public function match(Request $request, JobPosting $job, CompareResumeToJob $compare): RedirectResponse
    {
        $this->authorize('update', $job);

        $validated = $request->validate([
            'resume_id' => ['nullable', 'exists:resumes,id'],
        ]);

        $resume = $this->selectedResume($job, $validated['resume_id'] ?? null);
        $compare->handle($resume, $job);

        return back()->with('status', 'Resume match analysis completed.');
    }

    public function tailor(Request $request, JobPosting $job, TailorResumeForJob $tailor): RedirectResponse
    {
        $this->authorize('update', $job);

        $validated = $request->validate([
            'resume_id' => ['nullable', 'exists:resumes,id'],
        ]);

        $resume = $this->selectedResume($job, $validated['resume_id'] ?? null);
        $tailored = $tailor->handle($resume, $job);

        $application = $job->applications()->latest()->first();
        $application?->update([
            'resume_id' => $tailored->resume_id,
            'tailored_resume_id' => $tailored->id,
            'status' => 'preparing',
            'activity_timeline' => [
                ...($application->activity_timeline ?? []),
                ['label' => 'Tailored resume created', 'at' => now()->toDateTimeString()],
            ],
        ]);

        return to_route('resumes.edit', $tailored->resume)->with('status', 'Tailored resume created for review.');
    }

    public function coverLetter(Request $request, JobPosting $job, GenerateCoverLetter $generate): RedirectResponse
    {
        $this->authorize('update', $job);

        $validated = $request->validate([
            'resume_id' => ['nullable', 'exists:resumes,id'],
            'tone' => ['required', 'in:professional,confident,friendly,executive'],
            'length' => ['required', 'in:short,standard,detailed'],
        ]);

        $resume = $this->selectedResume($job, $validated['resume_id'] ?? null);
        $letter = $generate->handle($resume, $job, $validated['tone'], $validated['length']);

        $job->applications()->latest()->first()?->update([
            'resume_id' => $resume->id,
            'status' => 'preparing',
        ]);

        return back()->with('status', 'Cover letter generated.')->with('cover_letter_id', $letter->id);
    }

    public function destroy(JobPosting $job): RedirectResponse
    {
        $this->authorize('delete', $job);

        $job->delete();

        return to_route('jobs.index')->with('status', 'Job archived.');
    }

    private function selectedResume(JobPosting $job, ?int $resumeId): Resume
    {
        $query = $job->user->resumes()->where('status', 'active');

        if ($resumeId) {
            $resume = (clone $query)->whereKey($resumeId)->firstOrFail();

            return $resume;
        }

        return $job->user->masterResume()->firstOrCreate(
            ['is_master' => true],
            [
                'title' => 'Master Resume',
                'type' => 'master',
                'last_edited_at' => now(),
            ]
        );
    }
}
