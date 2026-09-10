<?php

namespace App\Actions\AI;

use App\Models\CoverLetter;
use App\Models\JobPosting;
use App\Models\Resume;
use App\Services\AI\LocalCareerAdvisor;
use App\Services\DeepSeekService;
use App\Services\ResumeSerializer;

class GenerateCoverLetter
{
    public function __construct(
        private readonly DeepSeekService $deepSeek,
        private readonly LocalCareerAdvisor $local,
        private readonly ResumeSerializer $serializer
    ) {}

    public function handle(Resume $resume, JobPosting $job, string $tone = 'professional', string $length = 'standard'): CoverLetter
    {
        $system = <<<'PROMPT'
You write factual cover letters from verified resume data and a job description. Return strict JSON with title, content, tone, length, factuality_note.
Do not invent achievements, employers, degrees, skills, dates, or metrics. If a fact is missing, keep the wording general and truthful.
PROMPT;

        $user = "Tone: {$tone}\nLength: {$length}\nVerified resume:\n{$this->serializer->toText($resume)}\n\nJob:\n{$job->description}";

        $payload = $this->deepSeek->structured(
            'cover_letter',
            $system,
            $user,
            fn () => $this->local->coverLetter($resume, $job, $tone, $length),
            $job->user
        );

        $application = $job->exists ? $job->applications()->latest()->first() : null;

        return CoverLetter::create([
            'user_id' => $job->user_id,
            'job_id' => $job->id,
            'resume_id' => $resume->id,
            'application_id' => $application?->id,
            'title' => $payload['title'] ?? "{$job->company} {$job->title} Cover Letter",
            'tone' => $payload['tone'] ?? $tone,
            'length' => $payload['length'] ?? $length,
            'content' => $payload['content'] ?? '',
            'status' => 'draft',
            'generated_by_ai' => true,
        ]);
    }
}
