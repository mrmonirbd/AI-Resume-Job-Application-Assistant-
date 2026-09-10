<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResumeJobMatch extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'matched_skills' => 'array',
            'missing_skills' => 'array',
            'recommendations' => 'array',
            'raw_response' => 'array',
            'analyzed_at' => 'datetime',
        ];
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
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
