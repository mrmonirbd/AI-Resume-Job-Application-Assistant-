@extends('layouts.app')

@section('title', 'AI Tools')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">AI Tools</p>
            <h1 class="mt-2 text-3xl font-black">Career utility workspace</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-neutral-600">Tools share the same AI service layer and factuality rules, so new providers can be added without rewriting controllers.</p>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            @php
                $currentTool = old('tool', $selectedTool ?? null);
                $currentResumeId = old('resume_id', $selectedResumeId ?? null);
                $currentJobId = old('job_id', $selectedJobId ?? null);
                $currentText = old('text', $inputText ?? '');
            @endphp

            <form method="POST" action="{{ route('ai-tools.run') }}" class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                @csrf
                @if ($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4">
                        <p class="text-sm font-black text-red-800">Tool could not run</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <label class="block">
                    <span class="text-sm font-semibold">Tool</span>
                    <select name="tool" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                        @foreach ($tools as $tool)
                            <option value="{{ $tool->slug }}" @selected($currentTool === $tool->slug)>{{ $tool->name }} · {{ str($tool->category)->headline() }}</option>
                        @endforeach
                    </select>
                    <x-field-error name="tool" />
                </label>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-semibold">Resume context</span>
                        <select name="resume_id" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                            <option value="">Optional</option>
                            @foreach ($resumes as $resume)
                                <option value="{{ $resume->id }}" @selected((string) $currentResumeId === (string) $resume->id)>{{ $resume->title }}</option>
                            @endforeach
                        </select>
                        <x-field-error name="resume_id" />
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold">Job context</span>
                        <select name="job_id" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                            <option value="">Optional</option>
                            @foreach ($jobs as $job)
                                <option value="{{ $job->id }}" @selected((string) $currentJobId === (string) $job->id)>{{ $job->company }} · {{ $job->title }}</option>
                            @endforeach
                        </select>
                        <x-field-error name="job_id" />
                    </label>
                </div>
                <label class="mt-5 block">
                    <span class="text-sm font-semibold">Input text</span>
                    <textarea name="text" rows="12" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">{{ $currentText }}</textarea>
                    <x-field-error name="text" />
                </label>
                <button class="mt-5 rounded-lg bg-neutral-950 px-5 py-3 text-sm font-black text-white">Run Tool</button>
            </form>

            <section class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-black">Output</h2>
                @if ($result)
                    <div class="mt-5 rounded-lg border border-black/10 bg-[#f6f7f9] p-5">
                        @foreach ($result as $key => $value)
                            <div class="border-b border-black/10 py-3 last:border-b-0">
                                <p class="text-xs font-bold uppercase tracking-[0.14em] text-neutral-500">{{ str($key)->headline() }}</p>
                                @if (is_array($value))
                                    <p class="mt-2 text-sm leading-6 text-neutral-700">{{ collect($value)->flatten()->join(', ') }}</p>
                                @else
                                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-neutral-700">{{ $value }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="mt-5 rounded-lg border border-dashed border-black/15 p-8 text-sm leading-6 text-neutral-500">Select a tool and run it to preview structured AI output before applying anything to your resume.</div>
                @endif
            </section>
        </div>

        <section class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($tools as $tool)
                <div class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                    <p class="text-sm font-black">{{ $tool->name }}</p>
                    <p class="mt-2 text-sm leading-6 text-neutral-600">{{ $tool->description }}</p>
                </div>
            @endforeach
        </section>
    </div>
@endsection
