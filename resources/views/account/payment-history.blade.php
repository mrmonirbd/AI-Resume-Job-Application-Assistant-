@extends('layouts.app')

@section('title', 'Payment history')

@section('content')
    @php($orderRows = is_array($invoices) && array_is_list($invoices) ? $invoices : [])
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div><p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Settings</p><h1 class="mt-2 text-3xl font-black">Payment history</h1><p class="mt-2 text-sm text-neutral-600">Orders and invoice totals returned by the CRM.</p></div>
        @if ($crmUnavailable)<div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">CRM payment history is temporarily unavailable.</div>@endif
        <div class="mt-8 grid gap-6 lg:grid-cols-[15rem_minmax(0,1fr)]">
            @include('account.partials.settings-sidebar')
            <section class="overflow-hidden rounded-lg border border-black/10 bg-white shadow-sm">
                <div class="border-b border-black/10 px-6 py-5"><h2 class="text-lg font-black">Payment history</h2></div>
                <div class="overflow-x-auto"><table class="w-full min-w-[48rem] text-left text-sm"><thead class="bg-neutral-50 text-xs uppercase tracking-[0.12em] text-neutral-500"><tr><th class="px-5 py-4">Order ID</th><th class="px-5 py-4">Product</th><th class="px-5 py-4">Type</th><th class="px-5 py-4">Total</th><th class="px-5 py-4">Status</th><th class="px-5 py-4">Created at</th></tr></thead><tbody class="divide-y divide-black/10">
                    @forelse ($orderRows as $order)
                        <tr><td class="px-5 py-4 font-semibold">{{ data_get($order, 'id', '--') }}</td><td class="px-5 py-4">{{ data_get($order, 'website.name', data_get($order, 'product.name', '--')) }}</td><td class="px-5 py-4">{{ data_get($order, 'type', '--') }}</td><td class="px-5 py-4">{{ trim((string) data_get($order, 'total', '--').' '.data_get($order, 'currency', '')) }}</td><td class="px-5 py-4">{{ data_get($order, 'status', '--') }}</td><td class="px-5 py-4">{{ data_get($order, 'created_at', '--') }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-neutral-500">No orders found.</td></tr>
                    @endforelse
                </tbody></table></div>
            </section>
        </div>
    </div>
@endsection
