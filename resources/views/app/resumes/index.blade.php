@extends('layouts.app')

@section('title', 'My Resumes')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">My Resumes</p>
                <h1 class="mt-2 text-3xl font-black">Resume versions</h1>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('resumes.master') }}" class="rounded-lg border border-black/10 bg-white px-4 py-3 text-sm font-black">Master Resume</a>
                <a href="{{ route('resumes.create') }}" class="rounded-lg bg-neutral-950 px-4 py-3 text-sm font-black text-white">Create Resume</a>
            </div>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($resumes as $resume)
                <article class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-lg font-black">{{ $resume->title }}</p>
                            <p class="mt-1 text-sm text-neutral-500">{{ str($resume->type)->headline() }} · {{ $resume->template?->name ?? 'Default template' }}</p>
                        </div>
                        @if ($resume->is_master)
                            <span class="rounded-full bg-teal-50 px-3 py-1 text-xs font-bold text-teal-700">Master</span>
                        @endif
                    </div>
                    <p class="mt-4 min-h-12 text-sm leading-6 text-neutral-600">{{ $resume->professional_summary ? str($resume->professional_summary)->limit(130) : 'Add summary, experience, skills, and projects in the builder.' }}</p>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a href="{{ route('resumes.edit', $resume) }}" class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-bold text-white">Open</a>
                        <form method="POST" action="{{ route('resumes.duplicate', $resume) }}">
                            @csrf
                            <button class="rounded-lg border border-black/10 px-4 py-2 text-sm font-bold">Duplicate</button>
                        </form>
                        <a href="{{ route('resumes.export', $resume) }}" class="rounded-lg border border-black/10 px-4 py-2 text-sm font-bold">PDF</a>
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-black/15 bg-white p-8 text-sm text-neutral-500">No resumes yet. Create your master resume to start.</div>
            @endforelse
        </div>

        <div class="mt-6">{{ $resumes->links() }}</div>
    </div>
@endsection
