@extends('layouts.app')

@section('title', $job->title)

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
            <div class="space-y-6">
                <section class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">{{ $job->company }}</p>
                            <h1 class="mt-2 text-3xl font-black">{{ $job->title }}</h1>
                            <p class="mt-2 text-sm text-neutral-500">{{ $job->location ?: 'Location open' }}{{ $job->salary ? ' · '.$job->salary : '' }}</p>
                        </div>
                        <span class="rounded-full bg-neutral-950 px-4 py-2 text-sm font-bold text-white">{{ str($job->stage)->headline() }}</span>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('jobs.analyze', $job) }}">
                            @csrf
                            <button class="rounded-lg border border-black/10 px-4 py-2 text-sm font-bold">Analyze This Job</button>
                        </form>
                        <form method="POST" action="{{ route('jobs.match', $job) }}">
                            @csrf
                            <select name="resume_id" class="rounded-lg border border-black/10 px-3 py-2 text-sm">
                                @foreach ($resumes as $resume)
                                    <option value="{{ $resume->id }}" @selected($masterResume?->id === $resume->id)>Match: {{ $resume->title }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-lg bg-neutral-950 px-4 py-2 text-sm font-bold text-white">Compare Match</button>
                        </form>
                    </div>
                </section>

                <section class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-black">Job Description Analyzer</h2>
                    @if ($job->analysis)
                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            @foreach ([
                                'Required Skills' => $job->analysis->required_skills,
                                'Preferred Skills' => $job->analysis->preferred_skills,
                                'Technologies' => $job->analysis->technologies,
                                'Important Keywords' => $job->analysis->keywords,
                                'Soft Skills' => $job->analysis->soft_skills,
                                'Responsibilities' => $job->analysis->responsibilities,
                            ] as $label => $items)
                                <div class="rounded-lg border border-black/10 p-4">
                                    <p class="text-sm font-black">{{ $label }}</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @forelse (($items ?? []) as $item)
                                            <span class="rounded-full bg-neutral-100 px-3 py-1 text-xs font-bold text-neutral-700">{{ $item }}</span>
                                        @empty
                                            <span class="text-sm text-neutral-500">Not specified</span>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-4 text-sm text-neutral-500">No analysis yet.</p>
                    @endif
                </section>

                <section class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-xl font-black">Resume vs Job Match</h2>
                        <p class="text-xs font-semibold text-neutral-500">Internal compatibility estimate only</p>
                    </div>
                    @if ($job->matches->isNotEmpty())
                        @php($match = $job->matches->sortByDesc('created_at')->first())
                        <div class="mt-6 grid gap-6 lg:grid-cols-[160px_1fr]">
                            <div class="flex justify-center">
                                <x-progress-ring :value="$match->overall_score" label="Overall Match Score" />
                            </div>
                            <div class="space-y-3">
                                @foreach ([
                                    'Skills Match' => $match->skills_score,
                                    'Experience Alignment' => $match->experience_score,
                                    'Education Alignment' => $match->education_score,
                                    'Keyword Coverage' => $match->keyword_score,
                                ] as $label => $score)
                                    <div>
                                        <div class="flex justify-between text-sm font-bold"><span>{{ $label }}</span><span>{{ $score }}%</span></div>
                                        <div class="mt-2 h-2 rounded-full bg-neutral-100"><div class="h-2 rounded-full bg-teal-600" style="width: {{ $score }}%"></div></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-sm font-black text-emerald-900">Matched Skills</p>
                                <p class="mt-2 text-sm leading-6 text-emerald-800">{{ collect($match->matched_skills)->join(', ') ?: 'None found yet' }}</p>
                            </div>
                            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                                <p class="text-sm font-black text-amber-900">Missing / Review</p>
                                <p class="mt-2 text-sm leading-6 text-amber-800">{{ collect($match->missing_skills)->join(', ') ?: 'No major gaps found' }}</p>
                            </div>
                        </div>
                        <div class="mt-6 rounded-lg border border-black/10 p-4">
                            <p class="text-sm font-black">Recommendations</p>
                            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-6 text-neutral-600">
                                @foreach ($match->recommendations ?? [] as $recommendation)
                                    <li>{{ $recommendation }}</li>
                                @endforeach
                            </ul>
                            <p class="mt-4 text-xs font-semibold text-neutral-500">{{ $match->disclaimer }}</p>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-neutral-500">Run a comparison against your master resume or any saved version.</p>
                    @endif
                </section>

                <section class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-black">Application documents</h2>
                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        <form method="POST" action="{{ route('jobs.tailor', $job) }}" class="rounded-lg border border-black/10 p-4">
                            @csrf
                            <p class="text-sm font-black">Tailored resume</p>
                            <select name="resume_id" class="mt-3 w-full rounded-lg border border-black/10 px-3 py-2 text-sm">
                                @foreach ($resumes as $resume)
                                    <option value="{{ $resume->id }}" @selected($masterResume?->id === $resume->id)>{{ $resume->title }}</option>
                                @endforeach
                            </select>
                            <button class="mt-3 w-full rounded-lg bg-neutral-950 px-4 py-3 text-sm font-black text-white">✨ Tailor Resume for This Job</button>
                        </form>
                        <form method="POST" action="{{ route('jobs.cover-letter', $job) }}" class="rounded-lg border border-black/10 p-4">
                            @csrf
                            <p class="text-sm font-black">Cover letter</p>
                            <div class="mt-3 grid gap-3">
                                <select name="resume_id" class="rounded-lg border border-black/10 px-3 py-2 text-sm">
                                    @foreach ($resumes as $resume)
                                        <option value="{{ $resume->id }}" @selected($masterResume?->id === $resume->id)>{{ $resume->title }}</option>
                                    @endforeach
                                </select>
                                <select name="tone" class="rounded-lg border border-black/10 px-3 py-2 text-sm">
                                    @foreach (['professional', 'confident', 'friendly', 'executive'] as $tone)
                                        <option value="{{ $tone }}">{{ str($tone)->headline() }}</option>
                                    @endforeach
                                </select>
                                <select name="length" class="rounded-lg border border-black/10 px-3 py-2 text-sm">
                                    @foreach (['short', 'standard', 'detailed'] as $length)
                                        <option value="{{ $length }}">{{ str($length)->headline() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="mt-3 w-full rounded-lg bg-neutral-950 px-4 py-3 text-sm font-black text-white">Generate Cover Letter</button>
                        </form>
                    </div>

                    @if ($job->coverLetters->isNotEmpty())
                        <div class="mt-5 space-y-3">
                            @foreach ($job->coverLetters as $letter)
                                <div class="rounded-lg border border-black/10 p-4">
                                    <div class="flex items-center justify-between gap-4">
                                        <p class="font-black">{{ $letter->title }}</p>
                                        <a href="{{ route('cover-letters.export', $letter) }}" class="rounded-lg border border-black/10 px-3 py-2 text-xs font-bold">Download PDF</a>
                                    </div>
                                    <p class="mt-3 whitespace-pre-line text-sm leading-6 text-neutral-600">{{ $letter->content }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <aside class="space-y-5">
                <section class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-black">AI Application Copilot</h2>
                    <div class="mt-4 space-y-2">
                        <form method="POST" action="{{ route('jobs.analyze', $job) }}">@csrf <button class="w-full rounded-lg border border-black/10 px-4 py-3 text-left text-sm font-bold">Analyze This Job</button></form>
                        <form method="POST" action="{{ route('jobs.match', $job) }}">@csrf <button class="w-full rounded-lg border border-black/10 px-4 py-3 text-left text-sm font-bold">How Well Do I Match?</button></form>
                        <form method="POST" action="{{ route('jobs.tailor', $job) }}">@csrf <button class="w-full rounded-lg border border-black/10 px-4 py-3 text-left text-sm font-bold">Improve My Resume</button></form>
                        <form method="POST" action="{{ route('jobs.cover-letter', $job) }}">@csrf <input type="hidden" name="tone" value="professional"><input type="hidden" name="length" value="standard"><button class="w-full rounded-lg border border-black/10 px-4 py-3 text-left text-sm font-bold">Generate Cover Letter</button></form>
                    </div>
                </section>

                <section class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-black">Application detail</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div><dt class="font-bold">URL</dt><dd class="mt-1 break-words text-neutral-600">{{ $job->job_url ?: 'Not saved' }}</dd></div>
                        <div><dt class="font-bold">Notes</dt><dd class="mt-1 text-neutral-600">{{ $job->notes ?: 'No notes yet' }}</dd></div>
                    </dl>
                </section>
            </aside>
        </div>
    </div>
@endsection
