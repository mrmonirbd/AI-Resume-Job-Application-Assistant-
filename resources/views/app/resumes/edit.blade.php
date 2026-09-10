@extends('layouts.app')

@section('title', $resume->title)

@section('content')
    <div class="px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-5 flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">{{ $resume->is_master ? 'Master Resume' : str($resume->type)->headline() }}</p>
                <h1 class="mt-2 text-3xl font-black">{{ $resume->title }}</h1>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('resumes.export', $resume) }}" class="rounded-lg bg-neutral-950 px-4 py-3 text-sm font-black text-white">Export PDF</a>
                <form method="POST" action="{{ route('resumes.duplicate', $resume) }}">
                    @csrf
                    <button class="rounded-lg border border-black/10 bg-white px-4 py-3 text-sm font-black">Duplicate</button>
                </form>
                @unless ($resume->is_master)
                    <form method="POST" action="{{ route('resumes.destroy', $resume) }}" onsubmit="return confirm('Archive this resume version?')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-lg border border-red-200 bg-white px-4 py-3 text-sm font-black text-red-700">Archive</button>
                    </form>
                @endunless
            </div>
        </div>

        <livewire:resume-builder :resume="$resume" />
    </div>
@endsection
