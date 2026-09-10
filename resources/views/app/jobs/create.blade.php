@extends('layouts.app')

@section('title', 'Add Job')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Add Job</p>
            <h1 class="mt-2 text-3xl font-black">Paste a job description</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-neutral-600">The analyzer extracts skills, responsibilities, keywords, and requirements, then you can compare the role against your master resume.</p>
        </div>

        <form method="POST" action="{{ route('jobs.store') }}" class="mt-8 rounded-lg border border-black/10 bg-white p-6 shadow-sm">
            @csrf
            <div class="grid gap-5 md:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-semibold">Company</span>
                    <input name="company" value="{{ old('company') }}" required class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                    <x-field-error name="company" />
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Job title</span>
                    <input name="title" value="{{ old('title') }}" required class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                    <x-field-error name="title" />
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Job URL</span>
                    <input name="job_url" value="{{ old('job_url') }}" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                    <x-field-error name="job_url" />
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Location</span>
                    <input name="location" value="{{ old('location') }}" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Salary</span>
                    <input name="salary" value="{{ old('salary') }}" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold">Job description</span>
                    <textarea name="description" rows="14" required class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">{{ old('description') }}</textarea>
                    <x-field-error name="description" />
                </label>
                <label class="block md:col-span-2">
                    <span class="text-sm font-semibold">Notes</span>
                    <textarea name="notes" rows="4" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">{{ old('notes') }}</textarea>
                </label>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('jobs.index') }}" class="rounded-lg border border-black/10 px-5 py-3 text-sm font-bold">Cancel</a>
                <button class="rounded-lg bg-neutral-950 px-5 py-3 text-sm font-black text-white">Analyze Job</button>
            </div>
        </form>
    </div>
@endsection
