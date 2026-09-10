@extends('layouts.app')

@section('title', 'Create Resume')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Create Resume</p>
            <h1 class="mt-2 text-3xl font-black">Start a new resume version</h1>
        </div>
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
                    <span class="text-sm font-semibold">Template</span>
                    <select name="template_id" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                        @foreach ($templates as $template)
                            <option value="{{ $template->id }}">{{ $template->name }} · {{ $template->category }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Spacing</span>
                    <select name="spacing" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                        <option value="compact">Compact</option>
                        <option value="comfortable" selected>Comfortable</option>
                        <option value="spacious">Spacious</option>
                    </select>
                </label>
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
