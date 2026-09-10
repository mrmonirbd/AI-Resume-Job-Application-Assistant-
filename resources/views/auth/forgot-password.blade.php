@extends('layouts.guest')

@section('content')
    <div class="flex min-h-screen items-center justify-center px-5 py-12">
        <div class="w-full max-w-md rounded-lg border border-black/10 bg-white p-8 shadow-sm">
            <h1 class="text-2xl font-black">Reset your password</h1>
            <p class="mt-2 text-sm text-neutral-500">Enter your email and Laravel will send a secure reset link.</p>
            @if (session('status'))
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
                @csrf
                <label class="block">
                    <span class="text-sm font-semibold">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm outline-none focus:border-neutral-950">
                    <x-field-error name="email" />
                </label>
                <button class="w-full rounded-lg bg-neutral-950 px-5 py-3 text-sm font-black text-white">Send reset link</button>
            </form>
        </div>
    </div>
@endsection
