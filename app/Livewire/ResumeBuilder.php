<?php

namespace App\Livewire;

use App\Actions\AI\RewriteResumeText;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Resume;
use App\Models\ResumeTemplate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Component;

class ResumeBuilder extends Component
{
    public Resume $resume;

    public array $overview = [];

    public array $sections = [];

    public array $newSkill = ['name' => '', 'category' => 'Core'];

    public array $newExperience = [
        'company' => '',
        'position' => '',
        'location' => '',
        'start_date' => '',
        'end_date' => '',
        'description' => '',
        'bullets' => '',
    ];

    public array $newEducation = [
        'school' => '',
        'degree' => '',
        'field' => '',
        'location' => '',
        'start_date' => '',
        'end_date' => '',
        'description' => '',
    ];

    public array $newProject = [
        'name' => '',
        'role' => '',
        'url' => '',
        'description' => '',
        'technologies' => '',
        'bullets' => '',
    ];

    public ?array $aiSuggestion = null;

    public ?string $notice = null;

    public function mount(Resume $resume): void
    {
        abort_unless($resume->user_id === auth()->id(), 403);

        $this->resume = $resume->load([
            'user', 'sections', 'experiences', 'educations', 'skills', 'projects',
            'certifications', 'languages', 'links', 'template',
        ]);
        $this->refreshState();
    }

    public function saveOverview(): void
    {
        $this->authorizeOwner();

        $validated = validator($this->overview, [
            'title' => ['required', 'string', 'max:120'],
            'professional_title' => ['nullable', 'string', 'max:160'],
            'professional_summary' => ['nullable', 'string', 'max:4000'],
            'template_id' => ['nullable', Rule::exists('resume_templates', 'id')->where('is_active', true)],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'font_family' => ['nullable', 'string', 'max:80'],
            'spacing' => ['nullable', 'in:compact,comfortable,spacious'],
        ])->validate();

        $templateId = filled($validated['template_id'] ?? null) ? (int) $validated['template_id'] : null;
        $template = $templateId ? ResumeTemplate::where('is_active', true)->find($templateId) : null;
        $templateChanged = $templateId !== $this->resume->template_id;

        if ($templateChanged && $template) {
            $validated['accent_color'] = $template->accent_color;
            $validated['font_family'] = $template->font_family;
        }

        $this->resume->update([
            ...$validated,
            'template_id' => $templateId,
            'last_edited_at' => now(),
        ]);
        $this->notice = 'Resume saved.';
        $this->refreshState();
    }

    public function saveSections(): void
    {
        $this->authorizeOwner();

        foreach ($this->sections as $id => $section) {
            $this->resume->sections()->whereKey($id)->update([
                'title' => $section['title'],
                'content' => $section['content'] ?? null,
                'is_visible' => (bool) ($section['is_visible'] ?? false),
                'sort_order' => (int) ($section['sort_order'] ?? 0),
            ]);
        }

        $this->notice = 'Sections updated.';
        $this->refreshState();
    }

