@extends('layouts.app')

@section('title', 'Application Tracker')

@section('content')
    <div class="px-4 py-8 sm:px-6 lg:px-8" x-data="{ dragging: null, move(stage) { if (!this.dragging) return; const form = document.getElementById('move-application-' + this.dragging); form.querySelector('[name=status]').value = stage; form.submit(); } }">
        <div class="mx-auto max-w-7xl">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Application Tracking</p>
                    <h1 class="mt-2 text-3xl font-black">Pipeline board</h1>
                </div>
                <a href="{{ route('jobs.create') }}" class="rounded-lg bg-neutral-950 px-4 py-3 text-sm font-black text-white">Add Job Application</a>
            </div>
        </div>

        <div class="mt-8 overflow-x-auto pb-4">
            <div class="grid min-w-[1180px] grid-cols-7 gap-4">
                @foreach ($stages as $stage)
                    <section class="rounded-lg border border-black/10 bg-white p-3 shadow-sm" @dragover.prevent @drop.prevent="move('{{ $stage }}')">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-black">{{ str($stage)->headline() }}</h2>
                            <span class="rounded-full bg-neutral-100 px-2 py-1 text-xs font-bold">{{ ($applicationsByStage[$stage] ?? collect())->count() }}</span>
                        </div>
                        <div class="mt-4 space-y-3">
                            @forelse (($applicationsByStage[$stage] ?? collect()) as $application)
                                @php($bestScore = $application->job->matches->max('overall_score'))
                                <article draggable="true" @dragstart="dragging = {{ $application->id }}" class="cursor-move rounded-lg border border-black/10 bg-[#f6f7f9] p-3">
                                    <p class="text-sm font-black">{{ $application->job->company }}</p>
                                    <p class="mt-1 text-sm text-neutral-700">{{ $application->job->title }}</p>
                                    <p class="mt-2 text-xs text-neutral-500">{{ $application->job->location ?: 'Location open' }}</p>
                                    <div class="mt-3 flex items-center justify-between">
                                        <span class="rounded-full bg-white px-2 py-1 text-xs font-bold">{{ $bestScore ? $bestScore.'%' : 'No score' }}</span>
                                        <a href="{{ route('jobs.show', $application->job) }}" class="text-xs font-bold text-teal-700">Open</a>
                                    </div>
                                    <form id="move-application-{{ $application->id }}" method="POST" action="{{ route('applications.stage', $application) }}" class="mt-3">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $stage }}">
                                        <select onchange="this.form.querySelector('[name=status]').value = this.value; this.form.submit()" class="w-full rounded-md border border-black/10 bg-white px-2 py-2 text-xs font-semibold">
                                            @foreach ($stages as $targetStage)
                                                <option value="{{ $targetStage }}" @selected($targetStage === $stage)>{{ str($targetStage)->headline() }}</option>
                                            @endforeach
                                        </select>
                                    </form>
                                </article>
                            @empty
                                <div class="rounded-lg border border-dashed border-black/15 p-4 text-xs leading-5 text-neutral-500">Drop applications here.</div>
                            @endforelse
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@endsection
