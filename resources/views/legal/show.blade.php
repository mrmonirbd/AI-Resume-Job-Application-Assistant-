@extends(auth()->check() ? 'layouts.app' : 'layouts.guest')

@section('title', $policy['title'].' | '.config('app.name'))

@section('content')
    @php
        $brand = $concept?->company_name ?: $concept?->name ?: config('app.name');
        $supportEmail = $concept?->support_email;
        $currentPage = request()->route('page');
        $legalLinks = [
            ['Terms & Conditions', 'terms'],
            ['Privacy Policy', 'privacy'],
            ['Subscription Policy', 'subscription-policy'],
            ['Refund Policy', 'refund-policy'],
        ];
        $fallbackContent = [
            'terms' => ['These terms govern access to '.$brand.' and its resume, job application, and AI-assisted career tools.', [
                ['Using the service', 'Keep your account credentials secure and provide accurate career information. You are responsible for reviewing all application materials before sharing them with an employer.'],
                ['AI output', 'AI suggestions are assistance, not professional or legal advice. The service does not guarantee interview invitations, employment outcomes, or compatibility with a third-party hiring system.'],
                ['Account access', 'We may suspend access where required to protect the service, comply with law, or address misuse.'],
            ]],
            'privacy' => ['This policy explains how account and career data is used to provide the workspace.', [
                ['Information we process', 'We process account information, resume content, job postings, application records, and technical data needed to operate and secure the service.'],
                ['How information is used', 'Data is used to create requested documents, provide account support, improve service reliability, and meet legal obligations. We do not sell your resume content.'],
                ['Your choices', 'You can update your account data, export your documents, and request account deletion from account settings.'],
            ]],
            'subscription' => ['Subscription status, renewal, payment history, and cancellation are managed through the CRM linked to this concept.', [
                ['Renewal', 'Paid subscriptions renew according to the billing period shown in your subscription screen unless cancelled before the next renewal.'],
                ['Cancellation', 'You can request cancellation from your subscription screen. Cancellation prevents future renewal; access and end dates depend on the CRM billing record.'],
                ['Billing records', 'Invoice and subscription data displayed in this workspace is retrieved from the linked CRM account.'],
            ]],
            'refund' => ['Please read this policy before purchasing or cancelling a subscription.', [
                ['Refund requests', 'Refund eligibility is reviewed against the purchase date, applicable consumer law, and the terms of the linked CRM billing record.'],
                ['How to request a refund', 'Contact support with your account email and invoice reference so the billing team can review the request.'],
                ['Approved refunds', 'When a refund is approved, it is returned using the original payment method where possible. Processing times depend on the payment provider.'],
            ]],
        ][$page];
        $cancelUrl = auth()->check() ? route('subscription.show').'#cancel-subscription' : route('login');
    @endphp

    @if (auth()->guest())
        <header class="sticky top-0 z-20 border-b border-black/10 bg-white/95 px-5 py-4 backdrop-blur lg:hidden">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('landing') }}" class="flex items-center gap-3 font-bold">
                    <span class="grid h-9 w-9 place-items-center rounded-md bg-neutral-950 text-xs font-black text-white">AI</span>
                    <span>{{ $brand }}</span>
                </a>
                <a href="{{ route('login') }}" class="text-sm font-semibold text-neutral-600 hover:text-neutral-950">Log in</a>
            </div>
        </header>

        <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 flex-col overflow-y-auto border-r border-black/10 bg-white p-5 lg:flex">
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-neutral-950 text-sm font-black text-white">AI</span>
                <span>
                    <span class="block text-sm font-bold">{{ $brand }}</span>
                    <span class="block text-xs text-neutral-500">Career workspace</span>
                </span>
            </a>

            <nav class="mt-10 space-y-1 text-sm font-medium">
                @foreach ($legalLinks as [$label, $slug])
                    <a href="{{ route('legal.show', $slug) }}" class="block rounded-lg px-3 py-2.5 {{ $currentPage === $slug ? 'bg-neutral-950 text-white' : 'text-neutral-600 hover:bg-black/5 hover:text-neutral-950' }}">{{ $label }}</a>
                @endforeach
                <a href="{{ $cancelUrl }}" class="mt-3 block rounded-lg px-3 py-2.5 {{ request()->routeIs('subscription.show') ? 'bg-neutral-950 text-white' : 'text-neutral-600 hover:bg-black/5 hover:text-neutral-950' }}">Cancel Subscription</a>
            </nav>

            <div class="mt-auto pt-8">
                <a href="{{ route('login') }}" class="block rounded-md bg-neutral-950 px-4 py-2.5 text-center text-sm font-semibold text-white">Log in</a>
            </div>
        </aside>
    @endif

    <main class="min-h-screen px-5 py-8 sm:px-8 sm:py-12 {{ auth()->guest() ? 'lg:ml-72' : '' }}">
        <article class="mx-auto max-w-6xl rounded-lg border border-black/10 bg-white p-6 shadow-sm sm:p-10 lg:p-12">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Legal</p>
            <h1 class="mt-3 text-3xl font-black">{{ $policy['title'] }}</h1>

            @if ($policy['html'])
                <div class="mt-8 max-w-none text-base leading-7 text-neutral-700 [&_a]:font-semibold [&_a]:text-teal-700 [&_a]:underline [&_h1]:mt-10 [&_h1]:text-2xl [&_h1]:font-black [&_h2]:mt-10 [&_h2]:text-xl [&_h2]:font-black [&_h3]:mt-8 [&_h3]:text-lg [&_h3]:font-bold [&_li]:mt-2 [&_li]:ml-6 [&_ol]:mt-4 [&_ol]:list-decimal [&_p]:mt-4 [&_table]:mt-6 [&_table]:w-full [&_table]:border-collapse [&_td]:border [&_td]:border-black/10 [&_td]:p-3 [&_th]:border [&_th]:border-black/10 [&_th]:bg-neutral-50 [&_th]:p-3 [&_th]:text-left [&_ul]:mt-4 [&_ul]:list-disc">
                    {!! $policy['html'] !!}
                </div>
            @else
                <p class="mt-4 text-base leading-7 text-neutral-600">{{ $fallbackContent[0] }}</p>
                <div class="mt-10 space-y-8">
                    @foreach ($fallbackContent[1] as [$heading, $body])
                        <section>
                            <h2 class="text-lg font-black">{{ $heading }}</h2>
                            <p class="mt-2 text-sm leading-7 text-neutral-600">{{ $body }}</p>
                        </section>
                    @endforeach
                </div>
            @endif

            <div class="mt-10 border-t border-black/10 pt-6 text-sm text-neutral-600">
                @if ($supportEmail)
                    Questions about this policy? <a class="font-semibold text-teal-700" href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
                @else
                    Questions about this policy? Contact the support team for your subscription.
                @endif
            </div>
        </article>
    </main>
@endsection
