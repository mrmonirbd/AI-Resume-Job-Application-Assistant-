<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TailoredResume extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'ai_summary' => 'array',
            'suggested_missing_information' => 'array',
            'approved_at' => 'datetime',
        ];
    }

    public function sourceResume(): BelongsTo
    {
        return $this->belongsTo(Resume::class, 'source_resume_id');
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(ResumeJobMatch::class, 'resume_job_match_id');
    }
}
