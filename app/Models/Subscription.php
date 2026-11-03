<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $guarded = [];

    protected static function booted(): void
    {
        static::saving(function (Subscription $subscription): void {
            $attributes = $subscription->getAttributes();
            if (! array_key_exists('expiry_at', $attributes) && ! array_key_exists('generated_images', $attributes)) {
                return;
            }

            $remote = request()->attributes->get('legacy_cmp_subscription', []);
            $subscription->metadata = [
                ...($subscription->metadata ?? []),
                'remote_id' => data_get($remote, 'id'),
                'remote_plan_id' => data_get($remote, 'plan_id', $attributes['plan_id'] ?? null),
                'remote_status' => data_get($remote, 'status', 'active'),
            ];
            $subscription->plan_id = null;
            $subscription->status = in_array($attributes['status'] ?? null, [1, '1', true, 'active', 'trialing'], true) ? 'active' : 'cancelled';
            $subscription->renews_at = $attributes['expiry_at'] ?? null;
            foreach (['expiry_at', 'generated_images', 'about_to_expire_reminder', 'expired_reminder', 'is_viewed'] as $attribute) {
                unset($subscription->$attribute);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'renews_at' => 'datetime',
            'ends_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }
}
