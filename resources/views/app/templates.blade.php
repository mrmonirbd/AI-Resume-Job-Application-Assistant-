@extends('layouts.app')

@section('title', 'Templates')

@section('content')
    @php
        $templateCount = $templateCount ?? $templates->flatten(1)->count();
        $sort = $sort ?? 'default';
    @endphp

    <div
        class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8"
        x-data="{ activeTemplate: null }"
        x-effect="document.body.classList.toggle('overflow-hidden', activeTemplate !== null)"
        @keydown.escape.window="activeTemplate = null"
    >
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Resume Templates</p>
                <h1 class="mt-2 text-3xl font-black">{{ $templateCount }} polished resume layouts</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-neutral-600">A clean resume template workspace with ATS-friendly, sidebar, photo-style, executive, academic, technical, and creative layouts.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('templates.index') }}" class="flex w-full items-center gap-2 rounded-lg border border-black/10 bg-white p-2 shadow-sm sm:w-auto">
                    <label for="template-sort" class="px-2 text-xs font-black uppercase tracking-[0.16em] text-neutral-500">Sort</label>
                    <select id="template-sort" name="sort" onchange="this.form.submit()" class="h-10 min-w-0 flex-1 rounded-md border border-black/10 bg-white px-3 text-sm font-bold text-neutral-800 outline-none focus:border-teal-700 sm:min-w-48">
                        @foreach ($sortOptions ?? ['default' => 'Default'] as $value => $label)
                            <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
                <a href="{{ route('resumes.create') }}" class="inline-flex h-12 w-full items-center justify-center rounded-lg bg-neutral-950 px-5 text-sm font-black text-white sm:w-auto">Create Resume</a>
            </div>
        </div>

        <div class="mt-8 space-y-10">
            @foreach ($templates as $category => $items)
                <section>
                    @if ($sort === 'category')
                        <div class="flex items-center justify-between gap-4">
                            <h2 class="text-xl font-black">{{ $category }}</h2>
                            <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-neutral-500">{{ $items->count() }} layouts</span>
                        </div>
                    @endif

                    <div @class([
                        'grid gap-5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4',
                        'mt-4' => $sort === 'category',
                    ])>
                        @foreach ($items as $template)
                            <article class="group relative overflow-hidden rounded-lg border border-black/10 bg-white shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-xl">
                                <div class="relative">
                                    <x-resume-template-thumbnail :template="$template" class="rounded-none border-0 shadow-none" />
                                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center bg-neutral-950/0 opacity-0 transition duration-200 group-hover:bg-neutral-950/35 group-hover:opacity-100">
                                        <span class="rounded-full bg-white px-4 py-2 text-xs font-black uppercase tracking-[0.14em] text-neutral-950 shadow-sm">Preview</span>
                                    </div>
                                </div>
                                <div class="flex items-start justify-between gap-4 p-4">
                                    <div>
                                        <p class="font-black">{{ $template->name }}</p>
                                        <p class="mt-1 text-sm text-neutral-500">{{ str($template->layout)->headline() }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-md border border-black/10 px-3 py-2 text-xs font-black text-neutral-700 group-hover:bg-[#f6f7f9]">Preview</span>
                                </div>
                                <button type="button" class="absolute inset-0 z-10 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-700 focus-visible:ring-offset-2" @click="activeTemplate = '{{ $template->slug }}'">
                                    <span class="sr-only">Preview {{ $template->name }}</span>
                                </button>
                            </article>

                            <div
                                x-cloak
                                x-show="activeTemplate === '{{ $template->slug }}'"
                                x-transition.opacity
                                class="fixed inset-0 z-50 overflow-y-auto bg-neutral-950/65 p-4 backdrop-blur-sm sm:p-6"
                                role="dialog"
                                aria-modal="true"
                                aria-labelledby="template-preview-{{ $template->id }}"
                                @click.self="activeTemplate = null"
                            >
                                <div class="mx-auto flex min-h-full max-w-6xl items-center justify-center">
                                    <div class="w-full overflow-hidden rounded-lg bg-white shadow-2xl" @click.stop>
                                        <header class="flex items-start justify-between gap-4 border-b border-black/10 p-5">
                                            <div>
                                                <p class="text-xs font-black uppercase tracking-[0.18em] text-teal-700">Template Preview</p>
                                                <h2 id="template-preview-{{ $template->id }}" class="mt-1 text-2xl font-black">{{ $template->name }}</h2>
                                                <p class="mt-1 text-sm text-neutral-500">{{ $template->category }} &middot; {{ str($template->layout)->headline() }}</p>
                                            </div>
                                            <button type="button" class="rounded-md border border-black/10 px-3 py-2 text-xs font-black text-neutral-600 hover:bg-[#f6f7f9]" @click="activeTemplate = null">Close</button>
                                        </header>

                                        <div class="grid gap-6 p-5 lg:grid-cols-[minmax(21rem,1fr)_minmax(17rem,0.55fr)]">
                                            <div class="flex justify-center rounded-lg bg-[#f6f7f9] p-4">
                                                <x-resume-template-thumbnail :template="$template" :preview="true" class="mx-auto w-full max-w-[30rem] rounded-md shadow-lg" />
                                            </div>

                                            <aside class="flex flex-col gap-5">
                                                <div>
                                                    <p class="text-sm font-black uppercase tracking-[0.16em] text-neutral-500">Layout</p>
                                                    <p class="mt-2 text-lg font-black">{{ str($template->layout)->headline() }}</p>
                                                    <p class="mt-2 text-sm leading-6 text-neutral-600">{{ $template->name }} keeps your profile, experience, education, skills, and contact details in a polished resume structure.</p>
                                                </div>

                                                <dl class="grid gap-3 text-sm">
                                                    <div class="flex items-center justify-between gap-4 border-b border-black/10 pb-3">
                                                        <dt class="font-bold text-neutral-500">Category</dt>
                                                        <dd class="font-black">{{ $template->category }}</dd>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-4 border-b border-black/10 pb-3">
                                                        <dt class="font-bold text-neutral-500">Font</dt>
                                                        <dd class="font-black">{{ $template->font_family }}</dd>
                                                    </div>
                                                    <div class="flex items-center justify-between gap-4 border-b border-black/10 pb-3">
                                                        <dt class="font-bold text-neutral-500">Accent</dt>
                                                        <dd class="flex items-center gap-2 font-black">
                                                            <span class="h-4 w-4 rounded-full border border-black/10" style="background: {{ $template->accent_color }}"></span>
                                                            {{ $template->accent_color }}
                                                        </dd>
                                                    </div>
                                                </dl>

                                                <div class="mt-auto flex flex-col gap-3 border-t border-black/10 pt-5">
                                                    <a href="{{ route('resumes.create', ['template' => $template->slug]) }}" class="inline-flex h-12 items-center justify-center rounded-lg bg-neutral-950 px-5 text-sm font-black text-white">Use this template</a>
                                                    <button type="button" class="inline-flex h-12 items-center justify-center rounded-lg border border-black/10 px-5 text-sm font-black text-neutral-700 hover:bg-[#f6f7f9]" @click="activeTemplate = null">Back to templates</button>
                                                </div>
                                            </aside>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
@endsection
