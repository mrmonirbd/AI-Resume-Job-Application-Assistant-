<?php

namespace App\Services\AI;

use App\Models\JobAnalysis;
use App\Models\JobPosting;
use App\Models\Resume;
use App\Services\ResumeSerializer;
use Illuminate\Support\Str;

class LocalCareerAdvisor
{
    private const SKILL_BANK = [
        'php', 'laravel', 'livewire', 'blade', 'mysql', 'postgresql', 'sql', 'redis', 'docker',
        'aws', 'azure', 'gcp', 'javascript', 'typescript', 'react', 'vue', 'alpine.js', 'tailwind',
        'rest api', 'graphql', 'git', 'ci/cd', 'testing', 'phpunit', 'python', 'java', 'node.js',
        'kubernetes', 'linux', 'agile', 'scrum', 'leadership', 'communication', 'stakeholder',
    ];

    public function analyzeJob(string $description, ?string $title = null): array
    {
        $text = Str::lower($description);
        $skills = collect(self::SKILL_BANK)
            ->filter(fn (string $skill) => str_contains($text, $skill))
            ->values();

        $keywords = collect(preg_split('/[^a-zA-Z0-9+#.]+/', $description) ?: [])
            ->map(fn (string $word) => trim(Str::lower($word)))
            ->filter(fn (string $word) => strlen($word) > 3)
            ->reject(fn (string $word) => in_array($word, ['with', 'that', 'this', 'your', 'will', 'from', 'have', 'work', 'team'], true))
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(18)
            ->values();

        $seniority = collect(['intern', 'junior', 'mid', 'senior', 'lead', 'principal', 'manager', 'director'])
            ->first(fn (string $level) => str_contains($text, $level));

        return [
            'extracted_title' => $title ?: Str::headline(Str::before($description, "\n")),
            'seniority' => $seniority ? Str::headline($seniority) : 'Not specified',
            'required_skills' => $skills->take(8)->map(fn ($skill) => Str::headline($skill))->all(),
            'preferred_skills' => $skills->skip(8)->take(6)->map(fn ($skill) => Str::headline($skill))->all(),
            'technologies' => $skills->filter(fn ($skill) => ! in_array($skill, ['leadership', 'communication', 'stakeholder', 'agile', 'scrum'], true))->map(fn ($skill) => Str::headline($skill))->values()->all(),
            'experience_requirements' => $this->sentencesContaining($description, ['experience', 'years', 'background']),
            'education_requirements' => $this->sentencesContaining($description, ['degree', 'education', 'bachelor', 'master']),
            'responsibilities' => $this->sentencesContaining($description, ['build', 'develop', 'lead', 'manage', 'design', 'support', 'maintain']),
            'keywords' => $keywords->map(fn ($word) => Str::headline($word))->all(),
            'soft_skills' => $skills->filter(fn ($skill) => in_array($skill, ['leadership', 'communication', 'stakeholder', 'agile', 'scrum'], true))->map(fn ($skill) => Str::headline($skill))->values()->all(),
            'raw_response' => ['source' => 'local_keyword_analyzer'],
        ];
    }

    public function matchResume(Resume $resume, JobPosting $job, ?JobAnalysis $analysis = null): array
    {
        $resume->loadMissing(['skills', 'experiences', 'projects', 'educations']);
        $resumeText = Str::lower(app(ResumeSerializer::class)->toText($resume));

        $jobTerms = collect([
            ...($analysis?->required_skills ?? []),
            ...($analysis?->preferred_skills ?? []),
            ...($analysis?->technologies ?? []),
            ...($analysis?->keywords ?? []),
        ])->map(fn ($skill) => Str::lower((string) $skill))->filter()->unique()->values();

        if ($jobTerms->isEmpty()) {
            $jobTerms = collect($this->analyzeJob($job->description, $job->title)['keywords'])
                ->map(fn ($skill) => Str::lower((string) $skill));
        }

        $resumeSkills = $resume->skills->pluck('name')->map(fn ($skill) => Str::lower($skill));
        $matched = $jobTerms->filter(fn ($term) => $resumeSkills->contains($term) || str_contains($resumeText, $term))->values();
        $missing = $jobTerms->diff($matched)->take(10)->values();

        $skillScore = $jobTerms->isEmpty() ? 65 : (int) round(($matched->count() / max($jobTerms->count(), 1)) * 100);
        $experienceScore = min(94, 55 + ($resume->experiences->count() * 12) + ($matched->count() * 2));
        $educationScore = $resume->educations->isNotEmpty() ? 88 : 55;
        $keywordScore = min(96, max(35, $skillScore + 8));
        $overall = (int) round(($skillScore * 0.4) + ($experienceScore * 0.25) + ($educationScore * 0.15) + ($keywordScore * 0.2));

        return [
            'overall_score' => $overall,
            'skills_score' => $skillScore,
            'experience_score' => $experienceScore,
            'education_score' => $educationScore,
            'keyword_score' => $keywordScore,
            'matched_skills' => $matched->map(fn ($term) => Str::headline($term))->all(),
            'missing_skills' => $missing->map(fn ($term) => Str::headline($term))->all(),
            'recommendations' => [
                'Highlight matching responsibilities using language from the job description where it is truthful.',
                'Move the most relevant verified skills closer to the top of the resume.',
                'For any missing requirement, add it only if you truly have that experience; otherwise keep it as a learning recommendation.',
            ],
            'disclaimer' => 'This score is an internal compatibility estimate, not an official employer ATS score or a guarantee of interview selection.',
            'raw_response' => ['source' => 'local_match_analyzer'],
        ];
    }

