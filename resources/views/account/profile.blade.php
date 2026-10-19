@extends('layouts.app')

@section('title', 'Account details')

@section('content')
    @php
        $profilePhotoUrl = $user->profile_photo_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($user->profile_photo_path)
            : asset('images/demo-profile.svg');
    @endphp
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
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="p-6" x-data="profilePhotoCropper(@js($profilePhotoUrl))">
                    @csrf
                    @method('PATCH')
                    @error('profile')<p class="mb-5 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    <div class="mb-6 flex flex-col gap-4 border-b border-black/10 pb-6 sm:flex-row sm:items-center">
                        <span class="block h-20 w-20 shrink-0 overflow-hidden rounded-full border border-black/10 bg-white">
                            <img :src="previewUrl" alt="Profile photo preview" class="h-full w-full object-cover">
                        </span>
                        <div class="block min-w-0">
                            <span class="text-sm font-semibold">Profile picture</span>
                            <input x-ref="profilePhoto" @change="choosePhoto" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm text-neutral-600 file:mr-4 file:rounded-md file:border-0 file:bg-neutral-950 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-neutral-800">
                            <span class="mt-2 block text-xs text-neutral-500">JPG, PNG, or WebP. Maximum 2 MB.</span>
                            <x-field-error name="profile_photo" />
                        </div>
                    </div>

                    <div x-cloak x-show="cropOpen" x-transition.opacity class="fixed inset-0 z-50 grid place-items-center bg-black/60 p-4" @keydown.escape.window="cancelCrop()" role="dialog" aria-modal="true" aria-label="Crop profile picture">
                        <div class="w-full max-w-md rounded-lg bg-white p-5 shadow-xl" @click.outside="cancelCrop()">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-lg font-black">Crop profile picture</h3>
                                </div>
                                <button type="button" @click="cancelCrop()" class="rounded-md border border-black/10 px-3 py-2 text-sm font-semibold hover:bg-black/5">Close</button>
                            </div>

                            <div class="mt-5 flex justify-center">
                                <div class="relative h-72 w-72 touch-none overflow-hidden rounded-md bg-neutral-100" @pointerdown="startDrag" @pointermove="drag" @pointerup="stopDrag" @pointercancel="stopDrag">
                                    <img :src="sourceUrl" alt="Crop source" class="pointer-events-none absolute max-w-none select-none" :style="`width: ${imageWidth * scale}px; height: ${imageHeight * scale}px; transform: translate(${x}px, ${y}px);`">
                                    <div class="absolute cursor-move border-2 border-white shadow-[0_0_0_999px_rgba(0,0,0,0.45)]" :style="`width: ${cropSize}px; height: ${cropSize}px; transform: translate(${cropX}px, ${cropY}px);`" @pointerdown.stop="startCropMove" @pointermove.stop="moveCrop" @pointerup.stop="stopCropPointer" @pointercancel.stop="stopCropPointer">
                                        <button type="button" aria-label="Resize crop area" class="absolute -bottom-2 -right-2 h-5 w-5 cursor-se-resize rounded-sm border-2 border-white bg-neutral-950" @pointerdown.stop="startCropResize" @pointermove.stop="resizeCrop" @pointerup.stop="stopCropPointer" @pointercancel.stop="stopCropPointer"></button>
                                    </div>
                                </div>
                            </div>

                            <label class="mt-5 block">
                                <span class="text-sm font-semibold">Zoom</span>
                                <input type="range" :min="minScale" :max="maxScale" step="0.01" x-model.number="scale" @input="clampPosition()" class="mt-3 w-full accent-neutral-950">
                            </label>

                            <div class="mt-6 flex justify-end gap-3">
                                <button type="button" @click="cancelCrop()" class="rounded-md border border-black/10 px-4 py-2.5 text-sm font-semibold hover:bg-black/5">Cancel</button>
                                <button type="button" @click="useCroppedPhoto()" class="rounded-md bg-neutral-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-neutral-800">Use cropped photo</button>
                            </div>
                            <canvas x-ref="cropCanvas" class="hidden"></canvas>
                        </div>
                    </div>
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
