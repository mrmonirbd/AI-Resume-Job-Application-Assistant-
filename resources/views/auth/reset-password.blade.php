@extends('layouts.guest')

@section('content')
    <div class="flex min-h-screen items-center justify-center px-5 py-12">
        <div class="w-full max-w-md rounded-lg border border-black/10 bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-black">Choose a new password</h1>
            <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <label class="block">
                    <span class="text-sm font-semibold">Email</span>
                    <input type="email" name="email" value="{{ old('email', $request->email) }}" required class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm outline-none focus:border-neutral-950">
                    <x-field-error name="email" />
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Password</span>
                    <input type="password" name="password" required class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm outline-none focus:border-neutral-950">
                    <x-field-error name="password" />
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Confirm password</span>
                    <input type="password" name="password_confirmation" required class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm outline-none focus:border-neutral-950">
                </label>
                <button class="w-full rounded-lg bg-neutral-950 px-5 py-3 text-sm font-black text-white">Reset password</button>
            </form>
        </div>
    </div>
@endsection
