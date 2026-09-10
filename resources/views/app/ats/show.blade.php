@extends('layouts.app')

@section('title', 'ATS Checker')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">ATS Resume Checker</p>
            <h1 class="mt-2 text-3xl font-black">Check resume readiness</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-neutral-600">Review structure, readability, keyword coverage, and parsing risks. The score is not an official ATS score.</p>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <form method="POST" action="{{ route('ats.store') }}" class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                @csrf
                <label class="block">
                    <span class="text-sm font-semibold">Resume</span>
                    <select name="resume_id" required class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                        @foreach ($resumes as $resume)
                            <option value="{{ $resume->id }}" @selected(($selectedResume->id ?? null) === $resume->id)>{{ $resume->title }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="mt-5 block">
                    <span class="text-sm font-semibold">Target job description</span>
                    <textarea name="job_description" rows="12" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">{{ old('job_description') }}</textarea>
                </label>
                <button class="mt-5 rounded-lg bg-neutral-950 px-5 py-3 text-sm font-black text-white">Analyze Resume</button>
            </form>

            <section class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                @if ($result)
                    <div class="flex items-center gap-5">
                        <x-progress-ring :value="$result['readiness_score'] ?? 0" label="Resume Readiness" />
                        <div>
                            <h2 class="text-xl font-black">Resume Readiness</h2>
                            <p class="mt-2 text-sm text-neutral-500">{{ $result['disclaimer'] ?? 'This readiness score is not an actual proprietary ATS score.' }}</p>
                        </div>
                    </div>
                    <div class="mt-6 space-y-3">
                        @foreach (($result['categories'] ?? []) as $category)
                            @php($score = (int) ($category['score'] ?? 0))
                            <div>
                                <div class="flex justify-between text-sm font-bold"><span>{{ $category['label'] ?? 'Category' }}</span><span>{{ $score }}/100</span></div>
                                <div class="mt-2 h-2 rounded-full bg-neutral-100"><div class="h-2 rounded-full bg-teal-600" style="width: {{ $score }}%"></div></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6 rounded-lg border border-black/10 p-4">
                        <p class="text-sm font-black">Recommendations</p>
                        <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-neutral-600">
                            @foreach (($result['recommendations'] ?? []) as $recommendation)
                                <li>{{ $recommendation }}</li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="rounded-lg border border-dashed border-black/15 p-8 text-sm leading-6 text-neutral-500">Select a resume and optionally paste a target job description to see content, structure, keyword, readability, and formatting feedback.</div>
                @endif
            </section>
        </div>
    </div>
@endsection
