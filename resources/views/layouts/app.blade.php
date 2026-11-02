<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ trim($__env->yieldContent('title', config('app.name', 'AI Resume Assistant'))) }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-cream text-ink antialiased">
        <a href="#main-content" class="sr-only z-50 rounded-lg bg-white px-4 py-3 font-semibold text-brand-700 focus:not-sr-only focus:fixed focus:left-4 focus:top-4">Skip to content</a>
        <div class="min-h-screen">
            <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 flex-col overflow-y-auto border-r border-line bg-white p-5 lg:flex">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-brand-600 shadow-lg shadow-brand-500/20 text-sm font-black text-white">AI</span>
                    <span>
                        <span class="block text-sm font-bold">Resume Assistant</span>
                        <span class="block text-xs text-muted">Career workspace</span>
                    </span>
                </a>

                <nav aria-label="Main navigation" class="mt-8 space-y-1.5 text-sm font-medium">
                    @foreach ([
                        ['Dashboard', 'dashboard', 'grid'],
                        ['My Resumes', 'resumes.index', 'document'],
                        ['Master Resume', 'resumes.master', 'layers'],
                        ['Job Applications', 'applications.index', 'briefcase'],
                        ['ATS Checker', 'ats.create', 'check'],
                        ['AI Tools', 'ai-tools.index', 'sparkles'],
                        ['Cover Letters', 'cover-letters.index', 'mail'],
                        ['Templates', 'templates.index', 'grid'],
                        // ['Subscription', 'subscription.show', 'layers'],
                    ] as [$label, $route, $icon])
                        <a href="{{ route($route) }}" aria-current="{{ request()->routeIs($route) ? 'page' : 'false' }}" class="flex items-center justify-between rounded-xl px-3 py-2.5 transition-colors {{ request()->routeIs($route) ? 'bg-brand-50 text-brand-700 ring-1 ring-brand-100' : 'text-muted hover:bg-slate-50 hover:text-ink' }}">
                            <span class="flex items-center gap-3"><x-workspace-icon :name="$icon" class="size-5" />{{ $label }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="mt-6 border-t border-line pt-5">
                    <p class="px-3 text-xs font-semibold uppercase tracking-[0.18em] text-muted">Account and legal</p>
                    <nav class="mt-2 space-y-1 text-sm font-medium">
                        @foreach ([
                            ['Terms & Conditions', 'terms'],
                            ['Privacy Policy', 'privacy'],
                            ['Refund Policy', 'refund-policy'],
                        ] as [$label, $page])
                            <a href="{{ route('legal.show', $page) }}" class="block rounded-lg px-3 py-2.5 {{ request()->routeIs('legal.show') && request()->route('page') === $page ? 'bg-brand-50 text-brand-700 ring-1 ring-brand-100' : 'text-muted hover:bg-slate-50 hover:text-ink' }}">{{ $label }}</a>
                        @endforeach
                        <a href="{{ route('subscription.show') }}#cancel-subscription" class="mt-2 block rounded-lg px-3 py-2.5 {{ request()->routeIs('subscription.show') ? 'bg-brand-50 text-brand-700 ring-1 ring-brand-100' : 'text-muted hover:bg-slate-50 hover:text-ink' }}">Cancel Subscription</a>
                    </nav>
                </div>

                <div class="mt-auto pt-8">
                    <div class="rounded-lg border border-line bg-cream p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-muted">Account</p>
                        <div class="mt-3 flex items-center gap-3">
                            <x-demo-profile-image :user="auth()->user()" class="h-11 w-11 border border-line" />
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-muted">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('profile.edit') }}" class="rounded-md border border-line px-3 py-2 text-xs font-semibold hover:bg-white">Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="rounded-md border border-line px-3 py-2 text-xs font-semibold hover:bg-white">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <main id="main-content" tabindex="-1" class="min-w-0 lg:ml-72">
                <header class="sticky top-0 z-20 border-b border-line bg-cream/90 px-4 py-3 backdrop-blur lg:hidden">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('dashboard') }}" class="font-bold">Resume Assistant</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="text-sm font-semibold text-neutral-600">Logout</button>
                        </form>
                    </div>
                    <details class="mt-3 rounded-xl border border-line bg-white">
                        <summary class="cursor-pointer px-4 py-2.5 text-sm font-semibold">Workspace navigation</summary>
                        <nav aria-label="Mobile workspace" class="grid grid-cols-2 gap-1 border-t border-slate-100 p-2 text-sm">
                            @foreach ([['Dashboard', 'dashboard'], ['My Resumes', 'resumes.index'], ['Master Resume', 'resumes.master'], ['Applications', 'applications.index'], ['ATS Checker', 'ats.create'], ['AI Tools', 'ai-tools.index'], ['Cover Letters', 'cover-letters.index'], ['Templates', 'templates.index'], ['Subscription', 'subscription.show'], ['Settings', 'profile.edit']] as [$label, $route])
                                <a href="{{ route($route) }}" class="rounded-lg px-3 py-2 {{ request()->routeIs($route) ? 'bg-brand-50 text-brand-700' : 'text-slate-600 hover:bg-slate-50' }}">{{ $label }}</a>
                            @endforeach
                        </nav>
                    </details>
                </header>

                @if (session('status'))
                    <div class="mx-auto mt-5 max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
        @livewireScriptConfig
    </body>
</html>
