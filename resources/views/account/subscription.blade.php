@extends('layouts.app')

@section('title', 'Membership')

@section('content')
    @php
        $data = $remoteSubscription ?? [];
        $field = fn (string $key, mixed $fallback = 'Not available') => data_get($data, $key, $fallback);
        $status = $field('status', $subscription?->status ?? 'not linked');
        $price = trim((string) $field('price', data_get($data, 'amount', '')));
        $currency = (string) $field('currency', '');
    @endphp
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div><p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Settings</p><h1 class="mt-2 text-3xl font-black">Membership</h1><p class="mt-2 text-sm text-neutral-600">Subscription information is read from the CRM customer record.</p></div>
        @if ($crmUnavailable)<div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">CRM data is temporarily unavailable. Last synced data is shown where available.</div>@endif
        <div class="mt-8 grid gap-6 lg:grid-cols-[15rem_minmax(0,1fr)]">
            @include('account.partials.settings-sidebar')
            <section class="rounded-lg border border-black/10 bg-white shadow-sm">
                <div class="border-b border-black/10 px-6 py-5"><h2 class="text-lg font-black">My subscription</h2></div>
                <div class="p-6">
                    @error('subscription')<p class="mb-5 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ([['Subscription number', $field('id', $user->crm_subscription_id ?? 'Not linked')], ['Name', $field('name', $subscription?->plan?->name ?? 'CRM plan')], ['Type', $field('type')], ['Status', $status], ['Price', trim($price.' '.$currency) ?: 'Not available'], ['Interval', ($interval = $field('interval', '')) !== '' ? $interval.' days' : 'Not available']] as [$label, $value])
                            <label class="block"><span class="text-sm font-semibold">{{ $label }}</span><input value="{{ $value }}" disabled class="mt-2 w-full rounded-md border border-black/10 bg-neutral-50 px-4 py-3 text-sm text-neutral-600"></label>
                        @endforeach
                    </div>
                    @if (filled($user->crm_subscription_id) && strtolower((string) $status) === 'active')
                        <form id="cancel-subscription" method="POST" action="{{ route('subscription.cancel') }}" class="mt-8 border-t border-black/10 pt-6" onsubmit="return confirm('Cancel this subscription through the CRM?')">
                            @csrf
                            <input type="hidden" name="confirmation" value="cancel">
                            <p class="text-sm leading-6 text-neutral-600">Cancelling is sent directly to the CRM and stops future renewal under the CRM billing terms.</p>
                            <button class="mt-4 rounded-md bg-neutral-950 px-5 py-3 text-sm font-black text-white">Cancel subscription</button>
                        </form>
                    @endif
                </div>
            </section>
        </div>
    </div>
@endsection
