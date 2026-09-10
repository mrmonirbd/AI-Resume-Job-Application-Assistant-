<?php

namespace App\Http\Controllers\App;

use App\Actions\AI\AnalyzeJobDescription;
use App\Actions\AI\GenerateCoverLetter;
use App\Actions\AI\RewriteResumeText;
use App\Http\Controllers\Controller;
use App\Models\AiTool;
use App\Models\JobPosting;
use App\Models\Resume;
use App\Services\AI\LocalCareerAdvisor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AiToolController extends Controller
{
    public function index(Request $request): View
    {
        return view('app.tools.index', [
            'tools' => AiTool::where('is_active', true)->orderBy('category')->orderBy('name')->get(),
            'resumes' => $request->user()->resumes()->where('status', 'active')->get(),
            'jobs' => $request->user()->jobs()->latest()->get(),
            'result' => null,
        ]);
    }

    public function run(Request $request, RewriteResumeText $rewrite, AnalyzeJobDescription $analyze, GenerateCoverLetter $coverLetter): View
    {
        $validated = $request->validate([
            'tool' => ['required', Rule::exists('ai_tools', 'slug')->where('is_active', true)],
            'text' => ['nullable', 'string', 'max:12000'],
            'job_id' => ['nullable', Rule::exists('jobs', 'id')->where('user_id', $request->user()->id)],
            'resume_id' => ['nullable', Rule::exists('resumes', 'id')->where('user_id', $request->user()->id)],
        ]);

        $result = match ($validated['tool']) {
            'job-description-analyzer' => $this->runJobAnalyzer($request, $validated, $analyze),
            'cover-letter-generator' => $this->runCoverLetter($request, $validated, $coverLetter),
            default => $rewrite->handle($request->user(), $validated['text'] ?? '', $validated['tool']),
        };

        return view('app.tools.index', [
            'tools' => AiTool::where('is_active', true)->orderBy('category')->orderBy('name')->get(),
            'resumes' => $request->user()->resumes()->where('status', 'active')->get(),
            'jobs' => $request->user()->jobs()->latest()->get(),
            'result' => $result,
            'selectedTool' => $validated['tool'],
            'selectedResumeId' => $validated['resume_id'] ?? null,
            'selectedJobId' => $validated['job_id'] ?? null,
            'inputText' => $validated['text'] ?? '',
        ]);
    }

    private function runJobAnalyzer(Request $request, array $validated, AnalyzeJobDescription $analyze): array
    {
        $job = ($validated['job_id'] ?? null)
            ? $request->user()->jobs()->whereKey($validated['job_id'])->firstOrFail()
            : JobPosting::make([
                'user_id' => $request->user()->id,
                'company' => 'Draft Company',
                'title' => 'Draft Role',
                'description' => $validated['text'] ?? '',
            ]);

        if (! $job->exists) {
            return app(LocalCareerAdvisor::class)->analyzeJob($job->description, $job->title);
        }

        return $analyze->handle($job)->raw_response;
    }

    private function runCoverLetter(Request $request, array $validated, GenerateCoverLetter $coverLetter): array
    {
        $resume = $this->selectedResume($request, $validated);

        if (! $resume) {
            throw ValidationException::withMessages([
                'resume_id' => 'Create or select an active resume before running the cover letter generator.',
            ]);
        }

        $job = $this->selectedJob($request, $validated);

        if (! $job && filled($validated['text'] ?? null)) {
            $job = JobPosting::make([
                'user_id' => $request->user()->id,
                'company' => 'Draft Company',
                'title' => 'Draft Role',
                'description' => $validated['text'],
            ])->setRelation('user', $request->user());
        }

        if (! $job) {
            throw ValidationException::withMessages([
                'text' => 'Paste a job description or select a saved job before running the cover letter generator.',
            ]);
        }

        $letter = $coverLetter->handle($resume, $job);

        return [
            'title' => $letter->title,
            'content' => $letter->content,
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function selectedResume(Request $request, array $validated): ?Resume
    {
        if ($validated['resume_id'] ?? null) {
            return $request->user()->resumes()->where('status', 'active')->whereKey($validated['resume_id'])->first();
        }

        return $request->user()
            ->resumes()
            ->where('status', 'active')
            ->orderByDesc('is_master')
            ->latest('last_edited_at')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function selectedJob(Request $request, array $validated): ?JobPosting
    {
        if (! ($validated['job_id'] ?? null)) {
            return null;
        }

        return $request->user()->jobs()->whereKey($validated['job_id'])->first();
    }
}
