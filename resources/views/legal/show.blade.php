@extends('layouts.guest')

@section('title', ucfirst($page).' policy')

@section('content')
    @php
        $brand = $concept?->company_name ?: $concept?->name ?: config('app.name');
        $supportEmail = $concept?->support_email;
        $content = [
            'terms' => ['Terms of service', 'These terms govern access to '.$brand.' and its resume, job application, and AI-assisted career tools.', [
                ['Using the service', 'Keep your account credentials secure and provide accurate career information. You are responsible for reviewing all application materials before sharing them with an employer.'],
                ['AI output', 'AI suggestions are assistance, not professional or legal advice. The service does not guarantee interview invitations, employment outcomes, or compatibility with a third-party hiring system.'],
                ['Account access', 'We may suspend access where required to protect the service, comply with law, or address misuse.'],
            ]],
            'privacy' => ['Privacy policy', 'This policy explains how account and career data is used to provide the workspace.', [
                ['Information we process', 'We process account information, resume content, job postings, application records, and technical data needed to operate and secure the service.'],
                ['How information is used', 'Data is used to create requested documents, provide account support, improve service reliability, and meet legal obligations. We do not sell your resume content.'],
                ['Your choices', 'You can update your account data, export your documents, and request account deletion from account settings.'],
            ]],
            'subscription' => ['Subscription policy', 'Subscription status, renewal, payment history, and cancellation are managed through the CRM linked to this concept.', [
                ['Renewal', 'Paid subscriptions renew according to the billing period shown in your subscription screen unless cancelled before the next renewal.'],
                ['Cancellation', 'You can request cancellation from your subscription screen. Cancellation prevents future renewal; access and end dates depend on the CRM billing record.'],
                ['Billing records', 'Invoice and subscription data displayed in this workspace is retrieved from the linked CRM account.'],
            ]],
            'refund' => ['Refund policy', 'Please read this policy before purchasing or cancelling a subscription.', [
                ['Refund requests', 'Refund eligibility is reviewed against the purchase date, applicable consumer law, and the terms of the linked CRM billing record.'],
                ['How to request a refund', 'Contact support with your account email and invoice reference so the billing team can review the request.'],
                ['Approved refunds', 'When a refund is approved, it is returned using the original payment method where possible. Processing times depend on the payment provider.'],
            ]],
        ][$page];
    @endphp

    <main class="min-h-screen bg-[#f6f7f9] px-5 py-10 sm:py-16">
        <div class="mx-auto max-w-3xl">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('landing') }}" class="flex items-center gap-3 font-bold"><span class="grid h-9 w-9 place-items-center rounded-md bg-neutral-950 text-xs font-black text-white">AI</span>{{ $brand }}</a>
                <a href="{{ route('login') }}" class="text-sm font-semibold text-neutral-600 hover:text-neutral-950">Log in</a>
            </div>
            <article class="mt-10 rounded-lg border border-black/10 bg-white p-6 shadow-sm sm:p-10">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Legal</p>
                <h1 class="mt-3 text-3xl font-black">{{ $content[0] }}</h1>
                <p class="mt-4 text-base leading-7 text-neutral-600">{{ $content[1] }}</p>
                <div class="mt-10 space-y-8">
                    @foreach ($content[2] as [$heading, $body])
                        <section><h2 class="text-lg font-black">{{ $heading }}</h2><p class="mt-2 text-sm leading-7 text-neutral-600">{{ $body }}</p></section>
                    @endforeach
                </div>
                <div class="mt-10 border-t border-black/10 pt-6 text-sm text-neutral-600">
                    @if ($supportEmail)
                        Questions about this policy? <a class="font-semibold text-teal-700" href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
                    @else
                        Questions about this policy? Contact the support team for your subscription.
                    @endif
                </div>
            </article>
        </div>
    </main>
@endsection
