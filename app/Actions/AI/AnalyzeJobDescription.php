<?php

namespace App\Actions\AI;

use App\Models\JobAnalysis;
use App\Models\JobPosting;
use App\Services\AI\LocalCareerAdvisor;
use App\Services\DeepSeekService;

class AnalyzeJobDescription
{
    public function __construct(
        private readonly DeepSeekService $deepSeek,
        private readonly LocalCareerAdvisor $local
    ) {}

    public function handle(JobPosting $job): JobAnalysis
    {
        $system = $this->systemPrompt();
        $user = "Job title: {$job->title}\nCompany: {$job->company}\nJob description:\n{$job->description}";

        $payload = $this->deepSeek->structured(
            'job_description_analyzer',
            $system,
            $user,
            fn () => $this->local->analyzeJob($job->description, $job->title),
            $job->user
        );

        return JobAnalysis::updateOrCreate(
            ['job_id' => $job->id, 'user_id' => $job->user_id],
            [
                'status' => 'complete',
                'extracted_title' => $payload['extracted_title'] ?? $job->title,
                'seniority' => $payload['seniority'] ?? null,
                'required_skills' => $payload['required_skills'] ?? [],
                'preferred_skills' => $payload['preferred_skills'] ?? [],
                'technologies' => $payload['technologies'] ?? [],
                'experience_requirements' => $payload['experience_requirements'] ?? [],
                'education_requirements' => $payload['education_requirements'] ?? [],
                'responsibilities' => $payload['responsibilities'] ?? [],
                'keywords' => $payload['keywords'] ?? [],
                'soft_skills' => $payload['soft_skills'] ?? [],
                'raw_response' => $payload,
                'analyzed_at' => now(),
            ]
        );
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You extract structured job requirements for a resume tailoring app. Return strict JSON with:
extracted_title, seniority, required_skills, preferred_skills, technologies, experience_requirements, education_requirements, responsibilities, keywords, soft_skills.
Never include commentary outside JSON.
PROMPT;
    }
}
