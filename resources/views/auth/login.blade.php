@extends('layouts.guest')

@section('content')
    <main class="grid min-h-dvh bg-white lg:grid-cols-[47%_53%]">
        <aside class="relative hidden min-h-dvh overflow-hidden login-gradient-panel text-white lg:flex lg:flex-col lg:justify-center lg:px-12 xl:px-20" aria-label="Your career starts here">
            <div class="login-gradient-grid pointer-events-none absolute inset-0" aria-hidden="true"></div>
            <div class="relative mx-auto w-full max-w-lg py-12">
                <p class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.2em] text-brand-200"><span class="h-px w-8 bg-brand-400"></span> Your next chapter</p>
                <h2 class="mt-6 text-4xl font-semibold leading-tight tracking-tight xl:text-5xl">Great opportunities<br>start with <span class="login-gradient-text">your story.</span></h2>
                <p class="mt-5 max-w-sm text-sm leading-7 text-brand-200">Turn your experience into a resume that opens doors. Keep every application moving forward.</p>

                <div class="my-8 flex justify-center xl:my-10">
                    <img src="{{ asset('images/login-career.png') }}" alt="Career illustration showing candidates and professional resumes" width="758" height="834" class="h-auto max-h-[46vh] w-full object-contain" fetchpriority="high">
                </div>
                <div class="flex items-center gap-3 border-t border-white/10 pt-6">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-white/10 text-brand-200"><svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 8 4v5c0 5-8 9-8 9s-8-4-8-9V7l8-4Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m8 12 3 3 5-6"/></svg></span>
                    <p class="text-xs leading-5 text-brand-200">Your experience. Your ambition.<br><span class="text-white">One workspace for your next move.</span></p>
                </div>
            </div>
        </aside>

        <section class="relative flex min-h-dvh flex-col px-6 sm:px-12 lg:px-16">
            <header class="flex justify-end py-7 sm:py-9">
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2.5 text-sm font-bold text-ink" aria-label="CVGenio login">
                    <img src="{{ asset('images/cvgenio-logo.png') }}" alt="CVGenio" width="900" height="274" class="h-auto w-40">
                </a>
            </header>
            <div class="flex flex-1 items-center justify-center pb-16 pt-10 sm:pb-24">
                <div class="w-full max-w-[400px]" x-data="{ forgot: {{ request()->routeIs('password.request') || session('auth_form') === 'forgot' ? 'true' : 'false' }} }">
                    <h1 class="text-4xl font-bold tracking-tight text-slate-800 sm:text-[42px]" x-text="forgot ? 'RESET PASSWORD' : 'LOGIN'">LOGIN</h1>
                    <p class="mt-3 text-base leading-6 text-slate-500" x-text="forgot ? 'Enter your email address and we will send you a password reset link.' : 'Sign in with your email address to access your account'">Sign in with your email address to access your account</p>

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
                            <div class="flex items-center gap-4 border-b border-slate-200 px-3 focus-within:border-brand-600">
                                <svg class="size-5 shrink-0 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4 21v-2a8 8 0 0 1 16 0v2"/></svg>
                                <input id="login-email" type="email" name="email" value="{{ old('email') }}" placeholder="Email address" autocomplete="username" required autofocus @error('email') aria-invalid="true" aria-describedby="login-email-error" @enderror class="min-w-0 flex-1 border-0 bg-transparent py-5 text-base text-slate-900 outline-none placeholder:text-slate-400 focus-visible:outline-none">
                            </div>
                            <div id="login-email-error"><x-field-error name="email" /></div>
                        </div>
                        <div class="mt-6">
                            <label for="login-password" class="sr-only">Password</label>
                            <div class="flex items-center gap-4 border-b border-slate-200 px-3 focus-within:border-brand-600">
                                <svg class="size-5 shrink-0 text-brand-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/><path stroke-linecap="round" d="M12 14v3"/></svg>
                                <input id="login-password" type="password" name="password" placeholder="Password" autocomplete="current-password" required @error('password') aria-invalid="true" aria-describedby="login-password-error" @enderror class="min-w-0 flex-1 border-0 bg-transparent py-5 text-base text-slate-900 outline-none placeholder:text-slate-400 focus-visible:outline-none">
                            </div>
                            <div id="login-password-error"><x-field-error name="password" /></div>
                        </div>
                        <div class="mt-7 flex flex-wrap items-center justify-between gap-3 text-sm">
                            <label class="flex cursor-pointer items-center gap-2 text-slate-500">
                                <input type="checkbox" name="remember" @checked(old('remember')) class="size-4 accent-brand-700">
                                Remember me
                            </label>
                            <a @click.prevent="forgot = true; $nextTick(() => $refs.resetEmail.focus())" href="{{ route('password.request') }}" class="font-medium text-brand-800 hover:underline">Forgot password?</a>
                        </div>
                        <button type="submit" class="mt-8 w-full rounded-full bg-brand-700 px-6 py-4 text-sm font-bold tracking-wide text-white transition hover:bg-brand-800 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-600">LOGIN</button>
                    </form>
                    <form x-cloak x-show="forgot" method="POST" action="{{ route('password.email') }}" class="mt-12 sm:mt-16">
                        @csrf
                        <label for="reset-email" class="text-sm font-medium text-slate-600">Email address</label>
                        <div class="mt-2 border-b border-slate-200 focus-within:border-brand-600">
                            <input x-ref="resetEmail" id="reset-email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required class="w-full bg-transparent py-5 text-base text-slate-900 outline-none placeholder:text-slate-400 focus-visible:outline-none">
                        </div>
                        <x-field-error name="email" />
                        <button type="submit" class="mt-8 w-full rounded-full bg-brand-700 px-6 py-4 text-sm font-bold tracking-wide text-white transition hover:bg-brand-800">Send reset link</button>
                        <div class="mt-6 text-center">
                            <a href="{{ route('login') }}" @click.prevent="forgot = false; $nextTick(() => document.getElementById('login-email').focus())" class="text-sm font-medium text-brand-800 hover:underline">← Back to login</a>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>
@endsection
