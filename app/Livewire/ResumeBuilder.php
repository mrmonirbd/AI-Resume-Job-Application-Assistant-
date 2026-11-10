<?php

namespace App\Livewire;

use App\Actions\AI\RewriteResumeText;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Project;
use App\Models\Resume;
use App\Models\ResumeSection;
use App\Models\ResumeTemplate;
use App\Models\Skill;
use App\Services\ImageExportService;
use App\Services\PdfExportService;
use App\Services\WordExportService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResumeBuilder extends Component
{
    use WithFileUploads;

    public string $fullName = '';

    public ?TemporaryUploadedFile $profilePhoto = null;

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
        $this->fullName = $this->resume->user->name;
        $this->refreshState();
    }

    public function savePersonalInformation(): void
    {
        $this->authorizeOwner();
        $validated = $this->validate([
            'fullName' => ['required', 'string', 'max:255', 'regex:/\S/u'],
            'profilePhoto' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $user = auth()->user();
        $previousPhotoPath = $user->profile_photo_path;
        $user->name = trim($validated['fullName']);

        if ($this->profilePhoto) {
            $user->profile_photo_path = $this->profilePhoto->store("profile-photos/{$user->id}", 'public');
        }

        $user->save();

        if ($this->profilePhoto && $previousPhotoPath) {
            Storage::disk('public')->delete($previousPhotoPath);
        }

        $this->reset('profilePhoto');
        $this->fullName = $user->name;
        $this->refreshState(false, false);
        $this->notice = 'Personal information saved.';
    }

    public function updatedOverview(): void
    {
        $this->notice = null;
    }

    public function updatedOverviewTemplateId(): void
    {
        $this->authorizeOwner();
        $this->validateOnly('overview.template_id', [
            'overview.template_id' => ['nullable', Rule::exists('resume_templates', 'id')->where('is_active', true)],
        ]);

        $template = ResumeTemplate::where('is_active', true)->find($this->overview['template_id']);

        if ($template) {
            $this->overview['accent_color'] = $template->accent_color;
            $this->overview['font_family'] = $template->font_family;
        }

        $this->notice = null;
    }

    public function saveOverview(): void
    {
        $this->authorizeOwner();

        $validated = $this->validatedOverview();

        $templateId = filled($validated['template_id'] ?? null) ? (int) $validated['template_id'] : null;
        $this->resume->update([
            ...$validated,
            'template_id' => $templateId,
            'last_edited_at' => now(),
        ]);
        $this->notice = 'Resume saved.';
        $this->refreshState(refreshSections: false);
    }

    public function saveSections(): void
    {
        $this->authorizeOwner();

        $validated = $this->validate([
            'sections' => ['required', 'array', 'max:30'],
            'sections.*.title' => ['required', 'string', 'max:120'],
            'sections.*.content' => ['nullable', 'string', 'max:5000'],
            'sections.*.is_visible' => ['required', 'boolean'],
            'sections.*.sort_order' => ['required', 'integer', 'min:0', 'max:1000'],
        ]);

        foreach ($validated['sections'] as $id => $section) {
            $this->resume->sections()->whereKey($id)->update([
                'title' => $section['title'],
                'content' => $section['content'] ?? null,
                'is_visible' => (bool) ($section['is_visible'] ?? false),
                'sort_order' => (int) ($section['sort_order'] ?? 0),
            ]);
        }

        $this->notice = 'Sections updated.';
        $this->refreshState(refreshOverview: false);
    }

    public function addSkill(): void
    {
        $this->authorizeOwner();

        $validated = $this->validate([
            'newSkill.name' => ['required', 'string', 'max:120'],
            'newSkill.category' => ['nullable', 'string', 'max:120'],
        ])['newSkill'];

        $this->resume->skills()->create([...$validated, 'sort_order' => $this->resume->skills()->count()]);
        $this->newSkill = ['name' => '', 'category' => 'Core'];
        $this->notice = 'Skill added.';
        $this->refreshState(false, false);
    }

    public function removeSkill(int $id): void
    {
        $this->authorizeOwner();
        $this->resume->skills()->whereKey($id)->delete();
        $this->refreshState(false, false);
    }

    public function addExperience(): void
    {
        $this->authorizeOwner();

        $validated = $this->validate([
            'newExperience.company' => ['required', 'string', 'max:160'],
            'newExperience.position' => ['required', 'string', 'max:160'],
            'newExperience.location' => ['nullable', 'string', 'max:160'],
            'newExperience.start_date' => ['nullable', 'string', 'max:60'],
            'newExperience.end_date' => ['nullable', 'string', 'max:60'],
            'newExperience.description' => ['nullable', 'string', 'max:4000'],
            'newExperience.bullets' => ['nullable', 'string', 'max:6000'],
        ])['newExperience'];

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
        $this->refreshState(false, false);
    }

    public function removeExperience(int $id): void
    {
        $this->authorizeOwner();
        $this->resume->experiences()->whereKey($id)->delete();
        $this->refreshState(false, false);
    }

    public function addEducation(): void
    {
        $this->authorizeOwner();

        $validated = $this->validate([
            'newEducation.school' => ['required', 'string', 'max:180'],
            'newEducation.degree' => ['nullable', 'string', 'max:180'],
            'newEducation.field' => ['nullable', 'string', 'max:180'],
            'newEducation.location' => ['nullable', 'string', 'max:160'],
            'newEducation.start_date' => ['nullable', 'string', 'max:60'],
            'newEducation.end_date' => ['nullable', 'string', 'max:60'],
            'newEducation.description' => ['nullable', 'string', 'max:3000'],
        ])['newEducation'];

        Education::create([
            ...$validated,
            'resume_id' => $this->resume->id,
            'sort_order' => $this->resume->educations()->count(),
        ]);

        $this->newEducation = ['school' => '', 'degree' => '', 'field' => '', 'location' => '', 'start_date' => '', 'end_date' => '', 'description' => ''];
        $this->notice = 'Education added.';
        $this->refreshState(false, false);
    }

    public function removeEducation(int $id): void
    {
        $this->authorizeOwner();
        $this->resume->educations()->whereKey($id)->delete();
        $this->refreshState(false, false);
    }

    public function addProject(): void
    {
        $this->authorizeOwner();

        $validated = $this->validate([
            'newProject.name' => ['required', 'string', 'max:180'],
            'newProject.role' => ['nullable', 'string', 'max:160'],
            'newProject.url' => ['nullable', 'url:http,https', 'max:500'],
            'newProject.description' => ['nullable', 'string', 'max:4000'],
            'newProject.technologies' => ['nullable', 'string', 'max:2000'],
            'newProject.bullets' => ['nullable', 'string', 'max:6000'],
        ])['newProject'];

        Project::create([
            ...$validated,
            'resume_id' => $this->resume->id,
            'technologies' => $this->csv($validated['technologies'] ?? ''),
            'bullets' => $this->lines($validated['bullets'] ?? ''),
            'sort_order' => $this->resume->projects()->count(),
        ]);

        $this->newProject = ['name' => '', 'role' => '', 'url' => '', 'description' => '', 'technologies' => '', 'bullets' => ''];
        $this->notice = 'Project added.';
        $this->refreshState(false, false);
    }

    public function removeProject(int $id): void
    {
        $this->authorizeOwner();
        $this->resume->projects()->whereKey($id)->delete();
        $this->refreshState(false, false);
    }

    public function improveSummary(string $action = 'make_professional'): void
    {
        $this->authorizeOwner();

        $validated = $this->validate([
            'overview.professional_summary' => ['required', 'string', 'max:4000'],
        ]);
        validator(['action' => $action], [
            'action' => ['required', Rule::in(['make_professional', 'shorten', 'expand', 'fix_grammar'])],
        ])->validate();

        $rateLimitKey = 'resume-summary:'.auth()->id();

        if (RateLimiter::tooManyAttempts($rateLimitKey, 12)) {
            throw ValidationException::withMessages([
                'overview.professional_summary' => 'Too many AI requests. Please try again in a minute.',
            ]);
        }

        RateLimiter::hit($rateLimitKey, 60);

        $this->aiSuggestion = app(RewriteResumeText::class)->handle(
            auth()->user(),
            $validated['overview']['professional_summary'],
            $action
        );
    }

    public function acceptSuggestion(): void
    {
        if (! $this->aiSuggestion) {
            return;
        }

        $this->authorizeOwner();
        $suggestion = app(RewriteResumeText::class)->normalize(
            $this->aiSuggestion,
            is_string($this->overview['professional_summary'] ?? null) ? $this->overview['professional_summary'] : ''
        );
        $this->overview['professional_summary'] = $suggestion['ai_rewrite'];
        $this->aiSuggestion = null;
        $this->notice = 'Suggestion added to preview. Save to keep your changes.';
    }

    public function discardSuggestion(): void
    {
        $this->aiSuggestion = null;
    }

    #[On('export-resume')]
    public function exportCurrent(string $format = 'pdf'): StreamedResponse
    {
        $this->authorizeOwner();
        validator(['format' => $format], ['format' => ['required', Rule::in(['pdf', 'docx', 'png'])]])->validate();
        $validated = $this->validatedOverview();
        $data = $this->previewData();
        $draft = clone $this->resume;
        $draft->fill($validated);
        $draft->setRelation('template', $data['previewTemplate']);
        foreach (['experiences', 'educations', 'skills', 'projects'] as $relation) {
            $draft->setRelation($relation, $data['preview'.ucfirst($relation)]);
        }
        $draft->setRelation('sections', collect($data['previewSections'])->map(fn (array $section): ResumeSection => new ResumeSection($section)));

        $response = match ($format) {
            'docx' => app(WordExportService::class)->resume($draft),
            'png' => app(ImageExportService::class)->resume($draft),
            default => app(PdfExportService::class)->resume($draft),
        };

        return response()->streamDownload(function () use ($response): void {
            echo $response->getContent();
        }, str($draft->title)->slug().'.'.$format, [
            'Content-Type' => $response->headers->get('Content-Type'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedOverview(): array
    {
        return $this->validate([
            'overview.title' => ['required', 'string', 'max:120'],
            'overview.professional_title' => ['nullable', 'string', 'max:160'],
            'overview.professional_summary' => ['nullable', 'string', 'max:4000'],
            'overview.template_id' => ['nullable', Rule::exists('resume_templates', 'id')->where('is_active', true)],
            'overview.accent_color' => ['nullable', 'hex_color'],
            'overview.font_family' => ['nullable', 'string', 'max:80', 'regex:/^[a-zA-Z0-9 ,\-]+$/'],
            'overview.spacing' => ['nullable', 'in:compact,comfortable,spacious'],
        ])['overview'];
    }

    public function render(): View
    {
        return view('livewire.resume-builder', $this->previewData());
    }

    /**
     * @return array<string, mixed>
     */
    private function previewData(): array
    {
        $templates = ResumeTemplate::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->sortBy(fn (ResumeTemplate $template): int => (int) data_get($template->settings, 'sort', 999))
            ->values();

        $experiences = $this->resume->experiences()->orderBy('sort_order')->get();
        $educations = $this->resume->educations()->orderBy('sort_order')->get();
        $skills = $this->resume->skills()->orderBy('sort_order')->get();
        $projects = $this->resume->projects()->orderBy('sort_order')->get();

        $previewExperiences = $experiences->toBase();
        if (filled($this->newExperience['company'] ?? '') || filled($this->newExperience['position'] ?? '') || filled($this->newExperience['description'] ?? '')) {
            $previewExperiences = $previewExperiences->concat([new Experience([
                ...$this->newExperience,
                'bullets' => $this->lines($this->newExperience['bullets'] ?? ''),
            ])]);
        }

        $previewEducations = $educations->toBase();
        if (filled($this->newEducation['school'] ?? '') || filled($this->newEducation['degree'] ?? '') || filled($this->newEducation['field'] ?? '')) {
            $previewEducations = $previewEducations->concat([new Education($this->newEducation)]);
        }

        $previewSkills = $skills->toBase();
        if (filled($this->newSkill['name'] ?? '')) {
            $previewSkills = $previewSkills->concat([new Skill($this->newSkill)]);
        }

        $previewProjects = $projects->toBase();
        if (filled($this->newProject['name'] ?? '') || filled($this->newProject['description'] ?? '')) {
            $previewProjects = $previewProjects->concat([new Project([
                ...$this->newProject,
                'technologies' => $this->csv($this->newProject['technologies'] ?? ''),
                'bullets' => $this->lines($this->newProject['bullets'] ?? ''),
            ])]);
        }

        return [
            'templates' => $templates,
            'safeAiSuggestion' => $this->aiSuggestion ? app(RewriteResumeText::class)->normalize(
                $this->aiSuggestion,
                is_string($this->overview['professional_summary'] ?? null) ? $this->overview['professional_summary'] : ''
            ) : null,
            'previewTemplate' => $templates->firstWhere('id', $this->overview['template_id'] ?? null) ?? $this->resume->template,
            'experiences' => $experiences,
            'educations' => $educations,
            'skills' => $skills,
            'projects' => $projects,
            'previewExperiences' => $previewExperiences,
            'previewEducations' => $previewEducations,
            'previewSkills' => $previewSkills,
            'previewProjects' => $previewProjects,
            'previewSections' => $this->resume->sections->map(fn ($section): array => [
                ...($this->sections[$section->id] ?? []),
                'type' => $section->type,
            ])->all(),
            'certifications' => $this->resume->certifications()->orderBy('sort_order')->get(),
            'languages' => $this->resume->languages()->orderBy('sort_order')->get(),
            'links' => $this->resume->links()->orderBy('sort_order')->get(),
        ];
    }

    private function refreshState(bool $refreshOverview = true, bool $refreshSections = true): void
    {
        $this->resume->refresh()->load(['user', 'sections', 'experiences', 'educations', 'skills', 'projects', 'certifications', 'languages', 'links', 'template']);

        if ($refreshOverview) {
            $this->overview = [
                'title' => $this->resume->title,
                'professional_title' => $this->resume->professional_title,
                'professional_summary' => $this->resume->professional_summary,
                'template_id' => $this->resume->template_id,
                'accent_color' => $this->resume->accent_color,
                'font_family' => $this->resume->font_family,
                'spacing' => $this->resume->spacing,
            ];
        }

        if ($refreshSections) {
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
