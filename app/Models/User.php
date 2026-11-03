<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'professional_title',
        'location',
        'career_goal',
        'is_admin',
        'onboarding_completed_at',
        'concept_id',
        'crm_customer_id',
        'crm_subscription_id',
        'crm_web_user_id',
        'domain_name',
        'crm_profile',
        'profile_photo_path',
        'customer_id',
        'subscription_id',
        'web_user_id',
        'firstname',
        'lastname',
        'plain_password',
    ];

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            $customer = request()->attributes->get('legacy_cmp_customer');
            if (is_array($customer) && filled($user->crm_customer_id)) {
                $user->crm_profile = [
                    ...($user->crm_profile ?? []),
                    'first_name' => (string) ($customer['first_name'] ?? ''),
                    'last_name' => (string) ($customer['last_name'] ?? ''),
                    'phone' => (string) ($customer['phone'] ?? ''),
                    'address' => (string) ($customer['address'] ?? $customer['address_1'] ?? ''),
                    'city' => (string) ($customer['city'] ?? ''),
                    'postcode' => (string) ($customer['postcode'] ?? $customer['zip'] ?? $customer['postal_code'] ?? ''),
                ];
                $user->email_verified_at ??= now();
                $user->onboarding_completed_at ??= now();
            }
        });
    }

    public function getCustomerIdAttribute(mixed $value): mixed
    {
        return $value ?? ($this->attributes['crm_customer_id'] ?? null);
    }

    public function setCustomerIdAttribute(mixed $value): void
    {
        $this->attributes['customer_id'] = $value;
        $this->attributes['crm_customer_id'] = $value;
    }

    public function setCrmCustomerIdAttribute(mixed $value): void
    {
        $this->attributes['customer_id'] = $value;
        $this->attributes['crm_customer_id'] = $value;
    }

    public function setSubscriptionIdAttribute(mixed $value): void
    {
        $this->attributes['crm_subscription_id'] = $value;
    }

    public function setWebUserIdAttribute(mixed $value): void
    {
        $this->attributes['crm_web_user_id'] = $value;
    }

    public function setFirstnameAttribute(mixed $value): void
    {
        $this->crm_profile = [...($this->crm_profile ?? []), 'first_name' => (string) $value];
    }

    public function setLastnameAttribute(mixed $value): void
    {
        $this->crm_profile = [...($this->crm_profile ?? []), 'last_name' => (string) $value];
    }

    /**
     * Legacy requests may contain this field; only the password hash is persisted.
     */
    public function setPlainPasswordAttribute(mixed $value): void
    {
        unset($this->attributes['plain_password']);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latest('id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'onboarding_completed_at' => 'datetime',
            'password' => 'hashed',
            'crm_profile' => 'array',
        ];
    }

    public function resumes(): HasMany
    {
        return $this->hasMany(Resume::class);
    }

    public function masterResume(): HasOne
    {
        return $this->hasOne(Resume::class)->where('is_master', true);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(JobPosting::class, 'user_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function coverLetters(): HasMany
    {
        return $this->hasMany(CoverLetter::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function concept(): BelongsTo
    {
        return $this->belongsTo(Concept::class);
    }
}
