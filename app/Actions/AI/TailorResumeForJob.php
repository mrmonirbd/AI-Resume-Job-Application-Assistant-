<?php

namespace App\Actions\AI;

use App\Actions\Resumes\DuplicateResume;
use App\Models\JobPosting;
use App\Models\Resume;
use App\Models\TailoredResume;
use App\Services\AI\LocalCareerAdvisor;
use App\Services\DeepSeekService;
use App\Services\ResumeSerializer;
use Illuminate\Support\Facades\DB;

class TailorResumeForJob
{
    public function __construct(
        private readonly DeepSeekService $deepSeek,
        private readonly LocalCareerAdvisor $local,
        private readonly ResumeSerializer $serializer,
        private readonly DuplicateResume $duplicateResume,
        private readonly CompareResumeToJob $compareResumeToJob
    ) {}

    public function handle(Resume $sourceResume, JobPosting $job): TailoredResume
    {
        $analysis = $job->analysis;
        $system = <<<'PROMPT'
You create a tailoring plan for a resume. Return strict JSON with summary_suggestion, skill_order, bullet_guidance, suggested_missing_information, comparison_note.
Use only verified resume facts. Never invent companies, dates, degrees, certifications, skills, achievements, metrics, or responsibilities.
Missing requirements must stay in suggested_missing_information and must not be inserted as verified resume content.
PROMPT;

        $user = "Verified master resume:\n{$this->serializer->toText($sourceResume)}\n\nJob description:\n{$job->description}\n\nJob analysis:\n".json_encode($analysis?->raw_response ?? [], JSON_PRETTY_PRINT);

        $payload = $this->deepSeek->structured(
            'tailored_resume',
            $system,
            $user,
            fn () => $this->local->tailoringPlan($sourceResume, $job, $analysis),
            $job->user
        );

        return DB::transaction(function () use ($sourceResume, $job, $payload) {
            $tailoredResume = $this->duplicateResume->handle($sourceResume->load([
                'sections', 'experiences', 'educations', 'skills', 'projects', 'certifications',
                'languages', 'awards', 'publications', 'volunteerExperiences', 'links', 'customSections',
            ]), [
                'title' => "{$job->title} - {$job->company}",
                'type' => 'tailored',
                'professional_summary' => $payload['summary_suggestion'] ?? $sourceResume->professional_summary,
                'status' => 'active',
            ]);

            $orderedSkills = collect($payload['skill_order'] ?? [])->map(fn ($skill) => trim((string) $skill))->filter()->unique()->values();

            if ($orderedSkills->isNotEmpty()) {
                $tailoredResume->skills->each(function ($skill) use ($orderedSkills) {
                    $index = $orderedSkills->search($skill->name);
                    $skill->update(['sort_order' => $index === false ? 99 : $index]);
                });
            }

            $match = $this->compareResumeToJob->handle($tailoredResume, $job);

            return TailoredResume::create([
                'user_id' => $job->user_id,
                'source_resume_id' => $sourceResume->id,
                'resume_id' => $tailoredResume->id,
                'job_id' => $job->id,
                'resume_job_match_id' => $match->id,
                'status' => 'draft',
                'ai_summary' => $payload,
                'suggested_missing_information' => $payload['suggested_missing_information'] ?? [],
            ]);
        });
    }
}
