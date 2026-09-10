<?php

namespace App\Services\AI;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DeepSeekProvider implements AIProviderInterface
{
    public function complete(array $messages, array $options = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('DeepSeek API key is not configured.');
        }

        $response = Http::withToken((string) config('services.deepseek.key'))
            ->acceptJson()
            ->timeout((int) config('services.deepseek.timeout', 30))
            ->retry(
                (int) config('services.deepseek.retries', 2),
                (int) config('services.deepseek.retry_sleep_ms', 350),
                throw: false
            )
            ->post(rtrim((string) config('services.deepseek.base_url'), '/').'/chat/completions', [
                'model' => $options['model'] ?? config('services.deepseek.model', 'deepseek-chat'),
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.2,
                'response_format' => $options['json'] ?? false ? ['type' => 'json_object'] : null,
            ]);

        if (! $response->successful()) {
            throw new ConnectionException('DeepSeek request failed with status '.$response->status().'.');
        }

        $payload = $response->json();
        $content = data_get($payload, 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('DeepSeek returned an empty response.');
        }

        return [
            'content' => $content,
            'model' => data_get($payload, 'model'),
            'usage' => [
                'prompt_tokens' => (int) data_get($payload, 'usage.prompt_tokens', 0),
                'completion_tokens' => (int) data_get($payload, 'usage.completion_tokens', 0),
                'total_tokens' => (int) data_get($payload, 'usage.total_tokens', 0),
            ],
        ];
    }

    public function isConfigured(): bool
    {
        return filled(config('services.deepseek.key'));
    }

    public function name(): string
    {
        return 'deepseek';
    }
}
