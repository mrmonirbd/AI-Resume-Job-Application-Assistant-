<?php

namespace App\Services;

use App\Models\Resume;

class ResumeSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Resume $resume): array
    {
        $resume->loadMissing([
            'sections', 'experiences', 'educations', 'skills', 'projects',
            'certifications', 'languages', 'awards', 'publications',
            'volunteerExperiences', 'links', 'customSections', 'user',
        ]);

        return [
            'title' => $resume->title,
            'professional_title' => $resume->professional_title,
            'professional_summary' => $resume->professional_summary,
            'personal' => [
                'name' => $resume->user->name,
                'email' => $resume->user->email,
                'location' => $resume->user->location,
            ],
            'experiences' => $resume->experiences->where('is_visible', true)->values()->all(),
            'education' => $resume->educations->where('is_visible', true)->values()->all(),
            'skills' => $resume->skills->where('is_visible', true)->pluck('name')->all(),
            'projects' => $resume->projects->where('is_visible', true)->values()->all(),
            'certifications' => $resume->certifications->where('is_visible', true)->values()->all(),
            'languages' => $resume->languages->where('is_visible', true)->values()->all(),
            'links' => $resume->links->where('is_visible', true)->values()->all(),
        ];
    }

    public function toText(Resume $resume): string
    {
        $data = $this->toArray($resume);
        $lines = [
            $data['personal']['name'] ?? '',
            $data['professional_title'] ?? '',
            $data['professional_summary'] ?? '',
            implode(', ', $data['skills'] ?? []),
        ];

        foreach ($resume->experiences as $experience) {
            $lines[] = implode(' ', array_filter([
                $experience->position,
                $experience->company,
                $experience->description,
                implode(' ', $experience->bullets ?? []),
            ]));
        }

        foreach ($resume->projects as $project) {
            $lines[] = implode(' ', array_filter([
                $project->name,
                $project->description,
                implode(' ', $project->technologies ?? []),
                implode(' ', $project->bullets ?? []),
            ]));
        }

        return trim(implode("\n", array_filter($lines)));
    }
}
