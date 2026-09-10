<?php

namespace App\Actions\AI;

use App\Models\Resume;
use App\Services\AI\LocalCareerAdvisor;
use App\Services\DeepSeekService;
use App\Services\ResumeSerializer;
use Illuminate\Support\Str;

class CheckAtsResume
{
    public function __construct(
        private readonly DeepSeekService $deepSeek,
        private readonly LocalCareerAdvisor $local,
        private readonly ResumeSerializer $serializer
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function handle(Resume $resume, ?string $jobDescription = null): array
    {
        $system = <<<'PROMPT'
You evaluate resume readiness for common parsing and content quality issues. Return strict JSON with readiness_score, categories, recommendations, disclaimer.
Do not claim this is an actual proprietary ATS score.
PROMPT;

        $user = "Resume:\n{$this->serializer->toText($resume)}\n\nOptional job description:\n".($jobDescription ?: 'None');

        $payload = $this->deepSeek->structured(
            'ats_checker',
            $system,
            $user,
            fn () => $this->local->atsCheck($resume, $jobDescription),
            $resume->user
        );

        return $this->normalizePayload($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{readiness_score: int, categories: array<int, array{label: string, score: int}>, recommendations: array<int, string>, disclaimer: string, _provider?: mixed}
     */
    private function normalizePayload(array $payload): array
    {
        $categories = $this->normalizeCategories($payload['categories'] ?? []);
        $readinessScore = $this->score($payload['readiness_score'] ?? $payload['resume_readiness'] ?? $payload['score'] ?? null);

        if ($readinessScore === 0 && $categories !== []) {
            $readinessScore = (int) round(collect($categories)->avg('score'));
        }

        return array_filter([
            'readiness_score' => $readinessScore,
            'categories' => $categories,
            'recommendations' => $this->normalizeList($payload['recommendations'] ?? $payload['issues'] ?? []),
            'disclaimer' => $this->stringValue($payload['disclaimer'] ?? null, 'This readiness score is not an actual proprietary ATS score.'),
            '_provider' => $payload['_provider'] ?? null,
        ], fn ($value, string $key): bool => $key !== '_provider' || filled($value), ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @return array<int, array{label: string, score: int}>
     */
    private function normalizeCategories(mixed $categories): array
    {
        if (! is_array($categories)) {
            return [];
        }

        return collect($categories)
            ->map(function (mixed $value, int|string $key): array {
                if (is_array($value)) {
                    $label = $value['label'] ?? $value['name'] ?? $value['category'] ?? $value['title'] ?? $key;
                    $score = $value['score'] ?? $value['value'] ?? $value['percentage'] ?? $value['rating'] ?? 0;
                } else {
                    $label = $key;
                    $score = $value;
                }

                $label = is_numeric($label) ? 'Category '.((int) $label + 1) : (string) $label;

                return [
                    'label' => Str::of($label)->replace(['_', '-'], ' ')->headline()->toString(),
                    'score' => $this->score($score),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function normalizeList(mixed $items): array
    {
        if (! is_array($items)) {
            $items = [$items];
        }

        return collect($items)
            ->map(fn (mixed $item): string => $this->stringValue($item))
            ->filter()
            ->values()
            ->all();
    }

    private function score(mixed $value): int
    {
        if (is_array($value)) {
            $value = $value['score'] ?? $value['value'] ?? $value['percentage'] ?? $value['rating'] ?? $value['readiness_score'] ?? 0;
        }

        if (is_string($value) && preg_match('/\d+(?:\.\d+)?/', $value, $matches)) {
            $value = $matches[0];
        }

        return max(0, min(100, (int) round((float) $value)));
    }

    private function stringValue(mixed $value, string $fallback = ''): string
    {
        if (is_string($value) || is_numeric($value)) {
            return trim((string) $value);
        }

        if (is_array($value)) {
            foreach (['text', 'message', 'recommendation', 'description', 'summary'] as $key) {
                if (array_key_exists($key, $value)) {
                    return $this->stringValue($value[$key], $fallback);
                }
            }

            return collect($value)
                ->flatten()
                ->filter(fn (mixed $item): bool => is_string($item) || is_numeric($item))
                ->map(fn (mixed $item): string => trim((string) $item))
                ->filter()
                ->join(' ');
        }

        return $fallback;
    }
}
