<aside class="h-fit rounded-lg border border-black/10 bg-white p-3 shadow-sm">
    <div class="border-b border-black/10 px-3 pb-4 pt-2"><div class="flex items-center gap-3"><x-demo-profile-image class="h-11 w-11 border border-black/10" /><div class="min-w-0"><p class="truncate text-sm font-bold">{{ auth()->user()->name }}</p><p class="truncate text-xs text-neutral-500">{{ auth()->user()->email }}</p></div></div></div>
    <nav class="mt-3 space-y-1 text-sm font-semibold">
        @foreach ([['My Profile', 'profile.edit'], ['Account Security', 'profile.security'], ['Payment History', 'subscription.payment-history'], ['Membership', 'subscription.show']] as [$label, $route])
            <a href="{{ route($route) }}" class="block rounded-md px-3 py-2.5 {{ request()->routeIs($route) ? 'bg-neutral-950 text-white' : 'text-neutral-600 hover:bg-black/5 hover:text-neutral-950' }}">{{ $label }}</a>
        @endforeach
    </nav>
    <nav class="mt-5 border-t border-black/10 pt-4 text-sm font-semibold">
        <a href="{{ route('legal.show', 'terms') }}" class="block rounded-md px-3 py-2.5 text-neutral-600 hover:bg-black/5 hover:text-neutral-950">Terms &amp; Conditions</a>
        <a href="{{ route('legal.show', 'privacy') }}" class="block rounded-md px-3 py-2.5 text-neutral-600 hover:bg-black/5 hover:text-neutral-950">Privacy Policy</a>
        <a href="{{ route('legal.show', 'refund-policy') }}" class="block rounded-md px-3 py-2.5 text-neutral-600 hover:bg-black/5 hover:text-neutral-950">Refund Policy</a>
        <a href="{{ route('subscription.show') }}#cancel-subscription" class="mt-2 block rounded-md border-l-2 border-red-500 bg-red-50 px-3 py-2.5 text-red-700 hover:bg-red-100">Cancel Subscription</a>
    </nav>
</aside>
