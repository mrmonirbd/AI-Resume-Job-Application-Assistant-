<?php

namespace App\Services;

use App\Models\User;
use App\Services\AI\AIManager;
use Closure;

class DeepSeekService
{
    public function __construct(private readonly AIManager $ai) {}

    /**
     * Compatibility facade for DeepSeek-backed structured JSON requests.
     *
     * @param  Closure(): array<string, mixed>  $fallback
     * @return array<string, mixed>
     */
    public function structured(string $feature, string $systemPrompt, string $userPrompt, Closure $fallback, ?User $user = null): array
    {
        return $this->ai->json($feature, $systemPrompt, $userPrompt, $fallback, $user);
    }
}
