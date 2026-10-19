@props(['alt' => 'Profile photo', 'user' => null])

@php
    $profileImage = $user?->profile_photo_path
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo_path)
        : asset('images/demo-profile.svg');
@endphp

<span {{ $attributes->merge(['class' => 'block overflow-hidden rounded-full bg-white']) }}>
    <img src="{{ $profileImage }}" alt="{{ $alt }}" class="h-full w-full object-cover">
</span>