    public function atsCheck(Resume $resume, ?string $jobDescription = null): array
    {
        $resume->loadMissing(['skills', 'experiences', 'educations']);
        $hasContact = $resume->sections->where('type', 'personal')->first()?->content || $resume->links->isNotEmpty();
        $hasBullets = $resume->experiences->flatMap(fn ($experience) => $experience->bullets ?? [])->filter()->isNotEmpty();
        $jobKeywords = $jobDescription ? collect($this->analyzeJob($jobDescription)['keywords']) : collect();
        $resumeText = Str::lower(app(ResumeSerializer::class)->toText($resume));
        $keywordHits = $jobKeywords->filter(fn ($keyword) => str_contains($resumeText, Str::lower($keyword)))->count();
        $keywordScore = $jobKeywords->isEmpty() ? 78 : (int) round(($keywordHits / max($jobKeywords->count(), 1)) * 100);
        $structureScore = ($resume->experiences->isNotEmpty() ? 30 : 0) + ($resume->educations->isNotEmpty() ? 25 : 0) + ($resume->skills->isNotEmpty() ? 25 : 0) + ($hasContact ? 20 : 0);
        $contentScore = min(96, 45 + ($resume->experiences->count() * 14) + ($hasBullets ? 18 : 0));
        $readabilityScore = strlen($resumeText) > 550 ? 84 : 68;
        $formattingScore = 90;

        return [
            'readiness_score' => (int) round(($contentScore + $structureScore + $keywordScore + $readabilityScore + $formattingScore) / 5),
            'categories' => [
                'content' => $contentScore,
                'structure' => $structureScore,
                'keywords' => $keywordScore,
                'readability' => $readabilityScore,
                'formatting' => $formattingScore,
            ],
            'recommendations' => array_values(array_filter([
                $hasContact ? null : 'Add clear contact information and at least one professional link.',
                $hasBullets ? null : 'Convert experience descriptions into concise achievement bullets.',
                $jobKeywords->isEmpty() ? null : 'Use verified resume content to naturally mirror important job keywords.',
                'Keep formatting simple: standard headings, readable spacing, and no complex tables.',
            ])),
            'disclaimer' => 'This readiness score is not an actual proprietary ATS score.',
        ];
    }

    public function rewrite(string $text, string $action): array
    {
        $lead = match ($action) {
            'make_shorter' => 'Concise rewrite',
            'expand' => 'Expanded rewrite',
            'fix_grammar' => 'Grammar-polished rewrite',
            'highlight_impact' => 'Impact-focused rewrite',
            default => 'Professional rewrite',
        };

        $clean = trim(preg_replace('/\s+/', ' ', $text) ?: $text);
        $suggestion = $clean === ''
            ? 'Describe the verified responsibility, tool, and outcome here. Add a measurable result only if available.'
            : $lead.': '.$clean;

        return [
            'verified_user_information' => $clean,
            'ai_rewrite' => $suggestion,
            'ai_recommendation' => 'Review this suggestion for factual accuracy before accepting it. Add metrics only when you can verify them.',
        ];
    }

    public function coverLetter(Resume $resume, JobPosting $job, string $tone, string $length): array
    {
        $resume->loadMissing(['skills', 'experiences']);
        $skills = $resume->skills->take(6)->pluck('name')->join(', ');
        $recentRole = $resume->experiences->first();
        $bodyLength = $length === 'short' ? 'concise' : ($length === 'detailed' ? 'detailed' : 'focused');

        $content = "Dear Hiring Team,\n\n";
        $content .= "I am excited to apply for the {$job->title} role at {$job->company}. ";
        $content .= 'My background'.($recentRole ? " as {$recentRole->position} at {$recentRole->company}" : '')." aligns with the needs of this {$bodyLength} application, especially around ".($skills ?: 'the verified experience listed in my resume').".\n\n";
        $content .= "I have kept this letter grounded in verified resume information and would welcome the opportunity to discuss how my experience can support {$job->company}'s goals.\n\n";
        $content .= "Sincerely,\n{$resume->user->name}";

        return [
            'title' => "{$job->company} {$job->title} Cover Letter",
            'content' => $content,
            'tone' => $tone,
            'length' => $length,
            'factuality_note' => 'Generated only from saved resume and job information; review before sending.',
        ];
    }

    public function tailoringPlan(Resume $resume, JobPosting $job, ?JobAnalysis $analysis = null): array
    {
        $match = $this->matchResume($resume, $job, $analysis);

        return [
            'summary_suggestion' => 'Position the summary toward '.$job->title.' at '.$job->company.' using only verified experience from the master resume.',
            'skill_order' => array_values(array_unique([...$match['matched_skills'], ...$resume->skills()->pluck('name')->all()])),
            'bullet_guidance' => [
                'Prioritize bullets that mention matched technologies and responsibilities.',
                'Do not add missing skills unless the user confirms real experience.',
                'Use “Add measurable result here if available” instead of invented metrics.',
            ],
            'suggested_missing_information' => $match['missing_skills'],
            'comparison_note' => 'A new resume version was created; the master resume was not modified.',
        ];
    }

    /**
     * @param  array<int, string>  $needles
     * @return array<int, string>
     */
    private function sentencesContaining(string $text, array $needles): array
    {
        return collect(preg_split('/(?<=[.!?])\s+|\n+/', $text) ?: [])
            ->map(fn (string $sentence) => trim($sentence))
            ->filter(fn (string $sentence) => $sentence !== '')
            ->filter(function (string $sentence) use ($needles) {
                $lower = Str::lower($sentence);

                return collect($needles)->contains(fn (string $needle) => str_contains($lower, $needle));
            })
            ->take(6)
            ->values()
            ->all();
    }
}
