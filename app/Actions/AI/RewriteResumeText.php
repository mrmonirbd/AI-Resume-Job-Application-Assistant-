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
You are an inline resume writing assistant. Return strict JSON with verified_user_information, ai_rewrite, ai_recommendation. Each value must be a plain text JSON string, never an array or object.
Never invent companies, dates, achievements, metrics, skills, certifications, degrees, or responsibilities.
If a metric would help but is missing, write "Add a measurable result here if available."
PROMPT;

        $prompt = "Action: {$action}\nExisting verified text:\n{$text}\n\nOptional job description:\n".($jobDescription ?: 'None');

        $payload = $this->deepSeek->structured(
            'inline_resume_rewrite',
            $system,
            $prompt,
            fn () => $this->local->rewrite($text, $action),
            $user
        );

        return $this->normalize($payload, $text);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function normalize(array $payload, string $originalText): array
    {
        return [
            ...$payload,
            'verified_user_information' => $this->textValue($payload['verified_user_information'] ?? null) ?: $originalText,
            'ai_rewrite' => $this->textValue($payload['ai_rewrite'] ?? null) ?: $originalText,
            'ai_recommendation' => $this->textValue($payload['ai_recommendation'] ?? null) ?: 'Review this suggestion for factual accuracy before accepting it.',
        ];
    }

    private function textValue(mixed $value): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (! is_array($value)) {
            return '';
        }

        foreach (['text', 'content', 'summary', 'rewrite', 'message', 'recommendation', 'description', 'value'] as $key) {
            $text = $this->textValue($value[$key] ?? null);

            if ($text !== '') {
                return $text;
            }
        }

        if (! array_is_list($value)) {
            return '';
        }

        return collect($value)
            ->map(fn (mixed $item): string => $this->textValue($item))
            ->filter(fn (string $text): bool => $text !== '')
            ->implode("\n");
    }
}
