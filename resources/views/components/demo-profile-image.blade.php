@props(['alt' => 'Profile photo', 'user' => null, 'resume' => null])

@php
    $photoPath = $resume ? $resume->profilePhotoPath() : $user?->profile_photo_path;
    $profileImage = $photoPath
        ? \Illuminate\Support\Facades\Storage::disk('public')->url($photoPath)
        : asset('images/demo-profile.svg').'?v=2';
@endphp

<span {{ $attributes->merge(['class' => 'block overflow-hidden rounded-full bg-white']) }}>
    <img src="{{ $profileImage }}" alt="{{ $alt }}" class="h-full w-full object-cover">
</span>
