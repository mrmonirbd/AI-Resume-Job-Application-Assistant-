<?php

namespace App\Services\AI;

interface AIProviderInterface
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{content: string, model: string|null, usage: array<string, int>}
     */
    public function complete(array $messages, array $options = []): array;

    public function isConfigured(): bool;

    public function name(): string;
}
