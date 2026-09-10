<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAnalysis extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'required_skills' => 'array',
            'preferred_skills' => 'array',
            'technologies' => 'array',
            'experience_requirements' => 'array',
            'education_requirements' => 'array',
            'responsibilities' => 'array',
            'keywords' => 'array',
            'soft_skills' => 'array',
            'raw_response' => 'array',
            'analyzed_at' => 'datetime',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
