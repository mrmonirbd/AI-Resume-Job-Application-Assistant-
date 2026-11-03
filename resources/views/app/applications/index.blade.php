@extends('layouts.app')

@section('title', 'Application Tracker')

@section('content')
    @php
        $stageStyles = [
            'saved' => ['dot' => 'bg-slate-400', 'badge' => 'bg-slate-100 text-slate-600', 'description' => 'Opportunities worth exploring'],
            'preparing' => ['dot' => 'bg-violet-500', 'badge' => 'bg-violet-100 text-violet-700', 'description' => 'Getting your application ready'],
            'applied' => ['dot' => 'bg-blue-500', 'badge' => 'bg-blue-100 text-blue-700', 'description' => 'Submitted and on their radar'],
            'interview' => ['dot' => 'bg-amber-500', 'badge' => 'bg-amber-100 text-amber-700', 'description' => 'Time to make your impression'],
            'offer' => ['dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-100 text-emerald-700', 'description' => 'Your next chapter awaits'],
            'rejected' => ['dot' => 'bg-rose-400', 'badge' => 'bg-rose-100 text-rose-700', 'description' => 'Making room for the right fit'],
            'archived' => ['dot' => 'bg-neutral-400', 'badge' => 'bg-neutral-100 text-neutral-600', 'description' => 'Kept for your records'],
        ];
    @endphp
    <div class="px-4 py-8 sm:px-6 lg:px-8" x-data="applicationBoard({{ Js::from($applications) }}, {{ Js::from(csrf_token()) }})">
        <header class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-brand-700">Your career workspace</p>
                <h1 class="mt-2 text-3xl font-black tracking-tight">Application pipeline</h1>
                <p class="mt-2 text-sm text-muted">Every opportunity, from first look to your next offer.</p>
            </div>
            <a href="{{ route('jobs.create') }}" class="inline-flex w-fit items-center gap-2 rounded-xl bg-brand-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-brand-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-700">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-width="2" d="M12 5v14M5 12h14" /></svg>
                Add application
            </a>
        </header>

        <div class="mt-7 grid grid-cols-2 gap-3 xl:grid-cols-4">
            @foreach ([['label' => 'Total applications', 'value' => 'applications.length', 'detail' => 'Your full opportunity pipeline', 'color' => 'text-brand-700'], ['label' => 'In progress', 'value' => "applications.filter(item => ['saved', 'preparing', 'applied'].includes(item.status)).length", 'detail' => 'Keep the momentum going', 'color' => 'text-blue-600'], ['label' => 'Interviews', 'value' => "inStage('interview').length", 'detail' => 'Conversations that count', 'color' => 'text-amber-600'], ['label' => 'Offers', 'value' => "inStage('offer').length", 'detail' => 'New beginnings ahead', 'color' => 'text-emerald-600']] as $stat)
                <div class="rounded-2xl border border-line bg-white p-4 sm:p-5">
                    <p class="text-xs font-semibold text-muted sm:text-sm">{{ $stat['label'] }}</p>
                    <p class="mt-2 text-3xl font-black {{ $stat['color'] }}" x-text="{{ $stat['value'] }}">0</p>
                    <p class="mt-2 text-xs text-muted">{{ $stat['detail'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-7 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-sm font-bold">Pipeline board <span class="ml-2 rounded-full bg-white px-2.5 py-1 text-xs text-muted" x-text="applications.length + ' applications'"></span></h2>
            <p class="text-xs text-muted">Drag cards between stages or use the move menu.</p>
        </div>
        <div class="mt-3" aria-live="polite" role="status">
            <p x-cloak x-show="pending !== null" class="text-sm text-muted">Saving your move…</p>
            <p x-cloak x-show="message" x-text="message" class="text-sm font-semibold text-emerald-700"></p>
        </div>
        <p x-cloak x-show="error" x-text="error" role="alert" class="mt-3 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700"></p>

        <div x-cloak x-show="applications.length === 0" class="mt-4 flex flex-col items-start gap-4 rounded-2xl border border-line bg-white p-5 sm:flex-row sm:items-center sm:p-6">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700">
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="7" width="18" height="14" rx="3" stroke-width="1.5"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12a24 24 0 0 0 18 0M12 11v4" stroke-width="1.5"/></svg>
            </div>
            <div class="flex-1"><h2 class="font-bold">Your next opportunity starts here</h2><p class="mt-1 text-sm text-muted">Add a job, tailor your resume, and track your progress in one place.</p></div>
            <a href="{{ route('jobs.create') }}" class="text-sm font-bold text-brand-700 hover:underline">Add your first application →</a>
        </div>

        <div class="mt-4 overflow-x-auto pb-5" tabindex="0" aria-label="Application stages. Scroll horizontally to see all stages.">
            <div class="grid min-w-[1920px] grid-cols-7 items-start gap-4">
                @foreach ($stages as $stage)
                    <section class="min-h-[380px] rounded-2xl border border-line bg-white/60 p-3 transition-colors" :class="over === '{{ $stage }}' ? 'ring-2 ring-brand-500 bg-brand-50' : ''" @dragover.prevent="if (dragging !== null && pending === null) over = '{{ $stage }}'" @drop.prevent="move(dragging, '{{ $stage }}')" aria-label="{{ str($stage)->headline() }} applications">
                        <div class="px-1 pb-4 pt-1">
                            <div class="flex items-center gap-2.5">
                                <span class="size-2.5 rounded-full {{ $stageStyles[$stage]['dot'] }}" aria-hidden="true"></span>
                                <h3 class="flex-1 text-sm font-bold">{{ str($stage)->headline() }}</h3>
                                <span class="rounded-lg px-2.5 py-1 text-xs font-bold {{ $stageStyles[$stage]['badge'] }}" x-text="inStage('{{ $stage }}').length">0</span>
                            </div>
                            <p class="mt-2 text-xs text-muted">{{ $stageStyles[$stage]['description'] }}</p>
                        </div>
                        <div class="space-y-3">
                            <template x-for="application in inStage('{{ $stage }}')" :key="application.id">
                                <article class="rounded-xl border border-line bg-white p-4 shadow-sm transition hover:border-brand-300 hover:shadow-md" :class="pending === application.id ? 'opacity-60' : 'cursor-grab active:cursor-grabbing'" :draggable="pending === null" :aria-busy="pending === application.id" @dragstart="dragging = application.id; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', String(application.id))" @dragend="dragging = null; over = null">
                                    <div class="flex items-start gap-3">
                                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-base font-black uppercase text-brand-700" x-text="(application.company || '?').slice(0, 1)"></span>
                                        <div class="min-w-0 flex-1">
                                            <p class="break-words text-xs font-semibold text-muted" x-text="application.company"></p>
                                            <a :href="application.url" draggable="false" class="mt-1 block break-words text-sm font-bold leading-5 hover:text-brand-700" x-text="application.title"></a>
                                        </div>
                                    </div>
                                    <p class="mt-4 truncate text-xs text-muted" :title="application.location" x-text="application.location"></p>
                                    <div class="mt-3 flex items-center justify-between border-b border-line pb-4">
                                        <span class="rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700" x-text="application.score !== null ? application.score + '% match' : 'Not analyzed'"></span>
                                        <a :href="application.url" draggable="false" class="text-xs font-bold text-brand-700 hover:underline">View job ↗</a>
                                    </div>
                                    <label class="mt-3 flex items-center gap-2 text-xs font-semibold text-muted">
                                        Move to
                                        <select :value="application.status" :disabled="pending !== null" @change="const target = $event.target.value; $event.target.value = application.status; move(application.id, target)" class="min-w-0 flex-1 rounded-lg border border-line bg-white px-2 py-2 text-xs font-semibold text-ink focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-100 disabled:cursor-wait">
                                            @foreach ($stages as $targetStage)
                                                <option value="{{ $targetStage }}">{{ str($targetStage)->headline() }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </article>
                            </template>
                            <div x-show="inStage('{{ $stage }}').length === 0" class="flex min-h-48 flex-col items-center justify-center rounded-xl border border-dashed border-line px-4 text-center">
                                <span class="mb-3 flex size-8 items-center justify-center rounded-full bg-white text-lg text-muted" aria-hidden="true">+</span>
                                <p class="text-xs font-semibold text-muted">No applications yet</p>
                                <p class="mt-1 text-xs text-muted">Drop a card here to move it</p>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@endsection
