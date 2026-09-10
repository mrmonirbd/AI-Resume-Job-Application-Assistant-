<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobApplication extends Model
{
    use SoftDeletes;

    protected $table = 'applications';

    protected $guarded = [];

    public const STAGES = ['saved', 'preparing', 'applied', 'interview', 'offer', 'rejected', 'archived'];

    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
            'interview_at' => 'datetime',
            'activity_timeline' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobPosting::class, 'job_id');
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function tailoredResume(): BelongsTo
    {
        return $this->belongsTo(TailoredResume::class);
    }
}
