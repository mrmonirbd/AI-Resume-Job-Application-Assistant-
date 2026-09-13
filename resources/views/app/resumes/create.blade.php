@extends('layouts.app')

@section('title', 'Create Resume')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Create Resume</p>
            <h1 class="mt-2 text-3xl font-black">Start a new resume version</h1>
        </div>
        @php($selectedTemplateId = old('template_id', $selectedTemplate?->id))
        <form method="POST" action="{{ route('resumes.store') }}" class="mt-8 rounded-lg border border-black/10 bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-5 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-semibold">Resume name</span>
                    <input name="title" value="{{ old('title') }}" required class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                    <x-field-error name="title" />
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Professional title</span>
                    <input name="professional_title" value="{{ old('professional_title') }}" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Spacing</span>
                    <select name="spacing" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                        <option value="compact" @selected(old('spacing') === 'compact')>Compact</option>
                        <option value="comfortable" @selected(old('spacing', 'comfortable') === 'comfortable')>Comfortable</option>
                        <option value="spacious" @selected(old('spacing') === 'spacious')>Spacious</option>
                    </select>
                </label>
                <div class="md:col-span-2">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold">Template</span>
                        <span class="text-xs font-bold text-neutral-500">{{ $templates->count() }} layouts</span>
                    </div>
                    <div class="mt-3 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($templates as $template)
                            <label class="group cursor-pointer">
                                <input type="radio" name="template_id" value="{{ $template->id }}" class="peer sr-only" @checked((string) $selectedTemplateId === (string) $template->id)>
                                <span class="block overflow-hidden rounded-lg border border-black/10 bg-white shadow-sm transition peer-checked:border-neutral-950 peer-checked:ring-2 peer-checked:ring-neutral-950/10 group-hover:border-neutral-400">
                                    <x-resume-template-thumbnail :template="$template" compact class="rounded-none border-0 shadow-none" />
                                    <span class="flex items-center justify-between gap-3 p-3">
                                        <span>
                                            <span class="block text-sm font-black">{{ $template->name }}</span>
                                            <span class="mt-1 block text-xs text-neutral-500">{{ $template->category }} · {{ str($template->layout)->headline() }}</span>
                                        </span>
                                        <span class="rounded-full border border-black/10 px-3 py-1 text-xs font-bold">Select</span>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <x-field-error name="template_id" />
                </div>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold">Professional summary</span>
                    <textarea name="professional_summary" rows="5" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">{{ old('professional_summary') }}</textarea>
                </label>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('resumes.index') }}" class="rounded-lg border border-black/10 px-5 py-3 text-sm font-bold">Cancel</a>
                <button class="rounded-lg bg-neutral-950 px-5 py-3 text-sm font-black text-white">Create Resume</button>
            </div>
        </form>
    </div>
@endsection
