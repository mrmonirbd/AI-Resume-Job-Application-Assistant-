@extends('layouts.app')

@section('title', 'Templates')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Resume Templates</p>
                <h1 class="mt-2 text-3xl font-black">{{ $templates->flatten(1)->count() }} polished resume layouts</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-neutral-600">Choose clean ATS-friendly, sidebar, photo-style, executive, academic, technical, and creative layouts for any resume version.</p>
            </div>
            <a href="{{ route('resumes.create') }}" class="inline-flex items-center justify-center rounded-lg bg-neutral-950 px-5 py-3 text-sm font-black text-white">Create Resume</a>
        </div>

        <div class="mt-8 space-y-10">
            @foreach ($templates as $category => $items)
                <section>
                    <div class="flex items-center justify-between gap-4">
                        <h2 class="text-xl font-black">{{ $category }}</h2>
                        <span class="rounded-full bg-white px-3 py-1 text-xs font-bold text-neutral-500">{{ $items->count() }} layouts</span>
                    </div>
                    <div class="mt-4 grid gap-5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                        @foreach ($items as $template)
                            <article class="overflow-hidden rounded-lg border border-black/10 bg-white shadow-sm">
                                <x-resume-template-thumbnail :template="$template" class="rounded-none border-0 shadow-none" />
                                <div class="flex items-start justify-between gap-4 p-4">
                                    <div>
                                        <p class="font-black">{{ $template->name }}</p>
                                        <p class="mt-1 text-sm text-neutral-500">{{ str($template->layout)->headline() }}</p>
                                    </div>
                                    <a href="{{ route('resumes.create', ['template' => $template->slug]) }}" class="shrink-0 rounded-md border border-black/10 px-3 py-2 text-xs font-black hover:bg-[#f6f7f9]">Use</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
@endsection