    public function addSkill(): void
    {
        $this->authorizeOwner();

        $validated = validator($this->newSkill, [
            'name' => ['required', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
        ])->validate();

        $this->resume->skills()->create([...$validated, 'sort_order' => $this->resume->skills()->count()]);
        $this->newSkill = ['name' => '', 'category' => 'Core'];
        $this->notice = 'Skill added.';
        $this->refreshState();
    }

    public function removeSkill(int $id): void
    {
        $this->authorizeOwner();
        $this->resume->skills()->whereKey($id)->delete();
        $this->refreshState();
    }

    public function addExperience(): void
    {
        $this->authorizeOwner();

        $validated = validator($this->newExperience, [
            'company' => ['required', 'string', 'max:160'],
            'position' => ['required', 'string', 'max:160'],
            'location' => ['nullable', 'string', 'max:160'],
            'start_date' => ['nullable', 'string', 'max:60'],
            'end_date' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:4000'],
            'bullets' => ['nullable', 'string', 'max:6000'],
        ])->validate();

        Experience::create([
            ...$validated,
            'resume_id' => $this->resume->id,
            'bullets' => $this->lines($validated['bullets'] ?? ''),
            'sort_order' => $this->resume->experiences()->count(),
        ]);

        $this->newExperience = [
            'company' => '', 'position' => '', 'location' => '', 'start_date' => '',
            'end_date' => '', 'description' => '', 'bullets' => '',
        ];
        $this->notice = 'Experience added.';
        $this->refreshState();
    }

    public function removeExperience(int $id): void
    {
        $this->authorizeOwner();
        $this->resume->experiences()->whereKey($id)->delete();
        $this->refreshState();
    }

    public function addEducation(): void
    {
        $this->authorizeOwner();

        $validated = validator($this->newEducation, [
            'school' => ['required', 'string', 'max:180'],
            'degree' => ['nullable', 'string', 'max:180'],
            'field' => ['nullable', 'string', 'max:180'],
            'location' => ['nullable', 'string', 'max:160'],
            'start_date' => ['nullable', 'string', 'max:60'],
            'end_date' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:3000'],
        ])->validate();

        Education::create([
            ...$validated,
            'resume_id' => $this->resume->id,
            'sort_order' => $this->resume->educations()->count(),
        ]);

        $this->newEducation = ['school' => '', 'degree' => '', 'field' => '', 'location' => '', 'start_date' => '', 'end_date' => '', 'description' => ''];
        $this->notice = 'Education added.';
        $this->refreshState();
    }

    public function removeEducation(int $id): void
    {
        $this->authorizeOwner();
        $this->resume->educations()->whereKey($id)->delete();
        $this->refreshState();
    }

    public function addProject(): void
    {
        $this->authorizeOwner();

        $validated = validator($this->newProject, [
            'name' => ['required', 'string', 'max:180'],
            'role' => ['nullable', 'string', 'max:160'],
            'url' => ['nullable', 'url', 'max:500'],
            'description' => ['nullable', 'string', 'max:4000'],
            'technologies' => ['nullable', 'string', 'max:2000'],
            'bullets' => ['nullable', 'string', 'max:6000'],
        ])->validate();

        Project::create([
            ...$validated,
            'resume_id' => $this->resume->id,
            'technologies' => $this->csv($validated['technologies'] ?? ''),
            'bullets' => $this->lines($validated['bullets'] ?? ''),
            'sort_order' => $this->resume->projects()->count(),
        ]);

        $this->newProject = ['name' => '', 'role' => '', 'url' => '', 'description' => '', 'technologies' => '', 'bullets' => ''];
        $this->notice = 'Project added.';
        $this->refreshState();
    }

    public function removeProject(int $id): void
    {
        $this->authorizeOwner();
        $this->resume->projects()->whereKey($id)->delete();
        $this->refreshState();
    }

    public function improveSummary(string $action = 'make_professional'): void
    {
        $this->authorizeOwner();

        $this->aiSuggestion = app(RewriteResumeText::class)->handle(
            auth()->user(),
            $this->overview['professional_summary'] ?? '',
            $action
        );
    }

    public function acceptSuggestion(): void
    {
        if (! $this->aiSuggestion) {
            return;
        }

        $this->overview['professional_summary'] = $this->aiSuggestion['ai_rewrite'] ?? $this->overview['professional_summary'];
        $this->aiSuggestion = null;
        $this->saveOverview();
    }

    public function discardSuggestion(): void
    {
        $this->aiSuggestion = null;
    }

    public function render(): View
    {
        return view('livewire.resume-builder', [
            'templates' => ResumeTemplate::where('is_active', true)
                ->orderBy('category')
                ->orderBy('name')
                ->get()
                ->sortBy(fn (ResumeTemplate $template): int => (int) data_get($template->settings, 'sort', 999))
                ->values(),
            'experiences' => $this->resume->experiences()->orderBy('sort_order')->get(),
            'educations' => $this->resume->educations()->orderBy('sort_order')->get(),
            'skills' => $this->resume->skills()->orderBy('sort_order')->get(),
            'projects' => $this->resume->projects()->orderBy('sort_order')->get(),
            'certifications' => $this->resume->certifications()->orderBy('sort_order')->get(),
            'languages' => $this->resume->languages()->orderBy('sort_order')->get(),
            'links' => $this->resume->links()->orderBy('sort_order')->get(),
        ]);
    }

    private function refreshState(): void
    {
        $this->resume->refresh()->load(['user', 'sections', 'experiences', 'educations', 'skills', 'projects', 'certifications', 'languages', 'links', 'template']);

        $this->overview = [
            'title' => $this->resume->title,
            'professional_title' => $this->resume->professional_title,
            'professional_summary' => $this->resume->professional_summary,
            'template_id' => $this->resume->template_id,
            'accent_color' => $this->resume->accent_color,
            'font_family' => $this->resume->font_family,
            'spacing' => $this->resume->spacing,
        ];

        $this->sections = $this->resume->sections
            ->mapWithKeys(fn ($section) => [
                $section->id => [
                    'title' => $section->title,
                    'content' => $section->content,
                    'is_visible' => $section->is_visible,
                    'sort_order' => $section->sort_order,
                ],
            ])
            ->all();
    }

    private function authorizeOwner(): void
    {
        abort_unless($this->resume->user_id === auth()->id(), 403);
    }

    private function lines(string $value): array
    {
        return collect(preg_split('/\r\n|\n|\r/', $value) ?: [])
            ->map(fn ($line) => trim($line, "- \t\n\r\0\x0B"))
            ->filter()
            ->values()
            ->all();
    }

    private function csv(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
