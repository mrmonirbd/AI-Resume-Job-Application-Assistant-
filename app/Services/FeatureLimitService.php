<?php

namespace App\Services;

use App\Models\User;

class FeatureLimitService
{
    public function canUse(User $user, string $feature): bool
    {
        $plan = $user->subscriptions()->with('plan')->latest()->first()?->plan;

        if (! $plan) {
            return in_array($feature, ['master_resume', 'basic_templates', 'ats_checker'], true);
        }

        return in_array($feature, $plan->features ?? [], true);
    }

    public function limitFor(User $user, string $limit): ?int
    {
        $plan = $user->subscriptions()->with('plan')->latest()->first()?->plan;

        return $plan ? data_get($plan->limits, $limit) : ['ai_generations_per_month' => 10, 'pdf_exports_per_month' => 3][$limit] ?? null;
    }
}
