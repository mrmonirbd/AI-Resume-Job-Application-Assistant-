<?php

namespace App\Actions\Resumes;

use App\Models\Resume;
use Illuminate\Support\Facades\DB;

class DuplicateResume
{
    public function handle(Resume $resume, array $overrides = []): Resume
    {
        return DB::transaction(function () use ($resume, $overrides) {
            $copy = $resume->replicate([
                'is_master',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);

            $copy->fill(array_merge([
                'title' => $resume->title.' Copy',
                'is_master' => false,
                'type' => 'general',
                'source_resume_id' => $resume->id,
                'last_edited_at' => now(),
            ], $overrides));
            $copy->save();

            foreach ($resume->sections as $section) {
                $clone = $section->replicate(['created_at', 'updated_at']);
                $clone->resume_id = $copy->id;
                $clone->save();
            }

            foreach (['experiences', 'educations', 'skills', 'projects', 'certifications', 'languages', 'awards', 'publications', 'volunteerExperiences', 'links', 'customSections'] as $relation) {
                foreach ($resume->{$relation} as $item) {
                    $clone = $item->replicate(['created_at', 'updated_at', 'deleted_at']);
                    $clone->resume_id = $copy->id;
                    $clone->save();
                }
            }

            return $copy->fresh([
                'sections', 'experiences', 'educations', 'skills', 'projects',
                'certifications', 'languages', 'awards', 'publications',
                'volunteerExperiences', 'links', 'customSections',
            ]);
        });
    }
}
