<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPosting extends Model
{
    use SoftDeletes;

    protected $table = 'jobs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
            'deadline_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analysis(): HasOne
    {
        return $this->hasOne(JobAnalysis::class, 'job_id')->latestOfMany();
    }

    public function matches(): HasMany
    {
        return $this->hasMany(ResumeJobMatch::class, 'job_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'job_id');
    }

    public function coverLetters(): HasMany
    {
        return $this->hasMany(CoverLetter::class, 'job_id');
    }
}
