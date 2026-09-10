<?php

namespace App\Actions\AI;

use App\Models\User;
use App\Services\AI\LocalCareerAdvisor;
use App\Services\DeepSeekService;

class RewriteResumeText
{
    public function __construct(
        private readonly DeepSeekService $deepSeek,
        private readonly LocalCareerAdvisor $local
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(User $user, string $text, string $action, ?string $jobDescription = null): array
    {
        $system = <<<'PROMPT'
You are an inline resume writing assistant. Return strict JSON with verified_user_information, ai_rewrite, ai_recommendation.
Never invent companies, dates, achievements, metrics, skills, certifications, degrees, or responsibilities.
If a metric would help but is missing, write "Add a measurable result here if available."
PROMPT;

        $prompt = "Action: {$action}\nExisting verified text:\n{$text}\n\nOptional job description:\n".($jobDescription ?: 'None');

        return $this->deepSeek->structured(
            'inline_resume_rewrite',
            $system,
            $prompt,
            fn () => $this->local->rewrite($text, $action),
            $user
        );
    }
}
