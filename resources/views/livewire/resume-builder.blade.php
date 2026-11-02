<div x-data="{ templatePickerOpen: false }" @keydown.escape.window="templatePickerOpen = false" class="grid gap-5 xl:grid-cols-[180px_minmax(0,1fr)_320px] 2xl:grid-cols-[220px_minmax(0,1fr)_420px]">
    <aside class="rounded-lg border border-line bg-white p-4 shadow-sm xl:sticky xl:top-6 xl:max-h-[calc(100dvh-3rem)] xl:self-start xl:overflow-y-auto">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-muted">Sections</p>
        <nav class="mt-4 space-y-1 text-sm font-semibold">
            @foreach ($sections as $id => $section)
                <a wire:key="section-link-{{ $id }}" href="#section-{{ $id }}" class="block rounded-md px-3 py-2 text-neutral-600 hover:bg-cream hover:text-ink">{{ $section['title'] }}</a>
            @endforeach
        </nav>
        <div class="mt-6 rounded-lg border border-dashed border-black/15 p-3 text-xs leading-5 text-muted">
            Master data is the verified source. Tailored resumes are separate versions.
        </div>
    </aside>

    <section class="min-w-0 space-y-5">
        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                <p class="font-semibold">Please check the following fields:</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if ($notice)
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ $notice }}</div>
        @endif

        <form wire:submit.prevent="saveOverview" class="rounded-lg border border-line bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-black">Overview</h2>
                <button wire:loading.attr="disabled" type="submit" class="rounded-lg bg-brand-700 px-4 py-2 text-sm font-black text-white">Save</button>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-semibold">Resume name</span>
                    <input aria-label="Title" wire:model.live.debounce.300ms="overview.title" class="mt-2 w-full rounded-lg border border-line px-4 py-3 text-sm">
                    @error('overview.title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Professional title</span>
                    <input aria-label="Professional title" wire:model.live.debounce.300ms="overview.professional_title" class="mt-2 w-full rounded-lg border border-line px-4 py-3 text-sm">
                </label>
                <div class="min-w-0 md:col-span-2">
                    <p class="text-sm font-semibold">Template</p>
                    <button type="button" @click="templatePickerOpen = true" :aria-expanded="templatePickerOpen" aria-haspopup="dialog" aria-controls="resume-template-picker" class="mt-2 flex w-full items-center justify-between gap-4 rounded-xl border border-line bg-white px-4 py-3 text-left transition hover:border-brand-300 hover:bg-brand-50/40">
                        <span class="flex min-w-0 items-center gap-3">
                            <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-brand-50 text-brand-600"><x-workspace-icon name="grid" /></span>
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-ink">Choose a template</span>
                                <span class="mt-1 block truncate text-xs text-muted">{{ $previewTemplate?->name ?? 'Browse available layouts' }}</span>
                            </span>
                        </span>
                        <span class="shrink-0 text-xs font-semibold text-brand-700">Browse layouts →</span>
                    </button>
                    <p class="mt-2 text-xs leading-5 text-muted">Select a layout in the popup to update your live preview. Save to keep your changes.</p>
                    @error('overview.template_id') <p role="alert" class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <label class="block">
                    <span class="text-sm font-semibold">Accent color</span>
                    <input aria-label="Accent color" wire:model.live="overview.accent_color" type="color" class="mt-2 h-12 w-full rounded-lg border border-line px-2 py-2">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Spacing</span>
                    <select wire:model.live="overview.spacing" class="mt-2 w-full rounded-lg border border-line px-4 py-3 text-sm">
                        <option value="compact">Compact</option>
                        <option value="comfortable">Comfortable</option>
                        <option value="spacious">Spacious</option>
                    </select>
                </label>
                <label class="block md:col-span-2">
                    <span class="flex items-center justify-between gap-3">
                        <span class="text-sm font-semibold">Professional summary</span>
                        <button type="button" wire:loading.attr="disabled" wire:target="improveSummary" wire:click="improveSummary('make_professional')" class="rounded-md border border-line px-3 py-1.5 text-xs font-black">✨ AI</button>
                    </span>
                    <textarea aria-label="Professional summary" wire:model.live.debounce.300ms="overview.professional_summary" rows="5" class="mt-2 w-full rounded-lg border border-line px-4 py-3 text-sm"></textarea>
                </label>
            </div>
        </form>

        @if ($aiSuggestion)
            <div class="rounded-lg border border-teal-200 bg-teal-50 p-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-sm font-black text-teal-900">AI suggestion ready</p>
                        <p class="mt-2 text-sm leading-6 text-teal-800">{{ $safeAiSuggestion['ai_rewrite'] ?? '' }}</p>
                        <p class="mt-3 text-xs font-semibold text-brand-700">{{ $safeAiSuggestion['ai_recommendation'] ?? 'Review before accepting.' }}</p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <button type="button" wire:click="acceptSuggestion" class="rounded-lg bg-brand-700 px-4 py-2 text-sm font-black text-white">Accept</button>
                        <button type="button" wire:loading.attr="disabled" wire:target="improveSummary" wire:click="improveSummary('make_professional')" class="rounded-lg border border-teal-300 px-4 py-2 text-sm font-bold">Try Again</button>
                        <button type="button" wire:click="discardSuggestion" class="rounded-lg border border-teal-300 px-4 py-2 text-sm font-bold">Discard</button>
                    </div>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="saveSections" class="rounded-lg border border-line bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black">Resume sections</h2>
                <button wire:loading.attr="disabled" class="rounded-lg bg-brand-700 px-4 py-2 text-sm font-black text-white">Save sections</button>
            </div>
            <div class="mt-5 space-y-4">
                @foreach ($sections as $id => $section)
                    <div wire:key="section-{{ $id }}" id="section-{{ $id }}" class="rounded-lg border border-line p-4">
                        <div class="grid gap-3 md:grid-cols-[minmax(0,1fr)_80px] 2xl:grid-cols-[minmax(0,1fr)_90px_70px]">
                            <input aria-label="Title" wire:model.live.debounce.300ms="sections.{{ $id }}.title" class="md:col-span-2 2xl:col-span-1 rounded-lg border border-line px-3 py-2 text-sm font-semibold">
                            <input aria-label="Sort order" wire:model.live.debounce.300ms="sections.{{ $id }}.sort_order" type="number" class="rounded-lg border border-line px-3 py-2 text-sm">
                            <label class="flex items-center gap-2 text-sm font-semibold">
                                <input aria-label="Is visible" wire:model.live.debounce.300ms="sections.{{ $id }}.is_visible" type="checkbox" class="rounded border-black/20">
                                Show
                            </label>
                        </div>
                        <textarea aria-label="Content" wire:model.live.debounce.300ms="sections.{{ $id }}.content" rows="3" class="mt-3 w-full rounded-lg border border-line px-3 py-2 text-sm" placeholder="Optional notes for this section"></textarea>
                    </div>
                @endforeach
            </div>
        </form>

        <div class="grid gap-5 lg:grid-cols-2">
            <form wire:submit.prevent="addSkill" class="rounded-lg border border-line bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black">Skills</h2>
                <div class="mt-4 grid gap-3">
                    <input aria-label="Name" wire:model.live.debounce.300ms="newSkill.name" class="rounded-lg border border-line px-4 py-3 text-sm" placeholder="Laravel">
                    <input aria-label="Category" wire:model.live.debounce.300ms="newSkill.category" class="rounded-lg border border-line px-4 py-3 text-sm" placeholder="Backend">
                    <button wire:loading.attr="disabled" class="rounded-lg bg-brand-700 px-4 py-3 text-sm font-black text-white">Add skill</button>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span wire:key="skill-{{ $skill->id }}" class="inline-flex items-center gap-2 rounded-full border border-line px-3 py-1 text-xs font-bold">
                            {{ $skill->name }}
                            <button type="button" aria-label="Remove {{ $skill->name }}" wire:click="removeSkill({{ $skill->id }})" class="text-neutral-400 hover:text-red-600">×</button>
                        </span>
                    @endforeach
                </div>
            </form>

            <form wire:submit.prevent="addEducation" class="rounded-lg border border-line bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black">Education</h2>
                <div class="mt-4 grid gap-3">
                    <input aria-label="School" wire:model.live.debounce.300ms="newEducation.school" class="rounded-lg border border-line px-4 py-3 text-sm" placeholder="School">
                    <input aria-label="Degree" wire:model.live.debounce.300ms="newEducation.degree" class="rounded-lg border border-line px-4 py-3 text-sm" placeholder="Degree">
                    <input aria-label="Field" wire:model.live.debounce.300ms="newEducation.field" class="rounded-lg border border-line px-4 py-3 text-sm" placeholder="Field">
                    <button wire:loading.attr="disabled" class="rounded-lg bg-brand-700 px-4 py-3 text-sm font-black text-white">Add education</button>
                </div>
                <div class="mt-4 space-y-2">
                    @foreach ($educations as $education)
                        <div wire:key="education-{{ $education->id }}" class="flex items-start justify-between gap-3 rounded-lg border border-line p-3">
                            <p class="text-sm"><strong>{{ $education->school }}</strong><br><span class="text-muted">{{ $education->degree }} {{ $education->field }}</span></p>
                            <button type="button" wire:click="removeEducation({{ $education->id }})" class="text-sm font-bold text-red-600">Remove</button>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>

        <form wire:submit.prevent="addExperience" class="rounded-lg border border-line bg-white p-5 shadow-sm">
            <h2 class="text-lg font-black">Work experience</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <input aria-label="Company" wire:model.live.debounce.300ms="newExperience.company" class="rounded-lg border border-line px-4 py-3 text-sm" placeholder="Company">
                <input aria-label="Position" wire:model.live.debounce.300ms="newExperience.position" class="rounded-lg border border-line px-4 py-3 text-sm" placeholder="Position">
                <input aria-label="Location" wire:model.live.debounce.300ms="newExperience.location" class="rounded-lg border border-line px-4 py-3 text-sm" placeholder="Location">
                <div class="grid grid-cols-2 gap-3">
                    <input aria-label="Start date" wire:model.live.debounce.300ms="newExperience.start_date" class="rounded-lg border border-line px-4 py-3 text-sm" placeholder="Start">
                    <input aria-label="End date" wire:model.live.debounce.300ms="newExperience.end_date" class="rounded-lg border border-line px-4 py-3 text-sm" placeholder="End">
                </div>
                <textarea aria-label="Description" wire:model.live.debounce.300ms="newExperience.description" rows="3" class="rounded-lg border border-line px-4 py-3 text-sm md:col-span-2" placeholder="Verified responsibility summary"></textarea>
                <textarea aria-label="Bullets" wire:model.live.debounce.300ms="newExperience.bullets" rows="4" class="rounded-lg border border-line px-4 py-3 text-sm md:col-span-2" placeholder="One verified bullet per line"></textarea>
            </div>
            <button wire:loading.attr="disabled" class="mt-4 rounded-lg bg-brand-700 px-4 py-3 text-sm font-black text-white">Add experience</button>
            <div class="mt-5 space-y-3">
                @foreach ($experiences as $experience)
                    <div wire:key="experience-{{ $experience->id }}" class="rounded-lg border border-line p-4">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm"><strong>{{ $experience->position }}</strong> at {{ $experience->company }}<br><span class="text-muted">{{ $experience->start_date }} - {{ $experience->end_date ?: 'Present' }}</span></p>
                            <button type="button" wire:click="removeExperience({{ $experience->id }})" class="text-sm font-bold text-red-600">Remove</button>
                        </div>
                        @if ($experience->description)
                            <p class="mt-3 text-sm leading-6 text-neutral-600">{{ $experience->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </form>

        <form wire:submit.prevent="addProject" class="rounded-lg border border-line bg-white p-5 shadow-sm">
            <h2 class="text-lg font-black">Projects</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <input aria-label="Name" wire:model.live.debounce.300ms="newProject.name" class="rounded-lg border border-line px-4 py-3 text-sm" placeholder="Project name">
                <input aria-label="Role" wire:model.live.debounce.300ms="newProject.role" class="rounded-lg border border-line px-4 py-3 text-sm" placeholder="Role">
                <input aria-label="Url" wire:model.live.debounce.300ms="newProject.url" class="rounded-lg border border-line px-4 py-3 text-sm md:col-span-2" placeholder="URL">
                <textarea aria-label="Description" wire:model.live.debounce.300ms="newProject.description" rows="3" class="rounded-lg border border-line px-4 py-3 text-sm md:col-span-2" placeholder="Verified project description"></textarea>
                <input aria-label="Technologies" wire:model.live.debounce.300ms="newProject.technologies" class="rounded-lg border border-line px-4 py-3 text-sm md:col-span-2" placeholder="Technologies, comma separated">
                <textarea aria-label="Bullets" wire:model.live.debounce.300ms="newProject.bullets" rows="3" class="rounded-lg border border-line px-4 py-3 text-sm md:col-span-2" placeholder="One verified bullet per line"></textarea>
            </div>
            <button wire:loading.attr="disabled" class="mt-4 rounded-lg bg-brand-700 px-4 py-3 text-sm font-black text-white">Add project</button>
            <div class="mt-5 space-y-3">
                @foreach ($projects as $project)
                    <div wire:key="project-{{ $project->id }}" class="rounded-lg border border-line p-4">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm"><strong>{{ $project->name }}</strong><br><span class="text-muted">{{ implode(', ', $project->technologies ?? []) }}</span></p>
                            <button type="button" wire:click="removeProject({{ $project->id }})" class="text-sm font-bold text-red-600">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </form>
    </section>

    <aside class="rounded-lg border border-line bg-white p-5 shadow-sm xl:sticky xl:top-6 xl:max-h-[calc(100dvh-3rem)] xl:self-start xl:overflow-auto">
        <div wire:key="live-preview-{{ $previewTemplate?->id ?? 'default' }}">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black">Live preview</h2>
                <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-bold">{{ $previewTemplate?->name ?? 'Template' }}</span>
            </div>
            <p wire:loading wire:target="exportCurrent" role="status" class="mt-2 text-xs font-semibold text-brand-700">Preparing your export…</p>
            <p class="mt-2 break-words text-xs text-muted">{{ $overview['title'] ?? '' }}</p>
            <x-resume-document-preview
                :resume="$resume"
                :overview="$overview"
                :preview-template="$previewTemplate"
                :experiences="$previewExperiences"
                :educations="$previewEducations"
                :skills="$previewSkills"
                :projects="$previewProjects"
                :certifications="$certifications"
                :languages="$languages"
                :links="$links"
                :sections="$previewSections"
                class="mt-5"
            />
        </div>
    </aside>
    <div
        id="resume-template-picker"
        x-cloak
        x-show="templatePickerOpen"
        x-trap.inert.noscroll="templatePickerOpen"
        x-transition.opacity
        @click.self="templatePickerOpen = false"
        role="dialog"
        aria-modal="true"
        aria-labelledby="resume-template-picker-title"
        class="fixed inset-0 z-50 flex items-center justify-center bg-ink/60 p-3 backdrop-blur-sm sm:p-6"
    >
        <div class="flex max-h-[90dvh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl border border-line bg-white shadow-2xl">
            <header class="flex shrink-0 items-start justify-between gap-4 border-b border-line p-5 sm:px-6">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-brand-600">{{ $templates->count() }} layouts to explore</p>
                    <h2 id="resume-template-picker-title" class="mt-1 text-xl font-bold tracking-tight text-ink">Choose a template</h2>
                    <p class="mt-2 text-sm text-muted">Click a layout to select it and update your live preview.</p>
                </div>
                <button type="button" @click="templatePickerOpen = false" class="shrink-0 rounded-lg border border-line px-3 py-2 text-sm font-semibold text-muted transition hover:bg-brand-50">Close</button>
            </header>
            <div class="min-h-0 flex-1 overflow-y-auto bg-cream p-4 sm:p-6">
                    <div class="grid grid-cols-1 gap-5 p-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        @forelse ($templates as $template)
                            <label wire:key="template-choice-{{ $template->id }}" class="group relative min-w-0 cursor-pointer">
                                <input type="radio" wire:model.live="overview.template_id" name="resume-template" @click="templatePickerOpen = false" value="{{ $template->id }}" class="peer sr-only" aria-label="{{ $template->name }} — {{ $template->category }}">
                                <div class="h-full overflow-hidden rounded-xl border border-line bg-white transition group-hover:border-brand-300 peer-checked:border-brand-600 peer-checked:ring-2 peer-checked:ring-brand-600/20 peer-focus-visible:outline-3 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-apricot">
                                    <div aria-hidden="true" class="pointer-events-none border-b border-line bg-slate-50 p-2">
                                        <x-resume-template-thumbnail :template="$template" compact height-class="h-64" class="rounded-md" />
                                    </div>
                                    <div class="p-3">
                                        <p class="text-sm font-semibold text-ink">{{ $template->name }}</p>
                                        <p class="mt-1 text-xs text-muted">{{ $template->category }} · {{ str($template->layout)->headline() }}</p>
                                    </div>
                                </div>
                                <span aria-hidden="true" class="pointer-events-none absolute right-3 top-3 rounded-full bg-brand-700 px-2.5 py-1 text-[10px] font-semibold text-white opacity-0 shadow-sm transition peer-checked:opacity-100">✓ Selected</span>
                            </label>
                        @empty
                            <p class="rounded-xl border border-dashed border-line p-5 text-sm text-muted sm:col-span-2 2xl:col-span-3">No templates are available right now. Your current resume layout will be kept.</p>
                        @endforelse
                    </div>
            </div>
            <footer class="shrink-0 border-t border-line px-5 py-3 text-xs text-muted sm:px-6">Your resume content stays the same when you change layouts.</footer>
        </div>
    </div>

</div>
