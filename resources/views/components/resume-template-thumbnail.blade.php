@props(['template', 'compact' => false, 'heightClass' => null, 'preview' => false])

@php
    $settings = $template->settings ?? [];
    $style = data_get($settings, 'style', 'classic');
    $slug = $template->slug ?: str($template->name)->slug()->toString();
    $variant = $slug;
    $header = data_get($settings, 'header', 'compact');
    $accent = $template->accent_color ?: '#2563eb';
    $soft = data_get($settings, 'soft', '#eef2ff');
    $dark = data_get($settings, 'dark', '#111827');
    $surface = data_get($settings, 'surface', '#ffffff');
    $heightClass = $heightClass ?? ($preview ? 'aspect-[210/297] h-auto' : ($compact ? 'h-64' : 'h-80'));
    $tiny = $preview ? 'text-[10px] leading-[1.65]' : ($compact ? 'text-[6px] leading-[1.3]' : 'text-[7px] leading-[1.55]');
    $section = $preview ? 'text-[10px]' : ($compact ? 'text-[6px]' : 'text-[7px]');
    $pagePadding = $preview ? 'p-7' : 'p-5';
    $panelPadding = $preview ? 'p-6' : 'p-4';
    $nameSize = $preview ? 'text-[22px]' : 'text-[14px]';
    $compactNameSize = $preview ? 'text-[19px]' : 'text-[13px]';
    $bandHeight = $preview ? 'h-32' : 'h-24';
    $sampleContent = match ($template->category) {
        'Executive' => [
            'Finance leader with 10+ years of experience in planning, reporting, and business strategy.',
            'Finance Manager · Harbor Financial, New York · 2021–Present.',
            'MBA, Finance · Boston University · 2016–2018.',
            'Led quarterly planning for a cross-functional team of 12.',
        ],
        'Creative' => [
            'Brand designer creating thoughtful identities and digital experiences for growing businesses.',
            'Senior Designer · Studio North, Berlin · 2021–Present.',
            'BA, Communication Design · University of the Arts London · 2016–2019.',
            'Built a design system used across five digital products.',
        ],
        'Technical' => [
            'Software engineer building reliable APIs, cloud services, and accessible web applications.',
            'Senior Engineer · Northstar Labs, Austin · 2021–Present.',
            'BS, Computer Science · University of Texas at Austin · 2015–2019.',
            'Improved API response times by 35% through profiling and caching.',
        ],
        'Academic', 'Graduate' => [
            'Research professional focused on data analysis, collaboration, and clear scientific communication.',
            'Research Assistant · University of Amsterdam · 2022–Present.',
            'MSc, Data Science · University of Amsterdam · 2020–2022.',
            'Presented research findings at a European research symposium.',
        ],
        default => [
            'Business analyst turning complex data into practical insights and better customer experiences.',
            'Business Analyst · Northstar Group, London · 2021–Present.',
            'BSc, Business Analytics · University of Manchester · 2016–2019.',
            'Automated monthly reporting and reduced preparation time by 30%.',
        ],
    };
    $demoLines = $sampleContent;
    $sampleSkills = match ($template->category) {
        'Creative' => 'Figma · Brand strategy · Typography · Design systems',
        'Technical' => 'PHP · Laravel · PostgreSQL · AWS · Automated testing',
        'Academic', 'Graduate' => 'Python · R · Statistics · Research methods · Academic writing',
        default => 'Data analysis · Excel · SQL · Stakeholder management · Reporting',
    };
    $sampleSections = [
        'Summary' => $sampleContent[0], 'Profile' => $sampleContent[0],
        'About Me' => $sampleContent[0], 'Executive Summary' => $sampleContent[0],
        'Impact Summary' => $sampleContent[0],
        'Experience' => $sampleContent[1], 'Work Experience' => $sampleContent[1],
        'Operations' => $sampleContent[1], 'Research' => $sampleContent[1],
        'Education' => $sampleContent[2],
        'Skills' => $sampleSkills, 'Tools' => $sampleSkills,
        'Highlights' => $sampleContent[3], 'Selected Wins' => $sampleContent[3],
        'Projects' => 'Customer Insights Dashboard · Built a reusable reporting workflow for distributed teams.',
        'Selected Projects' => 'Customer Insights Dashboard · Built a reusable reporting workflow for distributed teams.',
        'Capstone' => 'Open Data Research · Analyzed European public datasets and presented the findings.',
        'Certifications' => 'Professional development · Continuing education, 2024.',
        'Languages' => 'English · German', 'Language' => 'English · German',
        'Contact' => 'Available for hybrid and remote opportunities.',
    ];

    $fullSections = $preview
        ? ['Summary', 'Experience', 'Education', 'Projects', 'Certifications', 'Highlights']
        : ['Summary', 'Experience', 'Education', 'Skills', 'Highlights'];
    $splitSections = $preview
        ? ['Summary', 'Experience', 'Education', 'Selected Projects', 'Projects']
        : ['Summary', 'Experience', 'Education'];
    $sideItems = $preview
        ? ['Skills', 'Tools', 'Languages', 'Links', 'Awards']
        : ['Skills', 'Tools', 'Languages', 'Links'];
    $name = match ($style) {
        'editorial', 'academic' => 'Sophia Bennett',
        'timeline', 'technical' => 'James Carter',
        'photo-band', 'top-band', 'feature', 'dark-header' => 'Emma Wilson',
        default => 'John Doe',
    };
    $role = match ($template->category) {
        'Executive' => 'Finance Manager',
        'Creative' => 'Brand Designer',
        'Technical' => 'Backend Developer',
        'Academic', 'Graduate' => 'Research Assistant',
        default => 'Business Analyst',
    };
    $location = match ($template->category) {
        'Executive' => 'New York, NY',
        'Creative' => 'Berlin, Germany',
        'Technical' => 'Austin, TX',
        'Academic', 'Graduate' => 'Amsterdam, Netherlands',
        default => 'London, UK',
    };
    $email = str($name)->lower()->replace(' ', '.').'@example.com';
    $phone = match ($template->category) {
        'Executive', 'Technical' => '+1 (202) 555-0147',
        'Creative' => '+49 30 1234 5678',
        'Academic', 'Graduate' => '+31 20 123 4567',
        default => '+44 20 7946 0958',
    };
