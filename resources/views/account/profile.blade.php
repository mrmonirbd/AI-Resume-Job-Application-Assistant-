@extends('layouts.app')

@section('title', 'Account details')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-teal-700">Settings</p>
            <h1 class="mt-2 text-3xl font-black">Account details</h1>
            <p class="mt-2 text-sm text-neutral-600">Customer details are loaded from the CRM linked to this concept.</p>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[15rem_minmax(0,1fr)]">
            @include('account.partials.settings-sidebar')

            <section class="rounded-lg border border-black/10 bg-white shadow-sm">
                <div class="border-b border-black/10 px-6 py-5">
                    <h2 class="text-lg font-black">My profile</h2>
                    <p class="mt-1 text-sm text-neutral-500">The same contact fields stored in your CRM customer record.</p>
                </div>
                <form method="POST" action="{{ route('profile.update') }}" class="p-6">
                    @csrf
                    @method('PATCH')
                    @error('profile')<p class="mb-5 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ([['First name', 'first_name', 50], ['Last name', 'last_name', 50]] as [$label, $field, $max])
                            <label class="block"><span class="text-sm font-semibold">{{ $label }}</span><input name="{{ $field }}" value="{{ old($field, data_get($profile, $field)) }}" required maxlength="{{ $max }}" class="mt-2 w-full rounded-md border border-black/10 px-4 py-3 text-sm"><x-field-error name="{{ $field }}" /></label>
                        @endforeach
                    </div>
                    <label class="mt-4 block"><span class="text-sm font-semibold">Phone</span><input name="phone" value="{{ old('phone', data_get($profile, 'phone')) }}" required maxlength="50" class="mt-2 w-full rounded-md border border-black/10 px-4 py-3 text-sm"><x-field-error name="phone" /></label>
                    <label class="mt-4 block"><span class="text-sm font-semibold">Address</span><input name="address" value="{{ old('address', data_get($profile, 'address')) }}" required maxlength="255" class="mt-2 w-full rounded-md border border-black/10 px-4 py-3 text-sm"><x-field-error name="address" /></label>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="block"><span class="text-sm font-semibold">City</span><input name="city" value="{{ old('city', data_get($profile, 'city')) }}" required maxlength="150" class="mt-2 w-full rounded-md border border-black/10 px-4 py-3 text-sm"><x-field-error name="city" /></label>
                        <label class="block"><span class="text-sm font-semibold">Postcode</span><input name="postcode" value="{{ old('postcode', data_get($profile, 'postcode')) }}" required maxlength="50" class="mt-2 w-full rounded-md border border-black/10 px-4 py-3 text-sm"><x-field-error name="postcode" /></label>
                    </div>
                    <div class="mt-8 border-t border-black/10 pt-6">
                        <p class="text-sm font-black">Career workspace</p>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <label class="block"><span class="text-sm font-semibold">Professional title</span><input name="professional_title" value="{{ old('professional_title', $user->professional_title) }}" class="mt-2 w-full rounded-md border border-black/10 px-4 py-3 text-sm"></label>
                            <label class="block"><span class="text-sm font-semibold">Location</span><input name="location" value="{{ old('location', $user->location) }}" class="mt-2 w-full rounded-md border border-black/10 px-4 py-3 text-sm"></label>
                        </div>
                        <label class="mt-4 block"><span class="text-sm font-semibold">Career goal</span><input name="career_goal" value="{{ old('career_goal', $user->career_goal) }}" class="mt-2 w-full rounded-md border border-black/10 px-4 py-3 text-sm"></label>
                    </div>
                    <div class="mt-7 flex flex-col gap-3 border-t border-black/10 pt-5 text-sm sm:flex-row sm:items-center sm:justify-between"><p class="text-neutral-500">Account email: <span class="font-semibold text-neutral-800">{{ $user->email }}</span></p><button class="rounded-md bg-neutral-950 px-5 py-3 font-black text-white">Save changes</button></div>
                </form>
            </section>
        </div>
    </div>
@endsection
