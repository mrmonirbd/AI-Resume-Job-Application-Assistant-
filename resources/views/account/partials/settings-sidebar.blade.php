<aside class="h-fit rounded-lg border border-black/10 bg-white p-3 shadow-sm">
    <div class="border-b border-black/10 px-3 pb-4 pt-2"><div class="flex items-center gap-3"><x-demo-profile-image :user="auth()->user()" class="h-11 w-11 border border-black/10" /><div class="min-w-0"><p class="truncate text-sm font-bold">{{ auth()->user()->name }}</p><p class="truncate text-xs text-neutral-500">{{ auth()->user()->email }}</p></div></div></div>
    <nav class="mt-3 space-y-1 text-sm font-semibold">
        @foreach ([['My Profile', 'profile.edit'], ['Account Security', 'profile.security'], ['Payment History', 'subscription.payment-history'], ['Membership', 'subscription.show']] as [$label, $route])
            <a href="{{ route($route) }}" class="block rounded-md px-3 py-2.5 {{ request()->routeIs($route) ? 'bg-neutral-950 text-white' : 'text-neutral-600 hover:bg-black/5 hover:text-neutral-950' }}">{{ $label }}</a>
        @endforeach
    </nav>
</aside>
