<?php

namespace App\Services\AI;

use App\Models\AiGeneration;
use App\Models\AiUsage;
use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class AIManager
{
    public function __construct(private readonly AIProviderInterface $provider) {}

    /**
     * @param  Closure(): array<string, mixed>  $fallback
     * @return array<string, mixed>
     */
    public function json(string $feature, string $systemPrompt, string $userPrompt, Closure $fallback, ?User $user = null): array
    {
        if (! $this->provider->isConfigured()) {
            $data = $fallback();
            $this->record($feature, $user, 'local', null, 'complete', $data, []);

            return $data + ['_provider' => 'local'];
        }

        try {
            $response = $this->provider->complete([
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ], ['json' => true]);

            $data = $this->decodeJson($response['content']);
            $this->record($feature, $user, $this->provider->name(), $response['model'], 'complete', $data, $response['usage']);

            return $data + ['_provider' => $this->provider->name()];
        } catch (Throwable $exception) {
            Log::warning('AI request failed; using local fallback.', [
                'feature' => $feature,
                'provider' => $this->provider->name(),
                'user_id' => $user?->id,
                'error' => $exception->getMessage(),
            ]);

            $data = $fallback();
            $this->record($feature, $user, $this->provider->name(), null, 'fallback', $data, [], $exception->getMessage());

            return $data + ['_provider' => 'local'];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $content): array
    {
        $content = trim($content);
        $content = preg_replace('/^```(?:json)?|```$/m', '', $content) ?: $content;

        $decoded = json_decode(trim($content), true);

        if (! is_array($decoded)) {
            throw new \RuntimeException('AI response was not valid JSON.');
        }

        return $decoded;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, int>  $usage
     */
    private function record(string $feature, ?User $user, string $provider, ?string $model, string $status, array $payload, array $usage, ?string $error = null): void
    {
        $promptTokens = (int) ($usage['prompt_tokens'] ?? 0);
        $completionTokens = (int) ($usage['completion_tokens'] ?? 0);
        $totalTokens = (int) ($usage['total_tokens'] ?? $promptTokens + $completionTokens);

        AiGeneration::create([
            'user_id' => $user?->id,
            'provider' => $provider,
            'feature' => $feature,
            'model' => $model,
            'prompt_hash' => Str::random(8),
            'request_summary' => 'Structured '.$feature.' request',
            'response_summary' => Str::limit(json_encode($payload, JSON_THROW_ON_ERROR), 1200),
            'input_tokens' => $promptTokens,
            'output_tokens' => $completionTokens,
            'total_tokens' => $totalTokens,
            'status' => $status,
            'metadata' => ['sensitive_content_logged' => false],
            'error_message' => $error,
            'completed_at' => now(),
        ]);

        $usageRow = AiUsage::query()
            ->where('user_id', $user?->id)
            ->whereDate('date', now()->toDateString())
            ->where('provider', $provider)
            ->where('feature', $feature)
            ->first();

        if (! $usageRow) {
            $usageRow = AiUsage::create([
                'user_id' => $user?->id,
                'date' => now()->toDateString(),
                'provider' => $provider,
                'feature' => $feature,
            ]);
        }

        $usageRow->forceFill([
            'requests' => $usageRow->requests + 1,
            'input_tokens' => $usageRow->input_tokens + $promptTokens,
            'output_tokens' => $usageRow->output_tokens + $completionTokens,
            'total_tokens' => $usageRow->total_tokens + $totalTokens,
        ])->save();
    }
}
