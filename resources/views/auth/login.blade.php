@extends('layouts.guest')

@section('content')
    <div class="flex min-h-screen items-center justify-center px-5 py-12">
        <div class="w-full max-w-md rounded-lg border border-black/10 bg-white p-8 shadow-sm">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-neutral-950 text-sm font-black text-white">AI</span>
                <span class="font-bold">Resume Assistant</span>
            </a>
            <h1 class="mt-8 text-2xl font-black">Welcome back</h1>
            <p class="mt-2 text-sm text-neutral-500">Continue tailoring resumes and tracking applications.</p>
            <div class="mt-5 rounded-lg border border-teal-200 bg-teal-50 p-4 text-sm text-teal-900">
                <p class="font-black">Demo login</p>
                <p class="mt-2"><span class="font-semibold">Email:</span> demo@example.com</p>
                <p><span class="font-semibold">Password:</span> password</p>
            </div>
            @if (session('status'))
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf
                <label class="block">
                    <span class="text-sm font-semibold">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm outline-none focus:border-neutral-950">
                    <x-field-error name="email" />
                </label>
                <label class="block">
                    <span class="text-sm font-semibold">Password</span>
                    <input type="password" name="password" required class="mt-2 w-full rounded-lg border border-black/10 px-4 py-3 text-sm outline-none focus:border-neutral-950">
                    <x-field-error name="password" />
                </label>
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-neutral-600">
                        <input type="checkbox" name="remember" class="rounded border-black/20">
                        Remember me
                    </label>
                    <a href="{{ route('password.request') }}" class="font-bold text-neutral-950">Forgot password?</a>
                </div>
                <button class="w-full rounded-lg bg-neutral-950 px-5 py-3 text-sm font-black text-white hover:bg-neutral-800">Log in</button>
            </form>
            <p class="mt-6 text-center text-sm text-neutral-500">New accounts will be created through the API flow.</p>
        </div>
    </div>
@endsection
