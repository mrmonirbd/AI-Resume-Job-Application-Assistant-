@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Account</p>
            <h1 class="mt-2 text-3xl font-black">Profile and security</h1>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <form method="POST" action="{{ route('profile.update') }}" class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                @csrf
                @method('PATCH')
                <div class="flex items-center gap-4">
                    <x-demo-profile-image class="h-16 w-16 border border-black/10 shadow-sm" />
                    <div>
                        <h2 class="text-xl font-black">Profile</h2>
                        <p class="mt-1 text-sm text-neutral-500">{{ $user->professional_title ?: 'Career profile' }}</p>
                    </div>
                </div>
                <div class="mt-5 space-y-4">
                    <label class="block">
                        <span class="text-sm font-semibold">Name</span>
                        <input name="name" value="{{ old('name', $user->name) }}" required class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                    </label>
                    <label class="block">
                        <span class="text-sm font-semibold">Email</span>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
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
                </div>
                <button class="mt-5 rounded-lg bg-neutral-950 px-5 py-3 text-sm font-black text-white">Save profile</button>
            </form>

            <div class="space-y-6">
                <form method="POST" action="{{ route('profile.password') }}" class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                    @csrf
                    @method('PUT')
                    <h2 class="text-xl font-black">Change password</h2>
                    <div class="mt-5 space-y-4">
                        <input type="password" name="current_password" required placeholder="Current password" class="w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                        <input type="password" name="password" required placeholder="New password" class="w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                        <input type="password" name="password_confirmation" required placeholder="Confirm new password" class="w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                    </div>
                    <button class="mt-5 rounded-lg bg-neutral-950 px-5 py-3 text-sm font-black text-white">Change password</button>
                </form>

                <form method="POST" action="{{ route('profile.destroy') }}" class="rounded-lg border border-red-200 bg-white p-6 shadow-sm" onsubmit="return confirm('Delete your account permanently?')">
                    @csrf
                    @method('DELETE')
                    <h2 class="text-xl font-black text-red-700">Delete account</h2>
                    <p class="mt-2 text-sm leading-6 text-neutral-600">This permanently removes resumes, jobs, cover letters, and application history.</p>
                    <input type="password" name="password" required placeholder="Confirm password" class="mt-5 w-full rounded-lg border border-black/10 px-4 py-3 text-sm">
                    <button class="mt-5 rounded-lg bg-red-700 px-5 py-3 text-sm font-black text-white">Delete account</button>
                </form>
            </div>
        </div>
    </div>
@endsection
