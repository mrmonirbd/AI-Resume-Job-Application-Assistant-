@props(['alt' => 'Demo profile photo'])

<span {{ $attributes->merge(['class' => 'block overflow-hidden rounded-full bg-white']) }}>
    <img src="{{ asset('images/demo-profile.svg') }}" alt="{{ $alt }}" class="h-full w-full object-cover">
</span>
