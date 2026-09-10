@extends('layouts.app')

@section('title', 'Templates')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Resume Templates</p>
            <h1 class="mt-2 text-3xl font-black">Ten ATS-friendly starting points</h1>
        </div>
        <div class="mt-8 space-y-8">
            @foreach ($templates as $category => $items)
                <section>
                    <h2 class="text-xl font-black">{{ $category }}</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($items as $template)
                            <article class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                                <div class="h-72 rounded-md border border-black/10 bg-white p-5" style="font-family: {{ $template->font_family }}, Arial, sans-serif;">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="h-5 w-32 rounded bg-neutral-900"></div>
                                            <div class="mt-2 h-2 w-44 rounded" style="background: {{ $template->accent_color }}"></div>
                                        </div>
                                        <div class="h-8 w-8 rounded-full" style="background: {{ $template->accent_color }}"></div>
                                    </div>
                                    <div class="mt-7 grid {{ str_contains($template->layout, 'column') || str_contains($template->layout, 'sidebar') ? 'grid-cols-[0.85fr_1.5fr]' : 'grid-cols-1' }} gap-4">
                                        <div class="space-y-2">
                                            <div class="h-3 w-20 rounded bg-neutral-900"></div>
                                            <div class="h-2 rounded bg-neutral-200"></div>
                                            <div class="h-2 rounded bg-neutral-200"></div>
                                            <div class="h-2 rounded bg-neutral-200"></div>
                                        </div>
                                        <div class="space-y-3">
                                            <div class="h-3 w-24 rounded bg-neutral-900"></div>
                                            <div class="h-2 rounded bg-neutral-200"></div>
                                            <div class="h-2 rounded bg-neutral-200"></div>
                                            <div class="h-2 w-3/4 rounded bg-neutral-200"></div>
                                            <div class="h-3 w-28 rounded bg-neutral-900"></div>
                                            <div class="h-2 rounded bg-neutral-200"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-black">{{ $template->name }}</p>
                                        <p class="mt-1 text-sm text-neutral-500">{{ str($template->layout)->headline() }}</p>
                                    </div>
                                    @if ($template->is_premium)
                                        <span class="rounded-full bg-neutral-950 px-3 py-1 text-xs font-bold text-white">Pro</span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
@endsection
