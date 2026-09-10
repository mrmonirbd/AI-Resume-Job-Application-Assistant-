<?php

namespace App\Actions\AI;

use App\Models\JobPosting;
use App\Models\Resume;
use App\Models\ResumeJobMatch;
use App\Services\AI\LocalCareerAdvisor;
use App\Services\DeepSeekService;
use App\Services\ResumeSerializer;

class CompareResumeToJob
{
    public function __construct(
        private readonly DeepSeekService $deepSeek,
        private readonly LocalCareerAdvisor $local,
        private readonly ResumeSerializer $serializer
    ) {}

    public function handle(Resume $resume, JobPosting $job): ResumeJobMatch
    {
        $analysis = $job->analysis;
        $resumeText = $this->serializer->toText($resume);

        $system = <<<'PROMPT'
You compare a verified resume against a job description. Return strict JSON with overall_score, skills_score, experience_score, education_score, keyword_score, matched_skills, missing_skills, recommendations, disclaimer.
Do not claim this is an official employer ATS score. Do not invent user skills or achievements.
PROMPT;

        $user = "Verified resume:\n{$resumeText}\n\nJob:\n{$job->description}\n\nExisting job analysis:\n".json_encode($analysis?->raw_response ?? [], JSON_PRETTY_PRINT);

        $payload = $this->deepSeek->structured(
            'resume_job_match',
            $system,
            $user,
            fn () => $this->local->matchResume($resume, $job, $analysis),
            $job->user
        );

        return ResumeJobMatch::create([
            'user_id' => $job->user_id,
            'resume_id' => $resume->id,
            'job_id' => $job->id,
            'overall_score' => $this->score($payload['overall_score'] ?? 0),
            'skills_score' => $this->score($payload['skills_score'] ?? 0),
            'experience_score' => $this->score($payload['experience_score'] ?? 0),
            'education_score' => $this->score($payload['education_score'] ?? 0),
            'keyword_score' => $this->score($payload['keyword_score'] ?? 0),
            'matched_skills' => $payload['matched_skills'] ?? [],
            'missing_skills' => $payload['missing_skills'] ?? [],
            'recommendations' => $payload['recommendations'] ?? [],
            'disclaimer' => $payload['disclaimer'] ?? 'This score is an internal compatibility estimate, not an official employer ATS score or guarantee of interview selection.',
            'raw_response' => $payload,
            'analyzed_at' => now(),
        ]);
    }

    private function score(mixed $value): int
    {
        return max(0, min(100, (int) $value));
    }
}
