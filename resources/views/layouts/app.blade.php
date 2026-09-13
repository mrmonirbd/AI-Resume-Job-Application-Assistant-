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
    <body class="min-h-screen bg-[#f6f7f9] text-neutral-950 antialiased">
        <div class="flex min-h-screen">
            <aside class="hidden w-72 shrink-0 border-r border-black/10 bg-white/80 p-5 lg:block">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-neutral-950 text-sm font-black text-white">AI</span>
                    <span>
                        <span class="block text-sm font-bold">Resume Assistant</span>
                        <span class="block text-xs text-neutral-500">Career workspace</span>
                    </span>
                </a>

                <nav class="mt-8 space-y-1 text-sm font-medium">
                    @foreach ([
                        ['Dashboard', 'dashboard'],
                        ['My Resumes', 'resumes.index'],
                        ['Master Resume', 'resumes.master'],
                        ['Job Applications', 'applications.index'],
                        ['ATS Checker', 'ats.create'],
                        ['AI Tools', 'ai-tools.index'],
                        ['Cover Letters', 'cover-letters.index'],
                        ['Templates', 'templates.index'],
                    ] as [$label, $route])
                        <a href="{{ route($route) }}" class="flex items-center justify-between rounded-lg px-3 py-2.5 {{ request()->routeIs($route) ? 'bg-neutral-950 text-white' : 'text-neutral-600 hover:bg-black/5 hover:text-neutral-950' }}">
                            <span>{{ $label }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="mt-auto pt-8">
                    <div class="rounded-lg border border-black/10 bg-[#f6f7f9] p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-neutral-500">Account</p>
                        <div class="mt-3 flex items-center gap-3">
                            <x-demo-profile-image class="h-11 w-11 border border-black/10" />
                            <div class="min-w-0">
                                <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs text-neutral-500">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('profile.edit') }}" class="rounded-md border border-black/10 px-3 py-2 text-xs font-semibold hover:bg-white">Settings</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="rounded-md border border-black/10 px-3 py-2 text-xs font-semibold hover:bg-white">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="min-w-0 flex-1">
                <header class="sticky top-0 z-20 border-b border-black/10 bg-[#f6f7f9]/90 px-4 py-3 backdrop-blur lg:hidden">
                    <div class="flex items-center justify-between">
                        <a href="{{ route('dashboard') }}" class="font-bold">Resume Assistant</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="text-sm font-semibold text-neutral-600">Logout</button>
                        </form>
                    </div>
                    <nav class="mt-3 flex gap-2 overflow-x-auto text-xs font-semibold">
                        <a href="{{ route('dashboard') }}" class="whitespace-nowrap rounded-full bg-white px-3 py-1.5">Dashboard</a>
                        <a href="{{ route('resumes.index') }}" class="whitespace-nowrap rounded-full bg-white px-3 py-1.5">Resumes</a>
                        <a href="{{ route('applications.index') }}" class="whitespace-nowrap rounded-full bg-white px-3 py-1.5">Applications</a>
                        <a href="{{ route('ats.create') }}" class="whitespace-nowrap rounded-full bg-white px-3 py-1.5">ATS</a>
                    </nav>
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
        @livewireScripts
    </body>
</html>