@endphp

<div
    data-template-style="{{ $style }}"
    data-template-variant="{{ $variant }}"
    data-template-layout="{{ $template->layout }}"
    {{ $attributes->merge(['class' => "relative overflow-hidden rounded-md border border-slate-200/80 bg-white text-slate-800 shadow-md shadow-slate-900/5 {$heightClass}"]) }}
    style="font-family: {{ $template->font_family }}, Arial, sans-serif; background: {{ $surface }};"
>
    @switch($style)
        @case('sidebar')
            @if ($slug === 'executive-ledger')
                <div class="grid h-full grid-cols-[0.78fr_1.48fr]">
                    <aside class="p-4" style="background: {{ $dark }}; color: #ffffff;">
                        <x-demo-profile-image class="h-14 w-14 rounded-md border-2 border-white/80" />
                        <p class="mt-5 {{ $section }} font-black uppercase tracking-[0.18em] opacity-80">Ledger</p>
                        <div class="mt-2 space-y-2">
                            @foreach (['Revenue', 'Risk', 'Budget'] as $item)
                                <div class="grid grid-cols-[1fr_1.2fr] gap-1">
                                    <span class="h-2 rounded-sm bg-white/25"></span>
                                    <span class="h-2 rounded-sm bg-white/45"></span>
                                </div>
                            @endforeach
                        </div>
                        <p class="mt-5 {{ $section }} font-black uppercase tracking-[0.18em] opacity-80">Contact</p>
                        <p class="{{ $tiny }} mt-2 opacity-75">{{ $email }}<br>{{ $location }}</p>
                    </aside>
                    <main class="p-5">
                        <h3 class="text-[13px] font-black uppercase tracking-wide">{{ $name }}</h3>
                        <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                        <div class="mt-5 space-y-3">
                            @foreach (['Executive Summary', 'Financial Control', 'Board Reporting', 'Education'] as $item)
                                <section class="grid grid-cols-[0.72fr_1.2fr] gap-3 border-b border-black/10 pb-2">
                                    <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                                    <p class="{{ $tiny }} text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                                </section>
                            @endforeach
                        </div>
                    </main>
                </div>
            @elseif ($slug === 'slate-manager')
                <div class="grid h-full grid-cols-[0.9fr_1.35fr]">
                    <aside class="p-4 text-center" style="background: {{ $dark }}; color: #ffffff;">
                        <div class="rounded-sm bg-white/10 p-2">
                            <x-demo-profile-image class="mx-auto h-16 w-16 border-4 border-white/80" />
                        </div>
                        <p class="mt-4 {{ $section }} font-black uppercase tracking-[0.16em] opacity-80">Manager</p>
                        <div class="mt-3 space-y-2">
                            @foreach (['Planning', 'Budgets', 'Teams', 'Reports'] as $item)
                                <p class="{{ $tiny }} rounded-sm bg-white/15 px-2 py-1 text-left">{{ $item }}</p>
                            @endforeach
                        </div>
                    </aside>
                    <main class="p-5">
                        <h3 class="text-[13px] font-black uppercase tracking-wide">{{ $name }}</h3>
                        <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                        <div class="mt-5 space-y-4">
                            @foreach (['Profile', 'Operations', 'People', 'Education'] as $item)
                                <section class="border-l-4 pl-3" style="border-color: {{ $loop->first ? $accent : '#d4d4d8' }};">
                                    <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                                    <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                                </section>
                            @endforeach
                        </div>
                    </main>
                </div>
            @elseif ($slug === 'marketing-sidebar')
                <div class="grid h-full grid-cols-[0.72fr_1.5fr]">
                    <aside class="p-4" style="background: {{ $dark }}; color: #ffffff;">
                        <x-demo-profile-image class="h-16 w-16 border-4 border-white/80" />
                        <p class="mt-4 {{ $section }} font-black uppercase tracking-[0.16em] opacity-80">Campaigns</p>
                        <div class="mt-3 space-y-2">
                            @foreach ([90, 76, 64, 82] as $width)
                                <div class="h-2 rounded-full bg-white/20">
                                    <div class="h-2 rounded-full bg-white/65" style="width: {{ $width }}%;"></div>
                                </div>
                            @endforeach
                        </div>
                    </aside>
                    <main class="p-5">
                        <h3 class="text-[13px] font-black uppercase tracking-wide">{{ $name }}</h3>
                        <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            @foreach (['Profile', 'Brand Growth', 'Market Research', 'Tools', 'Education', 'Awards'] as $item)
                                <section>
                                    <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                                    <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                                </section>
                            @endforeach
                        </div>
                    </main>
                </div>
            @else
                <div class="grid h-full grid-cols-[0.82fr_1.42fr]">
                    <aside class="p-4" style="background: {{ $dark }}; color: #ffffff;">
                        <x-demo-profile-image class="h-14 w-14 border-4 border-white/80" />
                        <p class="mt-4 {{ $section }} font-black uppercase tracking-[0.16em] opacity-80">Contact</p>
                        <p class="{{ $tiny }} mt-2 opacity-75">{{ $email }}<br>{{ $location }}<br>{{ $phone }}</p>
                        <p class="mt-4 {{ $section }} font-black uppercase tracking-[0.16em] opacity-80">Skills</p>
                        <div class="mt-2 space-y-1.5">
                            @foreach (['Risk', 'Finance', 'Reports', 'Excel'] as $skill)
                                <div class="h-1.5 rounded-full bg-white/35"></div>
                            @endforeach
                        </div>
                    </aside>
                    <main class="p-4">
                        <h3 class="text-[13px] font-black uppercase tracking-wide">{{ $name }}</h3>
                        <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                        <p class="mt-4 {{ $section }} font-black uppercase tracking-[0.16em]">Profile</p>
                        <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[0] }}</p>
                        <div class="mt-4 space-y-3">
                            @foreach (['Senior Analyst', 'Business Analyst', 'Reporting Associate'] as $job)
                                <section>
                                    <p class="{{ $tiny }} font-black">{{ $job }}</p>
                                    <p class="{{ $tiny }} text-neutral-500">2021 - Present</p>
                                    <div class="mt-1.5 h-1.5 w-full rounded-full bg-neutral-200"></div>
                                    <div class="mt-1 h-1.5 w-8/12 rounded-full bg-neutral-200"></div>
                                </section>
                            @endforeach
                        </div>
                    </main>
                </div>
            @endif
            @break

        @case('soft-sidebar')
            <div class="grid h-full grid-cols-[0.88fr_1.36fr]">
                <aside class="p-4 text-center" style="background: {{ $soft }};">
                    <x-demo-profile-image class="mx-auto h-16 w-16 border-4 border-white" />
                    <p class="mt-3 {{ $section }} font-black uppercase tracking-[0.16em]" style="color: {{ $accent }};">Profile</p>
                    <p class="{{ $tiny }} mt-2 text-neutral-700">{{ $sampleContent[0] }}</p>
                    <div class="mt-4 space-y-1.5 text-left">
                        @foreach (['Email', 'Phone', 'Location', 'LinkedIn'] as $item)
                            <p class="{{ $tiny }} rounded-sm bg-white/70 px-2 py-1">{{ $item }}</p>
                        @endforeach
                    </div>
                </aside>
                <main class="p-4">
                    <h3 class="text-[13px] font-black uppercase tracking-wide">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                    <div class="mt-4 grid gap-3">
                        @foreach (['Education', 'Work Experience', 'Language', 'Skills'] as $item)
                            <section class="border-l-2 pl-2" style="border-color: {{ $accent }};">
                                <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                            </section>
                        @endforeach
                    </div>
                </main>
            </div>
            @break

        @case('right-rail')
            <div class="grid h-full grid-cols-[1.45fr_0.78fr]">
                <main class="p-4">
                    <h3 class="text-[13px] font-black uppercase tracking-wide">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-1 font-semibold" style="color: {{ $accent }};">{{ $role }}</p>
                    <div class="mt-4 space-y-3">
                        @foreach (['About Me', 'Experience', 'Education', 'Highlights'] as $item)
                            <section>
                                <p class="{{ $section }} font-black uppercase tracking-[0.15em]">{{ $item }}</p>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                            </section>
                        @endforeach
                    </div>
                </main>
                <aside class="p-4" style="background: {{ $dark }}; color: #ffffff;">
                    <x-demo-profile-image class="mx-auto h-14 w-14 border-4 border-white/80" />
                    <p class="mt-4 {{ $section }} font-black uppercase tracking-[0.16em] opacity-80">Contact</p>
                    <p class="{{ $tiny }} mt-2 opacity-75">Email<br>Phone<br>{{ $location }}</p>
                    <p class="mt-4 {{ $section }} font-black uppercase tracking-[0.16em] opacity-80">Awards</p>
                    <div class="mt-2 space-y-2">
                        <div class="h-8 rounded-sm bg-white/15"></div>
                        <div class="h-8 rounded-sm bg-white/15"></div>
                    </div>
                </aside>
            </div>
            @break

        @case('technical')
            <div class="grid h-full grid-cols-[0.8fr_1.45fr]">
                <aside class="p-4" style="background: {{ $dark }}; color: #ffffff;">
                    <div class="flex h-14 items-center justify-center rounded-md bg-white/90 p-1">
                        <x-demo-profile-image class="h-11 w-11 border-2 border-white shadow-sm" />
                    </div>
                    <p class="mt-4 {{ $section }} font-black uppercase tracking-[0.16em] opacity-80">Tech Stack</p>
                    <div class="mt-2 grid grid-cols-2 gap-1.5">
                        @foreach (['PHP', 'SQL', 'API', 'Git', 'AI', 'PDF'] as $skill)
                            <span class="{{ $tiny }} rounded-sm bg-white/15 px-1 py-1 text-center">{{ $skill }}</span>
                        @endforeach
                    </div>
                    <p class="mt-4 {{ $section }} font-black uppercase tracking-[0.16em] opacity-80">Metrics</p>
                    <div class="mt-2 space-y-1.5">
                        <div class="h-7 rounded-sm bg-white/15"></div>
                        <div class="h-7 rounded-sm bg-white/15"></div>
                    </div>
                </aside>
                <main class="p-4">
                    <h3 class="text-[13px] font-black uppercase tracking-wide">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-1 font-semibold" style="color: {{ $accent }};">{{ $role }}</p>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        @foreach (['Profile', 'Experience', 'Projects', 'Education'] as $item)
                            <section>
                                <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                            </section>
                        @endforeach
                    </div>
                </main>
            </div>
            @break

        @case('top-band')
        @case('photo-band')
            @if ($slug === 'rose-designer')
                <div class="h-full">
                    <header class="grid h-24 grid-cols-[0.72fr_1.4fr]" style="background: {{ $soft }};">
                        <div class="flex items-center justify-center">
                            <x-demo-profile-image class="h-16 w-16 border-4 border-white" />
                        </div>
                        <div class="flex min-w-0 flex-col justify-center pr-5">
                            <h3 class="truncate text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                            <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                            <p class="{{ $tiny }} mt-1 text-neutral-500">portfolio@example.com</p>
                        </div>
                    </header>
                    <main class="grid h-[calc(100%-6rem)] grid-cols-[1fr_0.82fr] gap-5 p-5">
                        <section class="space-y-3">
                            @foreach (['About Me', 'Experience', 'Education'] as $item)
                                <div>
                                    <p class="{{ $section }} font-black uppercase tracking-[0.16em]">{{ $item }}</p>
                                    <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index] }}</p>
                                </div>
                            @endforeach
                        </section>
                        <aside class="space-y-2">
                            @foreach (['Risk', 'Finance', 'Reports', 'Excel'] as $skill)
                                <p class="{{ $tiny }} rounded-r-full border-l-4 px-2 py-1" style="border-color: {{ $accent }}; background: {{ $loop->odd ? $soft : '#f1f5f9' }};">{{ $skill }}</p>
                            @endforeach
                        </aside>
                    </main>
                </div>
            @else
                <div class="h-full">
                    <header class="flex h-24 items-center gap-4 border-b-4 px-5" style="background: {{ $soft }}; border-color: {{ $accent }};">
                        <x-demo-profile-image class="h-16 w-16 border-4 border-white" />
                        <div class="min-w-0">
                            <h3 class="truncate text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                            <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                            <p class="{{ $tiny }} mt-1 text-neutral-500">{{ $email }} &middot; {{ $location }}</p>
                        </div>
                    </header>
                    <div class="grid h-[calc(100%-6rem)] grid-cols-[1.15fr_0.85fr] gap-4 p-5">
                        <main class="space-y-3">
                            @foreach (['About Me', 'Experience', 'Education'] as $item)
                                <section>
                                    <p class="{{ $section }} font-black uppercase tracking-[0.16em]">{{ $item }}</p>
                                    <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index] }}</p>
                                </section>
                            @endforeach
                        </main>
                        <aside class="space-y-2">
                            <p class="{{ $section }} font-black uppercase tracking-[0.16em]">Skills</p>
                            @foreach (['Risk', 'Finance', 'Reports', 'Excel'] as $skill)
                                <p class="{{ $tiny }} rounded-sm px-2 py-1" style="background: {{ $loop->odd ? $soft : '#f1f5f9' }};">{{ $skill }}</p>
                            @endforeach
                        </aside>
                    </div>
                </div>
            @endif
            @break

        @case('feature')
            <div class="grid h-full grid-cols-[0.92fr_1.35fr]">
                <aside class="p-5" style="background: {{ $dark ?: '#6b5745' }}; color: #ffffff;">
                    <x-demo-profile-image class="mx-auto h-20 w-20 border-4 border-white/80" />
                    <p class="mt-5 {{ $section }} font-black uppercase tracking-[0.16em]">Education</p>
                    <p class="{{ $tiny }} mt-2 opacity-80">MBA, Finance<br>Boston University · 2018</p>
                    <p class="mt-5 {{ $section }} font-black uppercase tracking-[0.16em]">Skills</p>
                    <p class="{{ $tiny }} mt-2 opacity-80">Content · Reports · Analysis</p>
                </aside>
                <main class="p-5">
                    <h3 class="text-[18px] font-black uppercase leading-tight tracking-wide">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                    <div class="mt-5 space-y-4">
                        @foreach (['Profile', 'Experience', 'Contact'] as $item)
                            <section>
                                <p class="{{ $section }} font-black uppercase tracking-[0.16em]">{{ $item }}</p>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index] }}</p>
                            </section>
                        @endforeach
                    </div>
                </main>
            </div>
            @break

        @case('dark-header')
            <div class="h-full">
                <header class="flex h-24 items-center gap-4 px-5" style="background: {{ $dark }};">
                    <x-demo-profile-image class="h-16 w-16 border-4 border-white/80" />
                    <div class="min-w-0">
                        <h3 class="truncate text-[14px] font-black uppercase tracking-wide text-white">{{ $name }}</h3>
                        <p class="{{ $tiny }} mt-1 text-white/75">{{ $role }}</p>
                    </div>
                </header>
                <div class="grid grid-cols-[1.2fr_0.8fr] gap-4 p-5">
                    <main class="space-y-3">
                        @foreach (['About Me', 'Experience', 'Education'] as $item)
                            <section>
                                <p class="{{ $section }} font-black uppercase tracking-[0.16em]">{{ $item }}</p>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index] }}</p>
                            </section>
                        @endforeach
                    </main>
                    <aside class="space-y-2">
                        @foreach (['Skills', 'Language', 'Reference'] as $item)
                            <section class="rounded-sm bg-neutral-100 p-2">
                                <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                                <div class="mt-1.5 h-1.5 rounded-full bg-neutral-300"></div>
                            </section>
                        @endforeach
                    </aside>
                </div>
            </div>
            @break

        @case('timeline')
            <div class="h-full p-5">
                <header class="flex items-center gap-4">
                    <x-demo-profile-image class="h-14 w-14 border-4 border-white shadow-sm" />
                    <div class="min-w-0">
                        <h3 class="truncate text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                        <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                    </div>
                </header>
                <main class="relative mt-5 space-y-4 pl-7">
                    <div class="absolute bottom-0 left-2 top-0 w-px bg-neutral-300"></div>
                    @foreach (['Profile', 'Credit Risk Officer', 'Financial Analyst', 'Education'] as $item)
                        <section class="relative">
                            <span class="absolute -left-[1.55rem] top-0 h-3 w-3 rounded-full" style="background: {{ $accent }};"></span>
                            <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                            <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                        </section>
                    @endforeach
                </main>
            </div>
            @break

        @case('edge')
            <div class="h-full border-l-[10px] p-5" style="border-color: {{ $accent }};">
                <div class="absolute right-0 top-0 h-14 w-20" style="background: {{ $soft }};"></div>
                <header class="relative pr-14">
                    <h3 class="text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                </header>
                <main class="mt-5 grid grid-cols-[0.9fr_1.1fr] gap-4">
                    <aside class="space-y-3">
                        @foreach (['Contact', 'Skills', 'Language'] as $item)
                            <section>
                                <p class="{{ $section }} font-black uppercase tracking-[0.16em]">{{ $item }}</p>
                                <div class="mt-1.5 h-1.5 rounded-full bg-neutral-200"></div>
                                <div class="mt-1 h-1.5 w-8/12 rounded-full bg-neutral-200"></div>
                            </section>
                        @endforeach
                    </aside>
                    <section class="space-y-3">
                        @foreach (['Experience', 'Education', 'Projects'] as $item)
                            <div>
                                <p class="{{ $section }} font-black uppercase tracking-[0.16em]">{{ $item }}</p>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index] }}</p>
                            </div>
                        @endforeach
                    </section>
                </main>
            </div>
            @break

        @case('stripe')
            <div class="h-full p-5">
                <div class="absolute right-0 top-0 h-24 w-24 -translate-y-10 translate-x-10 rotate-45" style="background: {{ $accent }};"></div>
                <div class="absolute left-0 bottom-0 h-20 w-20 translate-y-10 -translate-x-10 rotate-45" style="background: {{ $soft }};"></div>
                <header class="relative">
                    <h3 class="text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                </header>
                <main class="relative mt-5 grid grid-cols-2 gap-4">
                    @foreach (['Summary', 'Experience', 'Skills', 'Education', 'Projects', 'Awards'] as $item)
                        <section class="{{ $loop->first ? 'col-span-2 border-b border-black/10 pb-2' : '' }}">
                            <p class="{{ $section }} font-black uppercase tracking-[0.16em]">{{ $item }}</p>
                            <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                        </section>
                    @endforeach
                </main>
            </div>
            @break

        @case('split')
            <div class="h-full">
                <header class="grid {{ $bandHeight }} grid-cols-[1.25fr_0.75fr]">
                    <div class="{{ $pagePadding }}">
                        <h3 class="{{ $nameSize }} font-black uppercase tracking-wide">{{ $name }}</h3>
                        <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                        @if ($preview)
                            <p class="{{ $tiny }} mt-2 text-neutral-500">{{ $email }} · {{ $location }}</p>
                        @endif
                    </div>
                    <div style="background: {{ $accent }};"></div>
                </header>
                <main class="grid grid-cols-[1.08fr_0.92fr] gap-6 {{ $pagePadding }}">
                    <section class="{{ $preview ? 'space-y-5' : 'space-y-3' }}">
                        @foreach ($splitSections as $item)
                            <div>
                                <p class="{{ $section }} font-black uppercase tracking-[0.16em]">{{ $item }}</p>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % count($demoLines)] }}</p>
                            </div>
                        @endforeach
                    </section>
                    <aside class="{{ $preview ? 'space-y-3 pt-2' : 'space-y-2' }}">
                        @foreach ($sideItems as $item)
                            <p class="{{ $tiny }} rounded-full px-3 py-1.5" style="background: {{ $soft }};">{{ $item }}</p>
                        @endforeach
                    </aside>
                </main>
            </div>
            @break

        @case('balanced')
            <div class="h-full p-5">
                <header class="flex items-start justify-between gap-4 border-b pb-3" style="border-color: {{ $accent }};">
                    <div>
                        <h3 class="text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                        <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                    </div>
                    <div class="h-9 w-9 rounded-full" style="background: {{ $soft }};"></div>
                </header>
                <main class="mt-4 grid grid-cols-2 gap-4">
                    @foreach (['Profile', 'Skills', 'Experience', 'Projects', 'Education', 'Contact'] as $item)
                        <section>
                            <p class="{{ $section }} font-black uppercase tracking-[0.16em]">{{ $item }}</p>
                            <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                        </section>
                    @endforeach
                </main>
            </div>
            @break

        @case('brief')
            <div class="h-full p-5" style="background: {{ $surface }};">
                <header class="border-l-4 pl-3" style="border-color: {{ $accent }};">
                    <h3 class="text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                </header>
                <main class="mt-5 space-y-3">
                    @foreach (['Impact Summary', 'Selected Wins', 'Experience', 'Education', 'Highlights'] as $item)
                        <section class="grid grid-cols-[0.72fr_1.2fr] gap-3 border-b border-black/10 pb-2">
                            <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                            <p class="{{ $tiny }} text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                        </section>
                    @endforeach
                </main>
            </div>
            @break

        @case('compact')
            <div class="h-full p-5">
                <header class="text-center">
                    <h3 class="text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-1 text-neutral-500">{{ $email }} · {{ $location }}</p>
                </header>
                <main class="mt-4 grid grid-cols-[0.72fr_1.28fr] gap-4">
                    <aside class="space-y-3">
                        @foreach (['Contact', 'Skills', 'Language'] as $item)
                            <section>
                                <p class="{{ $section }} font-black uppercase tracking-[0.15em]" style="color: {{ $accent }};">{{ $item }}</p>
                                <div class="mt-1.5 space-y-1">
                                    <div class="h-1.5 rounded-full bg-neutral-200"></div>
                                    <div class="h-1.5 w-9/12 rounded-full bg-neutral-200"></div>
                                </div>
                            </section>
                        @endforeach
                    </aside>
                    <section class="space-y-3">
                        @foreach (['About Me', 'Experience', 'Education', 'Highlights'] as $item)
                            <div>
                                <p class="{{ $section }} font-black uppercase tracking-[0.15em]">{{ $item }}</p>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                            </div>
                        @endforeach
                    </section>
                </main>
            </div>
            @break

        @case('dense')
            <div class="h-full p-5">
                <header class="border-b pb-2" style="border-color: {{ $accent }};">
                    <h3 class="text-[13px] font-black uppercase tracking-wide">{{ $name }}</h3>
                    <p class="{{ $tiny }} font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                </header>
                <main class="mt-3 columns-2 gap-5 [column-fill:_auto]">
                    @foreach (['Summary', 'Experience', 'Education', 'Skills', 'Projects', 'Certifications', 'Highlights', 'Awards'] as $item)
                        <section class="mb-3 break-inside-avoid">
                            <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                            <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                        </section>
                    @endforeach
                </main>
            </div>
            @break

        @case('minimal')
            <div class="h-full p-6">
                <header>
                    <h3 class="text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-1 text-neutral-500">{{ $role }} · {{ $location }}</p>
                </header>
                <main class="mt-6 space-y-4">
                    @foreach (['Summary', 'Experience', 'Education', 'Skills', 'Highlights'] as $item)
                        <section class="grid grid-cols-[0.55fr_1.45fr] gap-4">
                            <p class="{{ $section }} font-black uppercase tracking-[0.14em]" style="color: {{ $accent }};">{{ $item }}</p>
                            <p class="{{ $tiny }} text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                        </section>
                    @endforeach
                </main>
            </div>
            @break

        @case('editorial')
            <div class="h-full p-6">
                <header class="border-y border-neutral-300 py-4 text-center">
                    <h3 class="text-[16px] font-black uppercase tracking-[0.22em]">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-2 italic text-neutral-500">{{ $role }}</p>
                </header>
                <main class="mt-5 space-y-4">
                    @foreach (['Summary', 'Experience', 'Education', 'Skills'] as $item)
                        <section>
                            <p class="{{ $section }} font-black uppercase tracking-[0.2em]">{{ $item }}</p>
                            <p class="{{ $tiny }} mt-1 border-l border-neutral-300 pl-3 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                        </section>
                    @endforeach
                </main>
            </div>
            @break

        @case('academic')
            @if ($slug === 'graduate-focus')
                <div class="h-full p-5">
                    <header class="border-b-2 pb-3" style="border-color: {{ $accent }};">
                        <h3 class="text-[13px] font-black">{{ $name }}</h3>
                        <p class="{{ $tiny }} mt-1 text-neutral-500">Graduate Analyst &middot; {{ $email }}</p>
                    </header>
                    <main class="mt-4 space-y-3">
                        @foreach (['Education', 'Capstone', 'Internship', 'Campus Leadership', 'Highlights'] as $item)
                            <section class="grid grid-cols-[1.15fr_0.42fr] gap-3">
                                <div>
                                    <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                                    <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                                </div>
                                <p class="{{ $tiny }} text-right text-neutral-400">2020<br>2024</p>
                            </section>
                        @endforeach
                    </main>
                </div>
            @else
                <div class="h-full p-5">
                    <header class="text-center">
                        <h3 class="text-[13px] font-black">{{ $name }}</h3>
                        <p class="{{ $tiny }} mt-1 text-neutral-500">Research Assistant &middot; {{ $email }}</p>
                    </header>
                    <main class="mt-5 space-y-3">
                        @foreach (['Publications', 'Research', 'Teaching', 'Education', 'Highlights'] as $item)
                            <section>
                                <div class="flex items-center justify-between border-b border-neutral-300 pb-1">
                                    <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                                    <p class="{{ $tiny }} text-neutral-400">2020 - 2024</p>
                                </div>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                            </section>
                        @endforeach
                    </main>
                </div>
            @endif
            @break

        @default
            @if ($slug === 'operator-compact')
                <div class="h-full p-5">
                    <header class="grid grid-cols-[1.2fr_0.72fr] gap-4 border-b border-neutral-300 pb-3">
                        <div>
                            <h3 class="text-[13px] font-black uppercase tracking-wide">{{ $name }}</h3>
                            <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">Operations Lead</p>
                        </div>
                        <div class="space-y-1">
                            <div class="h-2 rounded-full bg-neutral-200"></div>
                            <div class="h-2 w-8/12 rounded-full bg-neutral-200"></div>
                        </div>
                    </header>
                    <main class="mt-4 grid grid-cols-[0.82fr_1.2fr] gap-4">
                        <aside class="space-y-2">
                            @foreach (['Process', 'Volume', 'Quality', 'SLA'] as $item)
                                <p class="{{ $tiny }} rounded-sm px-2 py-1" style="background: {{ $soft }};">{{ $item }}</p>
                            @endforeach
                        </aside>
                        <section class="space-y-3">
                            @foreach (['Summary', 'Operations', 'Reporting', 'Education'] as $item)
                                <div>
                                    <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                                    <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                                </div>
                            @endforeach
                        </section>
                    </main>
                </div>
            @else
                <div class="h-full {{ $pagePadding }}">
                    <header @class(['text-center' => in_array($header, ['centered', 'small-center'], true)])>
                        <h3 class="{{ $nameSize }} font-black uppercase tracking-wide">{{ $name }}</h3>
                        <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                        <p class="{{ $tiny }} mt-1 text-neutral-500">{{ $email }} &middot; {{ $location }}</p>
                    </header>
                    <main class="{{ $preview ? 'mt-8 space-y-5' : 'mt-5 space-y-3' }}">
                        @foreach ($fullSections as $item)
                            <section>
                                <p class="{{ $section }} font-black uppercase tracking-[0.16em]">{{ $item }}</p>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $sampleSections[$item] ?? $demoLines[$loop->index % 3] }}</p>
                            </section>
                        @endforeach
                    </main>
                </div>
            @endif
    @endswitch
</div>
