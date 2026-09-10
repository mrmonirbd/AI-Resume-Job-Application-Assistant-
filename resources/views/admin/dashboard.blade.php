@extends('layouts.app')

@section('title', 'Admin')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Admin</p>
            <h1 class="mt-2 text-3xl font-black">System overview</h1>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $label => $value)
                <div class="rounded-lg border border-black/10 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-neutral-500">{{ str($label)->headline() }}</p>
                    <p class="mt-3 text-3xl font-black">{{ is_numeric($value) ? number_format($value, is_float($value) ? 2 : 0) : $value }}</p>
                </div>
            @endforeach
        </div>

        <section class="mt-8 rounded-lg border border-black/10 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-black">Recent AI usage</h2>
            <div class="mt-5 overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="border-b border-black/10 text-xs uppercase tracking-[0.14em] text-neutral-500">
                        <tr>
                            <th class="py-3">Feature</th>
                            <th class="py-3">Provider</th>
                            <th class="py-3">Status</th>
                            <th class="py-3">Tokens</th>
                            <th class="py-3">Completed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-black/10">
                        @foreach ($recentGenerations as $generation)
                            <tr>
                                <td class="py-3 font-semibold">{{ str($generation->feature)->headline() }}</td>
                                <td class="py-3">{{ $generation->provider }}</td>
                                <td class="py-3">{{ $generation->status }}</td>
                                <td class="py-3">{{ $generation->total_tokens }}</td>
                                <td class="py-3">{{ $generation->completed_at?->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
