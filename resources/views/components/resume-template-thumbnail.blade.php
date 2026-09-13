@props(['template', 'compact' => false, 'heightClass' => null])

@php
    $settings = $template->settings ?? [];
    $style = data_get($settings, 'style', 'classic');
    $header = data_get($settings, 'header', 'compact');
    $accent = $template->accent_color ?: '#2563eb';
    $soft = data_get($settings, 'soft', '#eef2ff');
    $dark = data_get($settings, 'dark', '#111827');
    $surface = data_get($settings, 'surface', '#ffffff');
    $heightClass = $heightClass ?? ($compact ? 'h-64' : 'h-80');
    $tiny = $compact ? 'text-[6px] leading-[1.3]' : 'text-[7px] leading-[1.45]';
    $section = $compact ? 'text-[6px]' : 'text-[7px]';
    $name = match ($style) {
        'editorial', 'academic' => 'Zara Rahman',
        'timeline', 'technical' => 'Arif Hasan',
        'photo-band', 'top-band', 'feature', 'dark-header' => 'Nadia Karim',
        default => 'Nusrat Jahan',
    };
    $role = match ($template->category) {
        'Executive' => 'Finance Manager',
        'Creative' => 'Brand Designer',
        'Technical' => 'Backend Developer',
        'Academic', 'Graduate' => 'Research Assistant',
        default => 'Credit Analyst',
    };
    $demoLines = [
        'Reviews financial statements and portfolio risk.',
        'Prepares verified reports for business decisions.',
        'Collects client documents through site visits.',
    ];
@endphp

<div
    data-template-style="{{ $style }}"
    data-template-layout="{{ $template->layout }}"
    {{ $attributes->merge(['class' => "relative overflow-hidden rounded-lg border border-black/10 bg-white shadow-sm {$heightClass}"]) }}
    style="font-family: {{ $template->font_family }}, Arial, sans-serif; background: {{ $surface }};"
