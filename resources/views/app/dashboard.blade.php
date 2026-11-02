@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-10 lg:py-10">
        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-brand-600">Your career workspace</p>
                <h1 class="mt-2 break-words text-3xl font-bold tracking-tight text-ink">Welcome back, {{ auth()->user()->name }}</h1>
                <p class="mt-2 text-sm leading-6 text-muted">Your next opportunity starts with a great first impression.</p>
            </div>
            <a href="{{ route('resumes.create') }}" class="inline-flex shrink-0 items-center justify-center gap-2 self-start rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-brand-600/20 transition hover:bg-brand-700 md:self-auto"><x-workspace-icon name="plus" />Create Resume</a>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['Active Applications', $stats['active_applications'], 'briefcase', 'bg-brand-50 text-brand-600', 'Opportunities in progress'],
                ['Interviews', $stats['interviews'], 'mail', 'bg-sun-soft text-brand-700', 'Conversations that count'],
                ['Resumes Created', $stats['resumes_created'], 'document', 'bg-coral-soft text-brand-700', 'Your professional story'],
                ['Average Match Score', $stats['average_match_score'].'%', 'check', 'bg-sea-soft text-brand-700', 'Across analyzed applications'],
            ] as [$label, $value, $icon, $color, $description])
                <div class="rounded-2xl border border-line/80 bg-white p-5 shadow-sm shadow-slate-200/30">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-medium text-muted">{{ $label }}</p>
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl {{ $color }}"><x-workspace-icon :name="$icon" /></span>
                    </div>
                    <p class="mt-3 text-3xl font-bold tracking-tight tabular-nums">{{ $value }}</p>
                    <p class="mt-2 text-xs text-slate-400">{{ $description }}</p>
                </div>
            @endforeach
        </div>

        <section class="relative mt-7 overflow-hidden rounded-2xl border border-brand-800 bg-linear-to-br from-ink via-brand-900 to-brand-700 p-6 sm:p-7">
            <div class="pointer-events-none absolute -right-12 -top-20 size-64 rounded-full border-[35px] border-white/5" aria-hidden="true"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-start gap-4">
                    <span class="hidden size-12 shrink-0 place-items-center rounded-2xl bg-white text-brand-600 shadow-sm sm:grid"><x-workspace-icon name="sparkles" class="size-6" /></span>
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight text-white">Make your next application stand out</h2>
                        <p class="mt-1 max-w-xl text-sm leading-6 text-brand-100/80">Tailor your resume to the role and check its ATS compatibility before you apply.</p>
                    </div>
                </div>
                <div class="flex shrink-0 flex-wrap gap-3">
                    <a href="{{ route('jobs.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-linear-to-r from-apricot to-coral px-4 py-2.5 text-sm font-semibold text-ink shadow-sm transition hover:brightness-105">Tailor Resume for a Job<x-workspace-icon name="arrow" class="size-4" /></a>
                    <a href="{{ route('ats.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">Check Resume</a>
                </div>
            </div>
        </section>

        <div class="mt-7 grid items-start gap-6 xl:grid-cols-[1.1fr_1fr]">
            <section class="overflow-hidden rounded-2xl border border-line/80 bg-white shadow-sm shadow-slate-200/30">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                    <div><h2 class="font-semibold tracking-tight">Recent resumes</h2><p class="mt-1 text-xs text-slate-400">Pick up where you left off</p></div>
                    <a href="{{ route('resumes.index') }}" class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-800">View all<x-workspace-icon name="arrow" class="size-4" /></a>
                </div>
                <div class="divide-y divide-slate-100 px-3">
                    @forelse ($resumes as $resume)
                        <a href="{{ route('resumes.edit', $resume) }}" class="group flex items-center gap-3 rounded-xl px-3 py-4 transition hover:bg-slate-50 sm:gap-4">
                            <span class="grid size-11 shrink-0 place-items-center rounded-xl border border-brand-100 bg-brand-50/60 text-brand-500"><x-workspace-icon name="document" /></span>
                            <span class="min-w-0 flex-1">
                                <span class="block break-words text-sm font-semibold leading-5 text-body group-hover:text-brand-600">{{ $resume->title }}</span>
                                <span class="mt-1 block text-xs text-slate-400">{{ str($resume->type)->headline() }} <span class="px-1">·</span> {{ $resume->updated_at->diffForHumans() }}</span>
                                <span class="mt-2 inline-block rounded-md bg-slate-100 px-2 py-1 text-[10px] font-medium text-muted">{{ $resume->template?->name ?? 'Default' }}</span>
                            </span>
                            <x-workspace-icon name="arrow" class="size-4 shrink-0 text-slate-300 transition group-hover:text-brand-600" />
                        </a>
                    @empty
                        <div class="px-4 py-12 text-center">
                            <x-workspace-icon name="document" class="mx-auto size-9 text-brand-400" />
                            <h3 class="mt-4 text-sm font-semibold">Your story starts here</h3>
                            <p class="mt-2 text-sm text-muted">Create your master resume to begin tailoring applications.</p>
                            <a href="{{ route('resumes.create') }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-brand-600"><x-workspace-icon name="plus" class="size-4" />Create your first resume</a>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="overflow-hidden rounded-2xl border border-line/80 bg-white shadow-sm shadow-slate-200/30">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                    <div><h2 class="font-semibold tracking-tight">Recent job applications</h2><p class="mt-1 text-xs text-slate-400">Keep your next move in sight</p></div>
                    <a href="{{ route('jobs.create') }}" class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-800"><x-workspace-icon name="plus" class="size-4" />Add job</a>
                </div>
                <div class="divide-y divide-slate-100 px-3">
                    @forelse ($applications as $application)
                        <a href="{{ route('jobs.show', $application->job) }}" class="flex flex-wrap items-center gap-3 rounded-xl px-3 py-4 transition hover:bg-slate-50">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-muted"><x-workspace-icon name="briefcase" /></span>
                            <span class="min-w-0 flex-1">
                                <span class="block break-words text-sm font-semibold text-body">{{ $application->job->title }}</span>
                                <span class="mt-1 block text-xs text-slate-400">{{ $application->job->company }} · {{ $application->job->location ?: 'Location open' }}</span>
                            </span>
                            <span class="rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700">{{ str($application->status)->headline() }}</span>
                        </a>
                    @empty
                        <div class="flex flex-col items-center px-6 py-14 text-center">
                            <span class="grid size-16 place-items-center rounded-2xl border border-slate-100 bg-slate-50 text-brand-400"><x-workspace-icon name="briefcase" class="size-7" /></span>
                            <h3 class="mt-5 font-semibold text-body">A new opportunity is waiting</h3>
                            <p class="mt-2 max-w-xs text-sm leading-6 text-slate-400">Paste a job description to start match analysis.</p>
                            <a href="{{ route('jobs.create') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl border border-line bg-white px-4 py-2.5 text-sm font-semibold text-body transition hover:border-brand-200 hover:text-brand-600"><x-workspace-icon name="plus" class="size-4" />Add your first job</a>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>
        <p class="mt-8 text-center text-xs text-slate-400">Build with confidence. Apply with purpose.</p>
    </div>
@endsection
