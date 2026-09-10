@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Welcome back</p>
                <h1 class="mt-2 text-3xl font-black">{{ auth()->user()->name }}</h1>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('resumes.create') }}" class="rounded-lg bg-neutral-950 px-4 py-3 text-sm font-black text-white">Create Resume</a>
                <a href="{{ route('jobs.create') }}" class="rounded-lg border border-black/10 bg-white px-4 py-3 text-sm font-black">Tailor Resume for a Job</a>
                <a href="{{ route('ats.create') }}" class="rounded-lg border border-black/10 bg-white px-4 py-3 text-sm font-black">Check Resume</a>
            </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['Active Applications', $stats['active_applications']],
                ['Interviews', $stats['interviews']],
                ['Resumes Created', $stats['resumes_created']],
                ['Average Match Score', $stats['average_match_score'].'%'],
            ] as [$label, $value])
                <div class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-neutral-500">{{ $label }}</p>
                    <p class="mt-3 text-3xl font-black">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-8 grid gap-6 xl:grid-cols-[1fr_1.05fr]">
            <section class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-black">Recent resumes</h2>
                    <a href="{{ route('resumes.index') }}" class="text-sm font-bold text-teal-700">View all</a>
                </div>
                <div class="mt-5 space-y-3">
                    @forelse ($resumes as $resume)
                        <a href="{{ route('resumes.edit', $resume) }}" class="flex items-center justify-between rounded-lg border border-black/10 p-4 hover:bg-[#f6f7f9]">
                            <span>
                                <span class="block text-sm font-bold">{{ $resume->title }}</span>
                                <span class="text-xs text-neutral-500">{{ str($resume->type)->headline() }} · {{ $resume->updated_at->diffForHumans() }}</span>
                            </span>
                            <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-bold">{{ $resume->template?->name ?? 'Default' }}</span>
                        </a>
                    @empty
                        <div class="rounded-lg border border-dashed border-black/15 p-6 text-sm text-neutral-500">Create your master resume to begin tailoring applications.</div>
                    @endforelse
                </div>
            </section>

            <section class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-black">Recent job applications</h2>
                    <a href="{{ route('jobs.create') }}" class="text-sm font-bold text-teal-700">Add job</a>
                </div>
                <div class="mt-5 space-y-3">
                    @forelse ($applications as $application)
                        <a href="{{ route('jobs.show', $application->job) }}" class="grid gap-2 rounded-lg border border-black/10 p-4 hover:bg-[#f6f7f9] sm:grid-cols-[1fr_auto]">
                            <span>
                                <span class="block text-sm font-bold">{{ $application->job->title }}</span>
                                <span class="text-xs text-neutral-500">{{ $application->job->company }} · {{ $application->job->location ?: 'Location open' }}</span>
                            </span>
                            <span class="self-start rounded-full bg-neutral-950 px-3 py-1 text-xs font-bold text-white">{{ str($application->status)->headline() }}</span>
                        </a>
                    @empty
                        <div class="rounded-lg border border-dashed border-black/15 p-6 text-sm text-neutral-500">Paste a job description to start match analysis.</div>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
@endsection