>
    @switch($style)
        @case('sidebar')
            <div class="grid h-full grid-cols-[0.82fr_1.42fr]">
                <aside class="p-4" style="background: {{ $dark }}; color: #ffffff;">
                    @if (data_get($settings, 'photo'))
                        <x-demo-profile-image class="h-14 w-14 border-4 border-white/80" />
                    @else
                        <div class="h-8 w-16 rounded-sm bg-white/90"></div>
                    @endif
                    <p class="mt-4 {{ $section }} font-black uppercase tracking-[0.16em] opacity-80">Contact</p>
                    <p class="{{ $tiny }} mt-2 opacity-75">demo@example.com<br>Dhaka, BD<br>+880 1700 000</p>
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
                        @foreach (['Senior Analyst', 'Credit Officer', 'Report Associate'] as $job)
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
            @break

        @case('soft-sidebar')
            <div class="grid h-full grid-cols-[0.88fr_1.36fr]">
                <aside class="p-4 text-center" style="background: {{ $soft }};">
                    <x-demo-profile-image class="mx-auto h-16 w-16 border-4 border-white" />
                    <p class="mt-3 {{ $section }} font-black uppercase tracking-[0.16em]" style="color: {{ $accent }};">Profile</p>
                    <p class="{{ $tiny }} mt-2 text-neutral-700">Organized analyst with financial reporting experience.</p>
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
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index % 3] }}</p>
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
                        @foreach (['About Me', 'Experience', 'Education', 'References'] as $item)
                            <section>
                                <p class="{{ $section }} font-black uppercase tracking-[0.15em]">{{ $item }}</p>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index % 3] }}</p>
                            </section>
                        @endforeach
                    </div>
                </main>
                <aside class="p-4" style="background: {{ $dark }}; color: #ffffff;">
                    <x-demo-profile-image class="mx-auto h-14 w-14 border-4 border-white/80" />
                    <p class="mt-4 {{ $section }} font-black uppercase tracking-[0.16em] opacity-80">Contact</p>
                    <p class="{{ $tiny }} mt-2 opacity-75">Email<br>Phone<br>Dhaka</p>
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
                    <div class="rounded-md bg-white/90 p-2">
                        <div class="h-7 rounded-sm" style="background: {{ $soft }};"></div>
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
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index % 3] }}</p>
                            </section>
                        @endforeach
                    </div>
                </main>
            </div>
            @break

        @case('top-band')
        @case('photo-band')
            <div class="h-full">
                <header class="flex h-24 items-center gap-4 px-5" style="background: {{ $soft }};">
                    <x-demo-profile-image class="h-16 w-16 border-4 border-white" />
                    <div class="min-w-0">
                        <h3 class="truncate text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                        <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                        <p class="{{ $tiny }} mt-1 text-neutral-500">demo@example.com · Dhaka</p>
                    </div>
                </header>
                <div class="grid h-[calc(100%-6rem)] grid-cols-[1.15fr_0.85fr] gap-4 p-5">
                    <main class="space-y-3">
                        @foreach (['About Me', 'Experience', 'Education'] as $item)
                            <section>
                                <p class="{{ $section }} font-black uppercase tracking-[0.16em]">{{ $item }}</p>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index] }}</p>
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
            @break

        @case('feature')
            <div class="grid h-full grid-cols-[0.92fr_1.35fr]">
                <aside class="p-5" style="background: {{ $dark ?: '#6b5745' }}; color: #ffffff;">
                    <x-demo-profile-image class="mx-auto h-20 w-20 border-4 border-white/80" />
                    <p class="mt-5 {{ $section }} font-black uppercase tracking-[0.16em]">Education</p>
                    <p class="{{ $tiny }} mt-2 opacity-80">BBA Finance<br>2016 - 2020</p>
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
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index] }}</p>
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
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index] }}</p>
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
                            <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index % 3] }}</p>
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
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index] }}</p>
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
                            <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index % 3] }}</p>
                        </section>
                    @endforeach
                </main>
            </div>
            @break

        @case('split')
            <div class="h-full">
                <header class="grid h-24 grid-cols-[1.25fr_0.75fr]">
                    <div class="p-5">
                        <h3 class="text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                        <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                    </div>
                    <div style="background: {{ $accent }};"></div>
                </header>
                <main class="grid grid-cols-[1.1fr_0.9fr] gap-5 p-5">
                    <section class="space-y-3">
                        @foreach (['Summary', 'Experience', 'Education'] as $item)
                            <div>
                                <p class="{{ $section }} font-black uppercase tracking-[0.16em]">{{ $item }}</p>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index] }}</p>
                            </div>
                        @endforeach
                    </section>
                    <aside class="space-y-2">
                        @foreach (['Skills', 'Tools', 'Languages', 'Links'] as $item)
                            <p class="{{ $tiny }} rounded-full px-2 py-1" style="background: {{ $soft }};">{{ $item }}</p>
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
                            <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index % 3] }}</p>
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
                    @foreach (['Impact Summary', 'Selected Wins', 'Experience', 'Education', 'References'] as $item)
                        <section class="grid grid-cols-[0.72fr_1.2fr] gap-3 border-b border-black/10 pb-2">
                            <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                            <p class="{{ $tiny }} text-neutral-600">{{ $demoLines[$loop->index % 3] }}</p>
                        </section>
                    @endforeach
                </main>
            </div>
            @break

        @case('compact')
            <div class="h-full p-5">
                <header class="text-center">
                    <h3 class="text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-1 text-neutral-500">demo@example.com · Dhaka</p>
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
                        @foreach (['About Me', 'Experience', 'Education', 'References'] as $item)
                            <div>
                                <p class="{{ $section }} font-black uppercase tracking-[0.15em]">{{ $item }}</p>
                                <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index % 3] }}</p>
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
                    @foreach (['Summary', 'Experience', 'Education', 'Skills', 'Projects', 'Certifications', 'References', 'Awards'] as $item)
                        <section class="mb-3 break-inside-avoid">
                            <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                            <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index % 3] }}</p>
                        </section>
                    @endforeach
                </main>
            </div>
            @break

        @case('minimal')
            <div class="h-full p-6">
                <header>
                    <h3 class="text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-1 text-neutral-500">Credit Analyst · Dhaka</p>
                </header>
                <main class="mt-6 space-y-4">
                    @foreach (['Summary', 'Experience', 'Education', 'Skills', 'References'] as $item)
                        <section class="grid grid-cols-[0.55fr_1.45fr] gap-4">
                            <p class="{{ $section }} font-black uppercase tracking-[0.14em]" style="color: {{ $accent }};">{{ $item }}</p>
                            <p class="{{ $tiny }} text-neutral-600">{{ $demoLines[$loop->index % 3] }}</p>
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
                            <p class="{{ $tiny }} mt-1 border-l border-neutral-300 pl-3 text-neutral-600">{{ $demoLines[$loop->index % 3] }}</p>
                        </section>
                    @endforeach
                </main>
            </div>
            @break

        @case('academic')
            <div class="h-full p-5">
                <header>
                    <h3 class="text-[13px] font-black">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-1 text-neutral-500">Research Assistant · demo@example.com</p>
                </header>
                <main class="mt-4 space-y-3">
                    @foreach (['Education', 'Research', 'Publications', 'Teaching', 'References'] as $item)
                        <section>
                            <div class="flex items-center justify-between border-b border-neutral-300 pb-1">
                                <p class="{{ $section }} font-black uppercase tracking-[0.14em]">{{ $item }}</p>
                                <p class="{{ $tiny }} text-neutral-400">2020 - 2024</p>
                            </div>
                            <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index % 3] }}</p>
                        </section>
                    @endforeach
                </main>
            </div>
            @break

        @default
            <div class="h-full p-5">
                <header @class(['text-center' => in_array($header, ['centered', 'small-center'], true)])>
                    <h3 class="text-[14px] font-black uppercase tracking-wide">{{ $name }}</h3>
                    <p class="{{ $tiny }} mt-1 font-bold" style="color: {{ $accent }};">{{ $role }}</p>
                    <p class="{{ $tiny }} mt-1 text-neutral-500">demo@example.com · Dhaka</p>
                </header>
                <main class="mt-5 space-y-3">
                    @foreach (['Summary', 'Experience', 'Education', 'Skills', 'References'] as $item)
                        <section>
                            <p class="{{ $section }} font-black uppercase tracking-[0.16em]">{{ $item }}</p>
                            <p class="{{ $tiny }} mt-1 text-neutral-600">{{ $demoLines[$loop->index % 3] }}</p>
                        </section>
                    @endforeach
                </main>
            </div>
    @endswitch
</div>
