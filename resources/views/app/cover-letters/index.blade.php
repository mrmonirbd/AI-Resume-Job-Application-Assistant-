@extends('layouts.app')

@section('title', 'Cover Letters')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Cover Letters</p>
                <h1 class="mt-2 text-3xl font-black">Saved application letters</h1>
            </div>
            <a href="{{ route('jobs.index') }}" class="rounded-lg bg-neutral-950 px-4 py-3 text-sm font-black text-white">Choose a Job</a>
        </div>

        <div class="mt-8 grid gap-4 lg:grid-cols-2">
            @forelse ($coverLetters as $letter)
                <article class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-black">{{ $letter->title }}</h2>
                            <p class="mt-1 text-sm text-neutral-500">{{ $letter->job?->company ?? 'No job linked' }} · {{ str($letter->tone)->headline() }} · {{ str($letter->length)->headline() }}</p>
                        </div>
                        <a href="{{ route('cover-letters.export', $letter) }}" class="rounded-lg border border-black/10 px-3 py-2 text-xs font-bold">PDF</a>
                    </div>
                    <p class="mt-4 line-clamp-5 whitespace-pre-line text-sm leading-6 text-neutral-600">{{ $letter->content }}</p>
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-black/15 bg-white p-8 text-sm text-neutral-500">Generate a cover letter from a job detail page to save it here.</div>
            @endforelse
        </div>

        <div class="mt-6">{{ $coverLetters->links() }}</div>
    </div>
@endsection
