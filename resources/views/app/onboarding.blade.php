@extends('layouts.app')

@section('title', 'Onboarding')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8" x-data="{ step: 1, method: '{{ old('start_method', 'ai') }}' }">
        <div class="mb-8">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Onboarding</p>
            <h1 class="mt-2 text-3xl font-black">How would you like to start?</h1>
            <div class="mt-5 grid grid-cols-5 gap-2">
                @foreach (['Personal Info', 'Experience', 'Education', 'Skills', 'Review'] as $label)
                    <button type="button" x-on:click="step = {{ $loop->iteration }}" class="rounded-lg border px-3 py-2 text-left text-xs font-bold" :class="step === {{ $loop->iteration }} ? 'border-neutral-950 bg-neutral-950 text-white' : 'border-black/10 bg-white text-neutral-600'">{{ $label }}</button>
                @endforeach
            </div>
        </div>

        <form method="POST" action="{{ route('onboarding.store') }}" class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
            @csrf
            <input type="hidden" name="start_method" x-model="method">

            <div class="grid gap-4 md:grid-cols-3" x-show="step === 1">
                @foreach ([['ai', 'Create with AI'], ['manual', 'Start Manually'], ['import', 'Import Existing Resume']] as [$value, $label])
                    <button type="button" x-on:click="method = '{{ $value }}'" class="rounded-lg border p-5 text-left" :class="method === '{{ $value }}' ? 'border-neutral-950 bg-neutral-950 text-white' : 'border-black/10 bg-white'">
                        <span class="text-lg font-black">{{ $label }}</span>
                        <span class="mt-3 block text-sm opacity-70">{{ $value === 'ai' ? 'Use guided prompts and optional AI cleanup.' : ($value === 'manual' ? 'Enter clean structured details yourself.' : 'Paste an existing resume and organize it.') }}</span>
                    </button>
                @endforeach

                <div class="md:col-span-3 grid gap-4 md:grid-cols-2">
                    <label class="block">
                        <span class="text-sm font-semibold">Name</span>
                        <input name="name" value="{{ old('name', $user->name) }}" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                        <x-field-error name="name" />
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold">Professional title</span>
                        <input name="professional_title" value="{{ old('professional_title', $user->professional_title) }}" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold">Location</span>
                        <input name="location" value="{{ old('location', $user->location) }}" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold">Career goal</span>
                        <input name="career_goal" value="{{ old('career_goal', $user->career_goal) }}" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                    </label>
                    <label class="block md:col-span-2">
                        <span class="text-sm font-semibold">Professional summary</span>
                        <textarea name="summary" rows="4" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">{{ old('summary') }}</textarea>
                    </label>
                </div>
            </div>

            <div x-show="step === 2">
                <label class="block">
                    <span class="text-sm font-semibold">Experience</span>
                    <textarea name="experience" rows="10" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="Paste verified roles, companies, responsibilities, and achievements.">{{ old('experience') }}</textarea>
                </label>
                <label class="mt-4 block">
                    <span class="text-sm font-semibold">Projects</span>
                    <textarea name="projects" rows="6" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">{{ old('projects') }}</textarea>
                </label>
            </div>

            <div x-show="step === 3">
                <label class="block">
                    <span class="text-sm font-semibold">Education</span>
                    <textarea name="education" rows="8" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">{{ old('education') }}</textarea>
                </label>
                <label class="mt-4 block">
                    <span class="text-sm font-semibold">Certifications</span>
                    <textarea name="certifications" rows="5" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">{{ old('certifications') }}</textarea>
                </label>
            </div>

            <div x-show="step === 4">
                <label class="block">
                    <span class="text-sm font-semibold">Skills</span>
                    <textarea name="skills" rows="6" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm" placeholder="Laravel, PHP, MySQL, REST APIs">{{ old('skills') }}</textarea>
                </label>
                <label class="mt-4 block">
                    <span class="text-sm font-semibold">Languages</span>
                    <textarea name="languages" rows="4" class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">{{ old('languages') }}</textarea>
                </label>
            </div>

            <div x-show="step === 5" class="rounded-lg border border-black/10 bg-[#f6f7f9] p-5">
                <h2 class="text-xl font-black">Ready to create your master resume</h2>
                <p class="mt-2 text-sm leading-6 text-neutral-600">Your master resume becomes the verified source of truth. Tailored resumes are created as new versions and will not overwrite it.</p>
            </div>

            <div class="mt-8 flex justify-between">
                <button type="button" x-on:click="step = Math.max(1, step - 1)" class="rounded-lg border border-black/10 px-5 py-3 text-sm font-bold">Back</button>
                <button type="button" x-show="step < 5" x-on:click="step = Math.min(5, step + 1)" class="rounded-lg bg-neutral-950 px-5 py-3 text-sm font-black text-white">Continue</button>
                <button x-show="step === 5" class="rounded-lg bg-neutral-950 px-5 py-3 text-sm font-black text-white">Create workspace</button>
            </div>
        </form>
    </div>
@endsection
