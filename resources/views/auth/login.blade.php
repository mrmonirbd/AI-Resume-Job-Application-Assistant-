@extends('layouts.guest')

@section('content')
    <main class="grid min-h-dvh bg-[#fff8f1] lg:grid-cols-[47%_53%]">
        <aside class="relative hidden min-h-dvh overflow-hidden login-gradient-panel text-[#16143a] lg:flex lg:flex-col lg:justify-center lg:px-12 xl:px-20" aria-label="Rezumax AI Resume Builder">
            <div class="relative mx-auto w-full max-w-lg py-12">
                <p class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.2em] text-[#5b5a7a]"><span class="h-px w-8 bg-[#ff6a45]"></span> Rezumax AI Resume Builder</p>
                <h2 class="mt-6 text-4xl font-semibold leading-tight tracking-tight xl:text-5xl">Build a resume<br>for <span class="login-gradient-text">your next role.</span></h2>
                <p class="mt-5 max-w-sm text-sm leading-7 text-[#5b5a7a]">Draft your summary and achievements with AI, then choose a layout and make it your own.</p>

                <div class="my-8 flex justify-center xl:my-10">
                    <img src="{{ asset('images/rezumax-hero-woman.webp') }}" alt="Smiling professional holding her printed resume" width="878" height="1176" class="h-auto max-h-[46vh] w-auto max-w-full rounded-3xl object-contain" fetchpriority="high">
                </div>
                <div class="flex items-center gap-3 border-t border-[#ece3d8] pt-6">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-[#ece8ff] text-[#5544d8]"><svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 8 4v5c0 5-8 9-8 9s-8-4-8-9V7l8-4Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 3 3 5-6"/></svg></span>
                    <p class="text-xs leading-5 text-[#5b5a7a]">Pick up where you left off.<br><span class="text-[#16143a]">Sign in to keep editing your resume.</span></p>
                </div>
            </div>
        </aside>

        <section class="relative flex min-h-dvh flex-col px-6 sm:px-12 lg:px-16">
            <header class="flex justify-end py-7 sm:py-9">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2.5 text-sm font-bold text-ink" aria-label="Rezumax login">
                    <img src="{{ asset('images/rezumax-stacked.svg') }}" alt="Rezumax" width="88" height="60" class="h-auto w-28 object-contain sm:w-32">
                </a>
            </header>
            <div class="flex flex-1 items-center justify-center pb-16 pt-10 sm:pb-24">
                <div class="w-full max-w-[400px]" x-data="{ forgot: {{ request()->routeIs('password.request') || session('auth_form') === 'forgot' ? 'true' : 'false' }} }">
                    <h1 class="text-4xl font-bold tracking-tight text-[#16143a] sm:text-[42px]" x-text="forgot ? 'RESET PASSWORD' : 'LOGIN'">LOGIN</h1>
                    <p class="mt-3 text-base leading-6 text-[#5b5a7a]" x-text="forgot ? 'Enter your email address and we will send you a password reset link.' : 'Sign in with your email address to access your account'">Sign in with your email address to access your account</p>

                    @if (session('status'))
                        <div role="status" class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</div>
                    @endif
                    @error('error')
                        <p role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</p>
                    @enderror

                    <form x-show="!forgot" method="POST" action="{{ route('login') }}" class="mt-12 sm:mt-16">
                        @csrf
                        <div>
                            <label for="login-email" class="sr-only">Email address</label>
                            <div class="flex items-center gap-4 border-b border-[#ece3d8] bg-[#fdf0e4] focus-within:bg-white px-3 focus-within:border-[#6d5bff]">
                                <svg class="size-5 shrink-0 text-[#5544d8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg>
                                <input id="login-email" type="email" name="email" value="{{ old('email') }}" placeholder="Email address" autocomplete="username" required autofocus @error('email') aria-invalid="true" aria-describedby="login-email-error" @enderror class="min-w-0 flex-1 border-0 bg-transparent py-5 text-base text-[#16143a] outline-none placeholder:text-[#5b5a7a] focus-visible:outline-none">
                            </div>
                            <div id="login-email-error"><x-field-error name="email" /></div>
                        </div>
                        <div class="mt-6">
                            <label for="login-password" class="sr-only">Password</label>
                            <div class="flex items-center gap-4 border-b border-[#ece3d8] bg-[#fdf0e4] focus-within:bg-white px-3 focus-within:border-[#6d5bff]">
                                <svg class="size-5 shrink-0 text-[#5544d8]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><path stroke-linecap="round" d="M12 14v3"/></svg>
                                <input id="login-password" type="password" name="password" placeholder="Password" autocomplete="current-password" required @error('password') aria-invalid="true" aria-describedby="login-password-error" @enderror class="min-w-0 flex-1 border-0 bg-transparent py-5 text-base text-[#16143a] outline-none placeholder:text-[#5b5a7a] focus-visible:outline-none">
                            </div>
                            <div id="login-password-error"><x-field-error name="password" /></div>
                        </div>
                        <div class="mt-7 flex flex-wrap items-center justify-between gap-3 text-sm">
                            <label class="flex cursor-pointer items-center gap-2 text-[#5b5a7a]">
                                <input type="checkbox" name="remember" @checked(old('remember')) class="size-4 accent-[#2a2760]">
                                Remember me
                            </label>
                            <a @click.prevent="forgot = true; $nextTick(() => $refs.resetEmail.focus())" href="{{ route('password.request') }}" class="font-medium text-[#2a2760] hover:underline">Forgot password?</a>
                        </div>
                        <button type="submit" class="mt-8 w-full rounded-full login-submit px-6 py-4 text-sm font-bold tracking-wide transition focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#6d5bff]">LOGIN</button>
                    </form>
                    <form x-cloak x-show="forgot" method="POST" action="{{ route('password.email') }}" class="mt-12 sm:mt-16">
                        @csrf
                        <label for="reset-email" class="text-sm font-medium text-[#2a2760]">Email address</label>
                        <div class="mt-2 border-b border-[#ece3d8] bg-[#fdf0e4] focus-within:bg-white focus-within:border-[#6d5bff]">
                            <input x-ref="resetEmail" id="reset-email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required class="w-full bg-transparent py-5 text-base text-[#16143a] outline-none placeholder:text-[#5b5a7a] focus-visible:outline-none">
                        </div>
                        <x-field-error name="email" />
                        <button type="submit" class="mt-8 w-full rounded-full login-submit px-6 py-4 text-sm font-bold tracking-wide transition focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#6d5bff]">Send reset link</button>
                        <div class="mt-6 text-center">
                            <a href="{{ route('login') }}" @click.prevent="forgot = false; $nextTick(() => document.getElementById('login-email').focus())" class="text-sm font-medium text-[#2a2760] hover:underline">← Back to login</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
@endsection
