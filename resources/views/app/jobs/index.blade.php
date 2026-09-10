@extends('layouts.app')

@section('title', 'Jobs')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Job Applications</p>
                <h1 class="mt-2 text-3xl font-black">Saved jobs and analyses</h1>
            </div>
            <a href="{{ route('jobs.create') }}" class="rounded-lg bg-neutral-950 px-4 py-3 text-sm font-black text-white">Add Job Application</a>
        </div>

        <div class="mt-8 grid gap-4 lg:grid-cols-2">
            @forelse ($jobs as $job)
                <article class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-black">{{ $job->title }}</h2>
                            <p class="mt-1 text-sm text-neutral-500">{{ $job->company }} · {{ $job->location ?: 'Location open' }}</p>
                        </div>
                        <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-bold">{{ str($job->stage)->headline() }}</span>
                    </div>
                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-lg border border-black/10 p-3">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-neutral-500">Analysis</p>
                            <p class="mt-2 text-sm font-bold">{{ $job->analysis ? 'Complete' : 'Pending' }}</p>
                        </div>
                        <div class="rounded-lg border border-black/10 p-3">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-neutral-500">Best Match</p>
                            <p class="mt-2 text-sm font-bold">{{ $job->matches->max('overall_score') ?: 0 }}%</p>
                        </div>
                        <div class="rounded-lg border border-black/10 p-3">
                            <p class="text-xs font-bold uppercase tracking-[0.12em] text-neutral-500">Tracker</p>
                            <p class="mt-2 text-sm font-bold">{{ $job->applications->count() }} card</p>
                        </div>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="{{ route('jobs.show', $job) }}" class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-bold text-white">Open</a>
                        <form method="POST" action="{{ route('jobs.analyze', $job) }}">
                            @csrf
                            <button class="rounded-lg border border-black/10 px-4 py-2 text-sm font-bold">Analyze</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-black/15 bg-white p-8 text-sm text-neutral-500">No jobs yet. Paste a job description to start matching.</div>
            @endforelse
        </div>

        <div class="mt-6">{{ $jobs->links() }}</div>
    </div>
@endsection
