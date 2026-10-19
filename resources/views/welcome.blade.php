@extends('layouts.guest')

@section('content')
    <header class="absolute inset-x-0 top-0 z-20">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-5 lg:px-8">
            <a href="{{ route('landing') }}" class="flex items-center gap-3 text-white">
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-white text-sm font-black text-neutral-950">AI</span>
                <span class="text-sm font-bold">Resume Assistant</span>
            </a>
            <nav class="hidden items-center gap-6 text-sm font-semibold text-white/80 md:flex">
                <a href="#templates" class="hover:text-white">Templates</a>
                <a href="#features" class="hover:text-white">Features</a>
                <a href="#pricing" class="hover:text-white">Pricing</a>
                <a href="#faq" class="hover:text-white">FAQ</a>
            </nav>
            <div class="flex items-center gap-3">
                <a href="{{ route('login') }}" class="hidden text-sm font-semibold text-white/85 hover:text-white sm:inline">Log in</a>
                <a href="{{ route('login') }}" class="rounded-lg bg-white px-4 py-2 text-sm font-bold text-neutral-950 shadow-sm hover:bg-neutral-100">Open Workspace</a>
            </div>
        </div>
    </header>

    <section class="relative min-h-[92vh] overflow-hidden bg-neutral-950">
        <img src="{{ asset('images/hero-workspace.png') }}" alt="AI resume workspace shown on desktop and laptop screens" class="absolute inset-0 h-full w-full object-cover">
        <div class="absolute inset-0 bg-[linear-gradient(90deg,rgba(10,10,10,0.86)_0%,rgba(10,10,10,0.68)_34%,rgba(10,10,10,0.16)_78%)]"></div>
        <div class="relative z-10 mx-auto flex min-h-[92vh] max-w-7xl items-center px-5 pb-20 pt-28 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-teal-200">AI career workspace</p>
                <h1 class="mt-5 max-w-xl text-5xl font-black leading-[1.02] text-white sm:text-6xl lg:text-7xl">Build a Resume That Fits the Job</h1>
                <p class="mt-6 max-w-xl text-lg leading-8 text-white/78">Create professional resumes, analyze job descriptions, tailor applications with AI, and manage your job search from one workspace.</p>
                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('login') }}" class="rounded-lg bg-white px-6 py-3 text-center text-sm font-black text-neutral-950 shadow-xl shadow-black/20 hover:bg-neutral-100">Open Demo Workspace</a>
                    <a href="{{ route('ats.create') }}" class="rounded-lg border border-white/35 px-6 py-3 text-center text-sm font-black text-white backdrop-blur hover:bg-white/10">Check My Resume</a>
                </div>
                <div class="mt-10 grid max-w-xl grid-cols-3 gap-3 text-white">
                    <div class="border-l border-white/25 pl-4">
                        <p class="text-2xl font-black">82%</p>
                        <p class="text-xs text-white/65">match clarity</p>
                    </div>
                    <div class="border-l border-white/25 pl-4">
                        <p class="text-2xl font-black">10</p>
                        <p class="text-xs text-white/65">ATS templates</p>
                    </div>
                    <div class="border-l border-white/25 pl-4">
                        <p class="text-2xl font-black">1</p>
                        <p class="text-xs text-white/65">source of truth</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <main class="bg-[#f6f7f9]">
        <section id="templates" class="mx-auto -mt-10 max-w-7xl px-5 pb-20 lg:px-8">
            <div class="grid gap-4 md:grid-cols-3">
                @foreach (['Executive Ledger', 'Modern Signal', 'Minimal Line'] as $template)
                    <div class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                        <div class="h-64 rounded-md border border-black/10 bg-white p-5">
                            <div class="h-5 w-32 rounded bg-neutral-900"></div>
                            <div class="mt-3 h-2 w-44 rounded bg-neutral-200"></div>
                            <div class="mt-8 grid grid-cols-[1fr_2fr] gap-4">
                                <div class="space-y-2">
                                    <div class="h-3 rounded bg-teal-600"></div>
                                    <div class="h-2 rounded bg-neutral-200"></div>
                                    <div class="h-2 rounded bg-neutral-200"></div>
                                    <div class="h-2 rounded bg-neutral-200"></div>
                                </div>
                                <div class="space-y-3">
                                    <div class="h-3 w-20 rounded bg-neutral-900"></div>
                                    <div class="h-2 rounded bg-neutral-200"></div>
                                    <div class="h-2 rounded bg-neutral-200"></div>
                                    <div class="h-2 w-2/3 rounded bg-neutral-200"></div>
                                    <div class="h-3 w-24 rounded bg-neutral-900"></div>
                                    <div class="h-2 rounded bg-neutral-200"></div>
                                    <div class="h-2 w-3/4 rounded bg-neutral-200"></div>
                                </div>
                            </div>
                        </div>
                        <p class="mt-4 text-sm font-bold">{{ $template }}</p>
                        <p class="mt-1 text-sm text-neutral-500">ATS-friendly structure with distinct hierarchy and spacing.</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="border-y border-black/10 bg-white py-20">
            <div class="mx-auto max-w-7xl px-5 lg:px-8">
                <div class="max-w-2xl">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">How It Works</p>
                    <h2 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Paste a job. Understand the gap. Build the application.</h2>
                </div>
                <div class="mt-10 grid gap-4 md:grid-cols-4">
                    @foreach (['Create a master resume', 'Analyze a job description', 'Compare match and gaps', 'Generate documents and track'] as $step)
                        <div class="rounded-lg border border-black/10 p-5">
                            <span class="grid h-9 w-9 place-items-center rounded-md bg-neutral-950 text-sm font-black text-white">{{ $loop->iteration }}</span>
                            <p class="mt-5 text-base font-bold">{{ $step }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="features" class="mx-auto max-w-7xl px-5 py-20 lg:px-8">
            <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['AI Resume Builder', 'Turn verified career history into structured, job-ready resume versions.'],
                    ['Job Match Analysis', 'See score breakdowns for skills, experience, education, and keyword coverage.'],
                    ['ATS Resume Checker', 'Review structure, readability, parsing risk, and target keyword coverage.'],
                    ['AI Cover Letter', 'Generate factual letters from saved resume and job data with tone controls.'],
                    ['Application Tracking', 'Move jobs through Saved, Preparing, Applied, Interview, Offer, and more.'],
                    ['AI Features', 'Add more career tools through a service-based provider architecture.'],
                ] as [$title, $copy])
                    <div class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                        <div class="h-1.5 w-14 rounded-full bg-teal-600"></div>
                        <h3 class="mt-5 text-lg font-black">{{ $title }}</h3>
                        <p class="mt-3 text-sm leading-6 text-neutral-600">{{ $copy }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="bg-neutral-950 py-20 text-white">
            <div class="mx-auto grid max-w-7xl gap-10 px-5 lg:grid-cols-[0.9fr_1.1fr] lg:px-8">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-rose-200">Built for factual tailoring</p>
                    <h2 class="mt-3 text-3xl font-black sm:text-4xl">AI can improve your wording. It cannot invent your career.</h2>
                    <p class="mt-5 text-white/70">Every tailored resume is generated as a new version from the master resume, with missing skills and recommendations kept separate from verified information.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-lg border border-white/10 bg-white/5 p-5">
                        <p class="text-sm font-bold">Verified User Information</p>
                        <p class="mt-3 text-sm text-white/65">Experience, education, skills, projects, dates, and achievements saved by the user.</p>
                    </div>
                    <div class="rounded-lg border border-white/10 bg-white/5 p-5">
                        <p class="text-sm font-bold">AI Recommendations</p>
                        <p class="mt-3 text-sm text-white/65">Missing skills and improvement ideas shown as suggestions, never silently inserted.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-7xl px-5 py-20 lg:px-8">
            <div class="grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['The match breakdown made my resume edits obvious instead of stressful.', 'Product manager'],
                    ['I finally had one place for resumes, letters, and application status.', 'Backend engineer'],
                    ['The factuality guardrails are exactly what job-search AI needs.', 'Career coach'],
                ] as [$quote, $role])
                    <figure class="rounded-lg border border-black/10 bg-white p-6 shadow-sm">
                        <blockquote class="text-lg font-semibold leading-8">"{{ $quote }}"</blockquote>
                        <figcaption class="mt-5 text-sm text-neutral-500">{{ $role }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </section>

        <section id="pricing" class="border-y border-black/10 bg-white py-20">
            <div class="mx-auto grid max-w-7xl gap-5 px-5 md:grid-cols-2 lg:px-8">
                <div class="rounded-lg border border-black/10 p-7">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-neutral-500">Free</p>
                    <p class="mt-4 text-4xl font-black">$0</p>
                    <p class="mt-3 text-neutral-600">Master resume, basic templates, ATS checks, limited AI generations.</p>
                </div>
                <div class="rounded-lg border border-neutral-950 bg-neutral-950 p-7 text-white">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-200">Pro</p>
                    <p class="mt-4 text-4xl font-black">$19</p>
                    <p class="mt-3 text-white/70">Unlimited versions, premium templates, tailoring, cover letters, and higher AI limits.</p>
                </div>
            </div>
        </section>

        <section id="faq" class="mx-auto max-w-4xl px-5 py-20">
            <h2 class="text-3xl font-black">FAQ</h2>
            <div class="mt-8 divide-y divide-black/10 rounded-lg border border-black/10 bg-white">
                @foreach ([
                    ['Will AI invent my experience?', 'No. The workflow separates verified information from recommendations and asks you to approve meaningful changes.'],
                    ['Is the match score an official ATS score?', 'No. It is an internal compatibility estimate to help you improve your application.'],
                    ['Can I keep multiple resume versions?', 'Yes. The master resume stays intact while tailored versions are stored separately.'],
                ] as [$question, $answer])
                    <div class="p-5">
                        <p class="font-bold">{{ $question }}</p>
                        <p class="mt-2 text-sm leading-6 text-neutral-600">{{ $answer }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="px-5 pb-20">
            <div class="mx-auto max-w-7xl rounded-lg bg-neutral-950 p-8 text-white md:p-12">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-3xl font-black">Ready to build your next application?</h2>
                        <p class="mt-3 text-white/70">Start with your master career profile and tailor from there.</p>
                    </div>
                    <a href="{{ route('login') }}" class="rounded-lg bg-white px-6 py-3 text-center text-sm font-black text-neutral-950">Open Workspace</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-black/10 bg-white px-5 py-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 text-sm text-neutral-500 md:flex-row md:items-center md:justify-between">
            <p>&copy; {{ now()->year }} AI Resume & Job Application Assistant</p>
            <div class="flex flex-wrap gap-x-5 gap-y-2">
                <a href="{{ route('legal.show', 'terms') }}" class="hover:text-neutral-950">Terms</a>
                <a href="{{ route('legal.show', 'privacy') }}" class="hover:text-neutral-950">Privacy</a>
                <a href="{{ route('legal.show', 'subscription-policy') }}" class="hover:text-neutral-950">Subscription policy</a>
                <a href="{{ route('legal.show', 'refund-policy') }}" class="hover:text-neutral-950">Refund policy</a>
            </div>
        </div>
    </footer>
@endsection
