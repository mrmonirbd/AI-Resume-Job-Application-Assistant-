@extends('layouts.app')

@section('title', 'Account security')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div><p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Settings</p><h1 class="mt-2 text-3xl font-black">Account security</h1></div>
        <div class="mt-8 grid gap-6 lg:grid-cols-[15rem_minmax(0,1fr)]">
            @include('account.partials.settings-sidebar')
            <div class="space-y-6">
                <form method="POST" action="{{ route('profile.password') }}" class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                    @csrf
                    @method('PUT')
                    <h2 class="text-lg font-black">Change password</h2><p class="mt-1 text-sm text-neutral-500">When linked, the password update is also sent to the CRM user account.</p>
                    @error('password')<p class="mt-4 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    <div class="mt-5 space-y-4"><input type="password" name="current_password" required placeholder="Current password" class="w-full rounded-md border border-black/10 px-4 py-3 text-sm"><input type="password" name="password" required placeholder="New password" class="w-full rounded-md border border-black/10 px-4 py-3 text-sm"><input type="password" name="password_confirmation" required placeholder="Confirm new password" class="w-full rounded-md border border-black/10 px-4 py-3 text-sm"></div>
                    <button class="mt-5 rounded-md bg-neutral-950 px-5 py-3 text-sm font-black text-white">Change password</button>
                </form>
                <form method="POST" action="{{ route('profile.destroy') }}" class="rounded-lg border border-red-200 bg-white p-6 shadow-sm" onsubmit="return confirm('Delete your account permanently?')">
                    @csrf
                    @method('DELETE')
                    <h2 class="text-lg font-black text-red-700">Delete account</h2><p class="mt-2 text-sm leading-6 text-neutral-600">This removes your workspace data. Billing changes must be handled from Membership.</p>
                    <input type="password" name="password" required placeholder="Confirm password" class="mt-5 w-full rounded-md border border-black/10 px-4 py-3 text-sm"><button class="mt-5 rounded-md border border-red-300 px-5 py-3 text-sm font-black text-red-700">Delete account</button>
                </form>
            </div>
        </div>
    </div>
@endsection
