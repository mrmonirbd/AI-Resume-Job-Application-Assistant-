@extends('layouts.guest')

@section('content')
    <div class="flex min-h-screen items-center justify-center px-5 py-12">
        <div class="w-full max-w-lg rounded-lg border border-black/10 bg-white p-8 text-center shadow-sm">
            <span class="mx-auto grid h-12 w-12 place-items-center rounded-lg bg-neutral-950 text-sm font-black text-white">AI</span>
            <h1 class="mt-6 text-2xl font-black">Verify your email</h1>
            <p class="mt-3 text-sm leading-6 text-neutral-600">We sent a verification link to your inbox. Once verified, you can finish onboarding and create your master resume workspace.</p>
            @if (session('status') === 'verification-link-sent')
                <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800">A new verification link has been sent.</div>
            @endif
            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button class="rounded-lg bg-neutral-950 px-5 py-3 text-sm font-black text-white">Resend email</button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg border border-black/10 px-5 py-3 text-sm font-bold">Log out</button>
                </form>
            </div>
        </div>
    </div>
@endsection
