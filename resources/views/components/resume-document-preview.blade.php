@props([
    'resume',
    'overview',
    'previewTemplate' => null,
    'experiences',
    'educations',
    'skills',
    'projects',
    'certifications' => collect(),
    'languages' => collect(),
    'links' => collect(),
    'sections' => [],
])

@php
    $template = $previewTemplate ?? $resume->template;
    $settings = $template?->settings ?? [];
    $slug = $template?->slug ?? 'default';
    $style = data_get($settings, 'style', 'classic');
    $accent = $overview['accent_color'] ?? $resume->accent_color ?? '#2563eb';
    $soft = data_get($settings, 'soft', '#eef2ff');
    $dark = data_get($settings, 'dark', '#111827');
    $surface = data_get($settings, 'surface', '#ffffff');
    $title = $overview['professional_title'] ?? $resume->user->professional_title;
    $summary = $overview['professional_summary'] ?? null;
    $sectionSettings = collect($sections)->keyBy('type');
    if (data_get($sectionSettings->get('summary'), 'is_visible', true) === false) {
        $summary = null;
    }
    foreach (['experience' => 'experiences', 'education' => 'educations', 'skills' => 'skills', 'projects' => 'projects', 'certifications' => 'certifications', 'languages' => 'languages'] as $type => $collectionName) {
        if (data_get($sectionSettings->get($type), 'is_visible', true) === false) {
            $$collectionName = collect();
        }
    }
    $spacing = $overview['spacing'] ?? 'comfortable';
    $sectionSpacing = ['compact' => 'mt-3', 'spacious' => 'mt-7'][$spacing] ?? 'mt-5';
    $bodyText = $spacing === 'compact' ? 'text-[11px]' : 'text-[12px]';
    $hasPhoto = (bool) data_get($settings, 'photo', false);
    $contact = collect([$resume->contactEmail(), $resume->user->location])->filter();
    $hasSidebar = in_array($style, ['sidebar', 'soft-sidebar', 'right-rail', 'technical'], true);
    $usesDarkSidebar = in_array($style, ['sidebar', 'right-rail', 'technical'], true);
    $sidebarBackground = $usesDarkSidebar ? $dark : $soft;
    $sidebarText = $usesDarkSidebar ? '#ffffff' : '#111827';
    $sidebarGridClass = data_get($settings, 'sidebar') === 'right'
        ? 'grid-cols-[1.55fr_0.78fr] [&>aside]:order-2 [&>main]:order-1'
        : 'grid-cols-[0.78fr_1.55fr]';
@endphp

