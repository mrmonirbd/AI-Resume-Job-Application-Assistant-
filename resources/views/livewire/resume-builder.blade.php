<div class="grid gap-5 xl:grid-cols-[220px_minmax(0,1fr)_420px]">
    <aside class="rounded-lg border border-black/10 bg-white p-4 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-neutral-500">Sections</p>
        <nav class="mt-4 space-y-1 text-sm font-semibold">
            @foreach ($sections as $id => $section)
                <a href="#section-{{ $id }}" class="block rounded-md px-3 py-2 text-neutral-600 hover:bg-[#f6f7f9] hover:text-neutral-950">{{ $section['title'] }}</a>
            @endforeach
        </nav>
        <div class="mt-6 rounded-lg border border-dashed border-black/15 p-3 text-xs leading-5 text-neutral-500">
            Master data is the verified source. Tailored resumes are separate versions.
        </div>
    </aside>

    <section class="min-w-0 space-y-5">
        @if ($notice)
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ $notice }}</div>
        @endif

        <form wire:submit.prevent="saveOverview" class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-black">Overview</h2>
                <button type="submit" class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-black text-white">Save</button>
            </div>
            <div class="mt-5 grid gap-4 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-semibold">Resume name</span>
                    <input wire:model.defer="overview.title" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                    @error('overview.title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Professional title</span>
                    <input wire:model.defer="overview.professional_title" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Template</span>
                    <select wire:model.defer="overview.template_id" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }} · {{ $template->category }}</option>
                        @endforeach
                    </select>
                    @error('overview.template_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Accent color</span>
                    <input wire:model.defer="overview.accent_color" type="color" class="mt-2 h-12 w-full rounded-lg border border-black/10 px-2 py-2">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Spacing</span>
                    <select wire:model.defer="overview.spacing" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                        <option value="compact">Compact</option>
                        <option value="comfortable">Comfortable</option>
                        <option value="spacious">Spacious</option>
                    </select>
                </label>
                <label class="block md:col-span-2">
                    <span class="flex items-center justify-between gap-3">
                        <span class="text-sm font-semibold">Professional summary</span>
                        <button type="button" wire:click="improveSummary('make_professional')" class="rounded-md border border-black/10 px-3 py-1.5 text-xs font-black">✨ AI</button>
                    </span>
                    <textarea wire:model.defer="overview.professional_summary" rows="5" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm"></textarea>
                </label>
            </div>
        </form>

        @if ($aiSuggestion)
            <div class="rounded-lg border border-teal-200 bg-teal-50 p-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-sm font-black text-teal-900">AI suggestion ready</p>
                        <p class="mt-2 text-sm leading-6 text-teal-800">{{ $aiSuggestion['ai_rewrite'] ?? '' }}</p>
                        <p class="mt-3 text-xs font-semibold text-teal-700">{{ $aiSuggestion['ai_recommendation'] ?? 'Review before accepting.' }}</p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <button type="button" wire:click="acceptSuggestion" class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-black text-white">Accept</button>
                        <button type="button" wire:click="improveSummary('make_professional')" class="rounded-lg border border-teal-300 px-4 py-2 text-sm font-bold">Try Again</button>
                        <button type="button" wire:click="discardSuggestion" class="rounded-lg border border-teal-300 px-4 py-2 text-sm font-bold">Discard</button>
                    </div>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="saveSections" class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-black">Resume sections</h2>
                <button class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-black text-white">Save sections</button>
            </div>
            <div class="mt-5 space-y-4">
                @foreach ($sections as $id => $section)
                    <div id="section-{{ $id }}" class="rounded-lg border border-black/10 p-4">
                        <div class="grid gap-3 md:grid-cols-[1fr_110px_80px]">
                            <input wire:model.defer="sections.{{ $id }}.title" class="rounded-lg border border-black/10 px-3 py-2 text-sm font-semibold">
                            <input wire:model.defer="sections.{{ $id }}.sort_order" type="number" class="rounded-lg border border-black/10 px-3 py-2 text-sm">
                            <label class="flex items-center gap-2 text-sm font-semibold">
                                <input wire:model.defer="sections.{{ $id }}.is_visible" type="checkbox" class="rounded border-black/20">
                                Show
                            </label>
                        </div>
                        <textarea wire:model.defer="sections.{{ $id }}.content" rows="3" class="mt-3 w-full rounded-lg border border-black/10 px-3 py-2 text-sm" placeholder="Optional notes for this section"></textarea>
                    </div>
                @endforeach
            </div>
        </form>

        <div class="grid gap-5 lg:grid-cols-2">
            <form wire:submit.prevent="addSkill" class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black">Skills</h2>
                <div class="mt-4 grid gap-3">
                    <input wire:model.defer="newSkill.name" class="rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="Laravel">
                    <input wire:model.defer="newSkill.category" class="rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="Backend">
                    <button class="rounded-lg bg-neutral-950 px-4 py-3 text-sm font-black text-white">Add skill</button>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($skills as $skill)
                        <span wire:key="skill-{{ $skill->id }}" class="inline-flex items-center gap-2 rounded-full border border-black/10 px-3 py-1 text-xs font-bold">
                            {{ $skill->name }}
                            <button type="button" wire:click="removeSkill({{ $skill->id }})" class="text-neutral-400 hover:text-red-600">×</button>
                        </span>
                    @endforeach
                </div>
            </form>

            <form wire:submit.prevent="addEducation" class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-black">Education</h2>
                <div class="mt-4 grid gap-3">
                    <input wire:model.defer="newEducation.school" class="rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="School">
                    <input wire:model.defer="newEducation.degree" class="rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="Degree">
                    <input wire:model.defer="newEducation.field" class="rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="Field">
                    <button class="rounded-lg bg-neutral-950 px-4 py-3 text-sm font-black text-white">Add education</button>
                </div>
                <div class="mt-4 space-y-2">
                    @foreach ($educations as $education)
                        <div wire:key="education-{{ $education->id }}" class="flex items-start justify-between gap-3 rounded-lg border border-black/10 p-3">
                            <p class="text-sm"><strong>{{ $education->school }}</strong><br><span class="text-neutral-500">{{ $education->degree }} {{ $education->field }}</span></p>
                            <button type="button" wire:click="removeEducation({{ $education->id }})" class="text-sm font-bold text-red-600">Remove</button>
                        </div>
                    @endforeach
                </div>
            </form>
        </div>

        <form wire:submit.prevent="addExperience" class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-black">Work experience</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <input wire:model.defer="newExperience.company" class="rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="Company">
                <input wire:model.defer="newExperience.position" class="rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="Position">
                <input wire:model.defer="newExperience.location" class="rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="Location">
                <div class="grid grid-cols-2 gap-3">
                    <input wire:model.defer="newExperience.start_date" class="rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="Start">
                    <input wire:model.defer="newExperience.end_date" class="rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="End">
                </div>
                <textarea wire:model.defer="newExperience.description" rows="3" class="rounded-lg border border-black/10 px-4 py-3 text-sm md:col-span-2" placeholder="Verified responsibility summary"></textarea>
                <textarea wire:model.defer="newExperience.bullets" rows="4" class="rounded-lg border border-black/10 px-4 py-3 text-sm md:col-span-2" placeholder="One verified bullet per line"></textarea>
            </div>
            <button class="mt-4 rounded-lg bg-neutral-950 px-4 py-3 text-sm font-black text-white">Add experience</button>
            <div class="mt-5 space-y-3">
                @foreach ($experiences as $experience)
                    <div wire:key="experience-{{ $experience->id }}" class="rounded-lg border border-black/10 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm"><strong>{{ $experience->position }}</strong> at {{ $experience->company }}<br><span class="text-neutral-500">{{ $experience->start_date }} - {{ $experience->end_date ?: 'Present' }}</span></p>
                            <button type="button" wire:click="removeExperience({{ $experience->id }})" class="text-sm font-bold text-red-600">Remove</button>
                        </div>
                        @if ($experience->description)
                            <p class="mt-3 text-sm leading-6 text-neutral-600">{{ $experience->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </form>

        <form wire:submit.prevent="addProject" class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-black">Projects</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                <input wire:model.defer="newProject.name" class="rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="Project name">
                <input wire:model.defer="newProject.role" class="rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="Role">
                <input wire:model.defer="newProject.url" class="rounded-lg border border-black/10 px-4 py-3 text-sm md:col-span-2" placeholder="URL">
                <textarea wire:model.defer="newProject.description" rows="3" class="rounded-lg border border-black/10 px-4 py-3 text-sm md:col-span-2" placeholder="Verified project description"></textarea>
                <input wire:model.defer="newProject.technologies" class="rounded-lg border border-black/10 px-4 py-3 text-sm md:col-span-2" placeholder="Technologies, comma separated">
                <textarea wire:model.defer="newProject.bullets" rows="3" class="rounded-lg border border-black/10 px-4 py-3 text-sm md:col-span-2" placeholder="One verified bullet per line"></textarea>
            </div>
            <button class="mt-4 rounded-lg bg-neutral-950 px-4 py-3 text-sm font-black text-white">Add project</button>
            <div class="mt-5 space-y-3">
                @foreach ($projects as $project)
                    <div wire:key="project-{{ $project->id }}" class="rounded-lg border border-black/10 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm"><strong>{{ $project->name }}</strong><br><span class="text-neutral-500">{{ implode(', ', $project->technologies ?? []) }}</span></p>
                            <button type="button" wire:click="removeProject({{ $project->id }})" class="text-sm font-bold text-red-600">Remove</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </form>
    </section>

    <aside class="rounded-lg border border-black/10 bg-white p-5 shadow-sm xl:sticky xl:top-6 xl:h-[calc(100vh-3rem)] xl:overflow-auto">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-black">Live preview</h2>
            <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-bold">{{ $resume->template?->name ?? 'Template' }}</span>
        </div>
        <x-resume-document-preview
            :resume="$resume"
            :overview="$overview"
            :experiences="$experiences"
            :educations="$educations"
            :skills="$skills"
            :projects="$projects"
            :certifications="$certifications"
            :languages="$languages"
            :links="$links"
            class="mt-5"
        />
    </aside>
</div>
