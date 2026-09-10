<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resume extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_master' => 'boolean',
            'settings' => 'array',
            'last_edited_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ResumeTemplate::class, 'template_id');
    }

    public function sourceResume(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_resume_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ResumeSection::class)->orderBy('sort_order');
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class)->orderBy('sort_order');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class)->orderBy('sort_order');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class)->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class)->orderBy('sort_order');
    }

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class)->orderBy('sort_order');
    }

    public function languages(): HasMany
    {
        return $this->hasMany(Language::class)->orderBy('sort_order');
    }

    public function awards(): HasMany
    {
        return $this->hasMany(Award::class)->orderBy('sort_order');
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class)->orderBy('sort_order');
    }

    public function volunteerExperiences(): HasMany
    {
        return $this->hasMany(VolunteerExperience::class)->orderBy('sort_order');
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class)->orderBy('sort_order');
    }

    public function customSections(): HasMany
    {
        return $this->hasMany(CustomSection::class)->orderBy('sort_order');
    }
}