<div data-preview-template="{{ $slug }}" data-preview-style="{{ $style }}" {{ $attributes->merge(['class' => "min-h-[720px] overflow-hidden rounded-md border border-black/10 bg-white {$bodyText} leading-relaxed shadow-sm"]) }} style="font-family: {{ $overview['font_family'] ?? $resume->font_family ?? 'Inter' }}, Arial, sans-serif; background: {{ $surface }};">
    @if ($hasSidebar)
        <div class="grid min-h-[720px] {{ $sidebarGridClass }}">
            <aside class="p-5" style="background: {{ $sidebarBackground }}; color: {{ $sidebarText }};">
                @if ($hasPhoto)
                    <x-demo-profile-image :resume="$resume" class="mb-5 h-20 w-20 border-4 border-white/80" />
                @endif
                <h3 class="text-xl font-black uppercase leading-tight tracking-wide">{{ $resume->contactName() }}</h3>
                @if ($title)
                    <p class="mt-2 text-xs font-semibold opacity-80">{{ $title }}</p>
                @endif

                @if ($contact->isNotEmpty())
                    <section class="{{ $sectionSpacing }}">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.18em] opacity-70">Contact</h4>
                        <div class="mt-2 space-y-1.5 opacity-85">
                            @foreach ($contact as $item)
                                <p>{{ $item }}</p>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($skills->isNotEmpty())
                    <section class="{{ $sectionSpacing }}">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.18em] opacity-70">{{ data_get($sectionSettings->get('skills'), 'title', 'Skills') }}</h4>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach ($skills->take(10) as $skill)
                                <span class="rounded-full bg-white/15 px-2 py-1 text-[10px] font-semibold">{{ $skill->name }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($educations->isNotEmpty())
                    <section class="{{ $sectionSpacing }}">
                        <h4 class="text-[10px] font-black uppercase tracking-[0.18em] opacity-70">{{ data_get($sectionSettings->get('education'), 'title', 'Education') }}</h4>
                        <div class="mt-2 space-y-2">
                            @foreach ($educations->take(2) as $education)
                                <p><strong>{{ $education->school }}</strong><br><span class="opacity-75">{{ $education->degree }} {{ $education->field }}</span></p>
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>

            <main class="p-6">
                @if ($summary)
                    <section>
                        <h4 class="text-[11px] font-black uppercase tracking-[0.18em]" style="color: {{ $accent }};">{{ data_get($sectionSettings->get('summary'), 'title', 'Profile') }}</h4>
                        <p class="mt-2 text-neutral-700">{{ $summary }}</p>
                    </section>
                @endif

                @if ($experiences->isNotEmpty())
                    <section class="{{ $sectionSpacing }}">
                        <h4 class="border-b pb-1 text-[11px] font-black uppercase tracking-[0.18em]" style="border-color: {{ $accent }};">{{ data_get($sectionSettings->get('experience'), 'title', 'Work Experience') }}</h4>
                        <div class="mt-3 space-y-4">
                            @foreach ($experiences as $experience)
                                <div>
                                    <div class="flex items-start justify-between gap-3">
                                        <p class="font-black">{{ $experience->position }} · {{ $experience->company }}</p>
                                        <p class="shrink-0 text-[10px] text-neutral-500">{{ $experience->start_date }} - {{ $experience->end_date ?: 'Present' }}</p>
                                    </div>
                                    @if ($experience->description)
                                        <p class="mt-1 text-neutral-700">{{ $experience->description }}</p>
                                    @endif
                                    @if ($experience->bullets)
                                        <ul class="mt-2 list-disc space-y-1 pl-4 text-neutral-700">
                                            @foreach ($experience->bullets as $bullet)
                                                <li>{{ $bullet }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($projects->isNotEmpty())
                    <section class="{{ $sectionSpacing }}">
                        <h4 class="border-b pb-1 text-[11px] font-black uppercase tracking-[0.18em]" style="border-color: {{ $accent }};">{{ data_get($sectionSettings->get('projects'), 'title', 'Projects') }}</h4>
                        <div class="mt-3 space-y-3">
                            @foreach ($projects as $project)
                                <p><strong>{{ $project->name }}</strong><br><span class="text-neutral-600">{{ $project->description }}</span></p>
                            @endforeach
                        </div>
                    </section>
                @endif
            </main>
        </div>
    @elseif (in_array($style, ['top-band', 'photo-band', 'feature', 'dark-header', 'stripe', 'edge'], true))
        <header @class(['relative overflow-hidden px-7 py-6', 'border-l-[8px] border-t-[8px]' => $style === 'edge', 'border-b-4' => in_array($style, ['top-band', 'photo-band'], true)]) style="background: {{ $style === 'dark-header' ? $dark : $soft }}; border-color: {{ $accent }};">
            @if ($style === 'stripe')
                <div class="absolute right-0 top-0 h-24 w-24 -translate-y-10 translate-x-10 rotate-45" style="background: {{ $accent }};"></div>
            @endif
            <div class="relative flex items-center gap-5">
                @if ($hasPhoto)
                    <x-demo-profile-image :resume="$resume" class="h-20 w-20 shrink-0 border-4 border-white" />
                @endif
                <div>
                    <h3 class="text-3xl font-black uppercase leading-none tracking-wide" style="color: {{ $style === 'dark-header' ? '#ffffff' : '#111827' }};">{{ $resume->contactName() }}</h3>
                    @if ($title)
                        <p class="mt-2 text-sm font-semibold" style="color: {{ $style === 'dark-header' ? '#ffffff' : $accent }};">{{ $title }}</p>
                    @endif
                    <p class="mt-2 text-[11px]" style="color: {{ $style === 'dark-header' ? '#d1d5db' : '#4b5563' }};">{{ $contact->join(' · ') }}</p>
                </div>
            </div>
        </header>
        <div class="grid grid-cols-[minmax(0,1.35fr)_minmax(0,0.75fr)] gap-4 p-5">
            <main>
                @if ($summary)
                    <section>
                        <h4 class="text-[11px] font-black uppercase tracking-[0.18em]" style="color: {{ $accent }};">{{ data_get($sectionSettings->get('summary'), 'title', 'About Me') }}</h4>
                        <p class="mt-2 text-neutral-700">{{ $summary }}</p>
                    </section>
                @endif
                @if ($experiences->isNotEmpty())
                    <section class="{{ $sectionSpacing }}">
                        <h4 class="text-[11px] font-black uppercase tracking-[0.18em]" style="color: {{ $accent }};">{{ data_get($sectionSettings->get('experience'), 'title', 'Experience') }}</h4>
                        <div class="mt-3 space-y-4">
                            @foreach ($experiences as $experience)
                                <div>
                                    <p class="font-black">{{ $experience->position }}</p>
                                    <p class="text-neutral-500">{{ $experience->company }} · {{ $experience->start_date }} - {{ $experience->end_date ?: 'Present' }}</p>
                                    <p class="mt-1 text-neutral-700">{{ $experience->description }}</p>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </main>
            <aside class="space-y-5">
                @if ($skills->isNotEmpty())
                    <section class="rounded-md p-4" style="background: {{ $soft }};">
                        <h4 class="text-[11px] font-black uppercase tracking-[0.18em]">{{ data_get($sectionSettings->get('skills'), 'title', 'Skills') }}</h4>
                        <p class="mt-2 text-neutral-700">{{ $skills->pluck('name')->join(' · ') }}</p>
                    </section>
                @endif
                @if ($educations->isNotEmpty())
                    <section>
                        <h4 class="text-[11px] font-black uppercase tracking-[0.18em]" style="color: {{ $accent }};">{{ data_get($sectionSettings->get('education'), 'title', 'Education') }}</h4>
                        @foreach ($educations as $education)
                            <p class="mt-2"><strong>{{ $education->school }}</strong><br><span class="text-neutral-600">{{ $education->degree }} {{ $education->field }}</span></p>
                        @endforeach
                    </section>
                @endif
                @if ($certifications->isNotEmpty())
                    <section>
                        <h4 class="text-[11px] font-black uppercase tracking-[0.18em]" style="color: {{ $accent }};">{{ data_get($sectionSettings->get('certifications'), 'title', 'Certifications') }}</h4>
                        @foreach ($certifications as $certification)
                            <p class="mt-2"><strong>{{ $certification->name }}</strong><br><span class="text-neutral-600">{{ $certification->issuer }}</span></p>
                        @endforeach
                    </section>
                @endif
            </aside>
        </div>
    @elseif ($style === 'timeline')
        <div class="p-7">
            <header class="flex items-center gap-5 border-b pb-5" style="border-color: {{ $accent }};">
                <x-demo-profile-image :resume="$resume" class="h-20 w-20 shrink-0 border-4 border-white shadow-sm" />
                <div>
                    <h3 class="text-3xl font-black uppercase leading-none">{{ $resume->contactName() }}</h3>
                    <p class="mt-2 font-semibold" style="color: {{ $accent }};">{{ $title }}</p>
                    <p class="mt-1 text-neutral-500">{{ $contact->join(' · ') }}</p>
                </div>
            </header>
            @if ($summary)
                <section class="{{ $sectionSpacing }}">
                    <h4 class="text-[11px] font-black uppercase tracking-[0.18em] text-neutral-500">{{ data_get($sectionSettings->get('summary'), 'title', 'About Me') }}</h4>
                    <p class="mt-2 text-neutral-700">{{ $summary }}</p>
                </section>
            @endif
            @if ($experiences->isNotEmpty())
                <section class="{{ $sectionSpacing }} relative space-y-4 pl-7">
                    <div class="absolute bottom-0 left-2 top-0 w-px bg-neutral-300"></div>
                    @foreach ($experiences as $experience)
                        <div class="relative">
                            <span class="absolute -left-[1.7rem] top-1 h-3 w-3 rounded-full" style="background: {{ $accent }};"></span>
                            <h4 class="font-black">{{ $experience->position }} · {{ $experience->company }}</h4>
                            <p class="text-neutral-500">{{ $experience->start_date }} - {{ $experience->end_date ?: 'Present' }}</p>
                            <p class="mt-1 text-neutral-700">{{ $experience->description }}</p>
                        </div>
                    @endforeach
                </section>
            @endif
            @if ($skills->isNotEmpty())
                <section class="{{ $sectionSpacing }}">
                    <h4 class="text-[11px] font-black uppercase tracking-[0.18em]" style="color: {{ $accent }};">{{ data_get($sectionSettings->get('skills'), 'title', 'Skills') }}</h4>
                    <p class="mt-2 text-neutral-700">{{ $skills->pluck('name')->join(' · ') }}</p>
                </section>
            @endif
        </div>
    @else
        <div @class(['p-7', 'text-[11px]' => in_array($style, ['dense', 'compact'], true)])>
            @if ($slug === 'operator-compact')
                <header class="grid grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)] gap-4 border-b pb-4" style="border-color: {{ $accent }};">
                    <div>
                        <h3 class="text-xl font-black uppercase tracking-wide">{{ $resume->contactName() }}</h3>
                        <p class="mt-1 font-semibold" style="color: {{ $accent }};">{{ $title }}</p>
                    </div>
                    <p class="break-words text-[10px] text-neutral-500">{{ $contact->join(' · ') }}</p>
                </header>
            @elseif ($style === 'split')
                <header class="grid grid-cols-[minmax(0,1fr)_0.4fr] gap-4">
                    <div class="py-3">
                        <h3 class="text-2xl font-black uppercase tracking-wide">{{ $resume->contactName() }}</h3>
                        <p class="mt-2 text-sm font-semibold" style="color: {{ $accent }};">{{ $title }}</p>
                        <p class="mt-2 break-words text-[10px] text-neutral-500">{{ $contact->join(' · ') }}</p>
                    </div>
                    <div class="min-h-32" style="background: {{ $accent }};" aria-hidden="true"></div>
                </header>
            @elseif ($style === 'balanced')
                <header class="flex items-start justify-between gap-3 border-b-2 pb-5" style="border-color: {{ $accent }};">
                    <div class="min-w-0">
                        <h3 class="text-2xl font-black uppercase tracking-wide">{{ $resume->contactName() }}</h3>
                        <p class="mt-2 font-semibold" style="color: {{ $accent }};">{{ $title }}</p>
                        <p class="mt-2 break-words text-[10px] text-neutral-500">{{ $contact->join(' · ') }}</p>
                    </div>
                    <span class="size-10 shrink-0 rounded-full" style="background: {{ $soft }};" aria-hidden="true"></span>
                </header>
            @elseif ($style === 'brief')
                <header class="border-l-4 pl-4" style="border-color: {{ $accent }};">
                    <h3 class="text-2xl font-black uppercase tracking-wide">{{ $resume->contactName() }}</h3>
                    <p class="mt-1 font-semibold" style="color: {{ $accent }};">{{ $title }}</p>
                    <p class="mt-2 text-neutral-500">{{ $contact->join(' · ') }}</p>
                </header>
            @else
                <header @class([
                    'pb-4',
                    'border-b' => $style !== 'minimal',
                    'border-b-2' => $slug === 'graduate-focus',
                    'text-center' => in_array(data_get($settings, 'header'), ['centered', 'small-center', 'formal'], true) || $style === 'editorial',
                    'tracking-[0.12em]' => $style === 'editorial',
                ]) style="border-color: {{ $accent }};">
                    <h3 @class(['font-black uppercase tracking-wide', 'text-xl' => in_array($style, ['dense', 'compact'], true), 'text-2xl' => ! in_array($style, ['dense', 'compact'], true)])>{{ $resume->contactName() }}</h3>
                    @if ($title)
                        <p class="mt-1 text-sm font-semibold" style="color: {{ $accent }};">{{ $title }}</p>
                    @endif
                    <p class="mt-2 text-neutral-500">{{ $contact->join(' · ') }}</p>
                </header>
            @endif

            <main @class([
                'mt-5',
                'grid grid-cols-2 items-start gap-x-4' => in_array($style, ['balanced', 'compact'], true),
                'grid grid-cols-[minmax(0,1.4fr)_minmax(0,0.8fr)] items-start gap-x-4' => $style === 'split',
                'grid grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] items-start gap-x-4 [&>section]:col-start-2' => $slug === 'operator-compact',
                'columns-2 gap-5' => $style === 'dense',
                '[&>section]:break-inside-avoid' => $style === 'dense',
                '[&>section]:border-b [&>section]:border-neutral-200 [&>section]:pb-3 [&>section]:grid [&>section]:grid-cols-[minmax(0,0.7fr)_minmax(0,1.3fr)] [&>section]:gap-3 [&>section>h4]:mt-2' => $style === 'brief',
                '[&>section]:border-t [&>section]:border-neutral-200 [&>section]:pt-4' => $style === 'minimal',
                '[&>section>h4]:border-b [&>section>h4]:border-neutral-300 [&>section>h4]:pb-2' => $style === 'academic',
                '[&>section>p]:border-l [&>section>p]:border-neutral-300 [&>section>p]:pl-3' => $style === 'editorial',
            ])>
            @if ($style === 'academic' && $educations->isNotEmpty())
                <section>
                    <h4 class="text-[11px] font-black uppercase tracking-[0.18em]" style="color: {{ $accent }};">{{ data_get($sectionSettings->get('education'), 'title', 'Education') }}</h4>
                    @foreach ($educations as $education)
                        <p class="mt-2"><strong>{{ $education->school }}</strong>{{ $education->degree ? ' · '.$education->degree : '' }}<br><span class="text-neutral-500">{{ $education->start_date }} - {{ $education->end_date }}</span></p>
                    @endforeach
                </section>
            @endif
            @if ($summary)
                <section class="{{ $sectionSpacing }}">
                    <h4 class="text-[11px] font-black uppercase tracking-[0.18em] text-neutral-500">{{ data_get($sectionSettings->get('summary'), 'title', 'Summary') }}</h4>
                    <p class="mt-2 text-neutral-700">{{ $summary }}</p>
                </section>
            @endif

            @if ($skills->isNotEmpty())
                <section @class([$sectionSpacing, 'col-start-2 row-start-1 rounded-xl p-3' => $style === 'split', '!col-start-1 row-start-1 row-span-5 rounded-md p-3' => $slug === 'operator-compact']) style="{{ ($style === 'split' || $slug === 'operator-compact') ? 'background: '.$soft.';' : '' }}">
                    <h4 class="text-[11px] font-black uppercase tracking-[0.18em] text-neutral-500">{{ data_get($sectionSettings->get('skills'), 'title', 'Skills') }}</h4>
                    <p class="mt-2 text-neutral-700">{{ $skills->pluck('name')->join(' · ') }}</p>
                </section>
            @endif

            @if ($experiences->isNotEmpty())
                <section @class([$sectionSpacing, 'col-start-1 row-start-2' => $style === 'split'])>
                    <h4 class="text-[11px] font-black uppercase tracking-[0.18em] text-neutral-500">{{ data_get($sectionSettings->get('experience'), 'title', 'Experience') }}</h4>
                    <div class="mt-3 space-y-4">
                        @foreach ($experiences as $experience)
                            <div>
                                <p class="font-black">{{ $experience->position }} · {{ $experience->company }}</p>
                                <p class="text-neutral-500">{{ $experience->start_date }} - {{ $experience->end_date ?: 'Present' }}{{ $experience->location ? ' · '.$experience->location : '' }}</p>
                                @if ($experience->description)
                                    <p class="mt-1 text-neutral-700">{{ $experience->description }}</p>
                                @endif
                                @if ($experience->bullets)
                                    <ul class="mt-2 list-disc space-y-1 pl-4 text-neutral-700">
                                        @foreach ($experience->bullets as $bullet)
                                            <li>{{ $bullet }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($projects->isNotEmpty())
                <section @class([$sectionSpacing, 'col-start-1' => $style === 'split'])>
                    <h4 class="text-[11px] font-black uppercase tracking-[0.18em] text-neutral-500">{{ data_get($sectionSettings->get('projects'), 'title', 'Projects') }}</h4>
                    <div class="mt-3 space-y-3">
                        @foreach ($projects as $project)
                            <p><strong>{{ $project->name }}</strong><br><span class="text-neutral-600">{{ $project->description }}</span></p>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($style !== 'academic' && $educations->isNotEmpty())
                <section @class([$sectionSpacing, 'col-start-2 row-start-2' => $style === 'split'])>
                    <h4 class="text-[11px] font-black uppercase tracking-[0.18em] text-neutral-500">{{ data_get($sectionSettings->get('education'), 'title', 'Education') }}</h4>
                    @foreach ($educations as $education)
                        <p class="mt-2"><strong>{{ $education->school }}</strong>{{ $education->degree ? ' · '.$education->degree : '' }}</p>
                    @endforeach
                </section>
            @endif
            </main>
        </div>
    @endif

    @if (($style === 'timeline' || in_array($style, ['top-band', 'photo-band', 'feature', 'dark-header', 'stripe', 'edge'], true)) && $projects->isNotEmpty())
        <section class="mx-7 mb-5">
            <h4 class="text-[11px] font-black uppercase tracking-[0.18em]" style="color: {{ $accent }};">{{ data_get($sectionSettings->get('projects'), 'title', 'Projects') }}</h4>
            @foreach ($projects as $project)
                <p class="mt-3"><strong>{{ $project->name }}</strong><br>{{ $project->description }}</p>
            @endforeach
        </section>
    @endif
    @if ($style === 'timeline' && $educations->isNotEmpty())
        <section class="mx-7 mb-5">
            <h4 class="text-[11px] font-black uppercase tracking-[0.18em]" style="color: {{ $accent }};">{{ data_get($sectionSettings->get('education'), 'title', 'Education') }}</h4>
            @foreach ($educations as $education)
                <p class="mt-3"><strong>{{ $education->school }}</strong><br>{{ $education->degree }} {{ $education->field }}</p>
            @endforeach
        </section>
    @endif
    @foreach (collect($sections)->sortBy('sort_order') as $section)
        @if (($section['is_visible'] ?? true) && filled($section['content'] ?? null))
            <section class="mx-7 mb-5 border-t border-neutral-200 pt-4">
                <h4 class="text-[11px] font-bold uppercase tracking-wide" style="color: {{ $accent }};">{{ $section['title'] ?? '' }}</h4>
                <p class="mt-2 whitespace-pre-line text-neutral-700">{{ $section['content'] }}</p>
            </section>
        @endif
    @endforeach

</div>
