@props(['template', 'compact' => false])

@php
    $settings = $template->settings ?? [];
    $style = data_get($settings, 'style', 'classic');
    $accent = $template->accent_color ?: '#2563eb';
    $soft = data_get($settings, 'soft', '#eef2ff');
    $dark = data_get($settings, 'dark', '#111827');
    $surface = data_get($settings, 'surface', '#ffffff');
    $hasPhoto = (bool) data_get($settings, 'photo', false);
    $hasSidebar = in_array($style, ['sidebar', 'soft-sidebar', 'right-rail', 'technical'], true);
    $usesDarkSidebar = in_array($style, ['sidebar', 'right-rail', 'technical'], true);
    $sidebarBackground = $usesDarkSidebar ? $dark : $soft;
    $sidebarText = $usesDarkSidebar ? '#ffffff' : '#111827';
    $heightClass = $compact ? 'h-64' : 'h-80';
@endphp

<div {{ $attributes->merge(['class' => "relative overflow-hidden rounded-lg border border-black/10 bg-white shadow-sm {$heightClass}"]) }} style="font-family: {{ $template->font_family }}, Arial, sans-serif; background: {{ $surface }};">
    @if ($style === 'stripe')
        <div class="absolute right-0 top-0 h-20 w-20 -translate-y-8 translate-x-8 rotate-45" style="background: {{ $accent }};"></div>
        <div class="absolute left-0 bottom-0 h-16 w-16 translate-y-8 -translate-x-8 rotate-45" style="background: {{ $soft }};"></div>
    @endif

    @if ($style === 'edge')
        <div class="absolute left-0 top-0 h-full w-2" style="background: {{ $accent }};"></div>
        <div class="absolute right-0 top-0 h-10 w-16" style="background: {{ $soft }};"></div>
    @endif

    @if ($hasSidebar && data_get($settings, 'sidebar') === 'right')
        <div class="grid h-full grid-cols-[1.5fr_0.72fr]">
            <div class="p-4">
                <div class="h-4 w-28 rounded-sm bg-neutral-900"></div>
                <div class="mt-2 h-2 w-20 rounded-sm" style="background: {{ $accent }};"></div>
                <div class="mt-5 space-y-2">
                    <div class="h-2 w-full rounded-sm bg-neutral-200"></div>
                    <div class="h-2 w-10/12 rounded-sm bg-neutral-200"></div>
                    <div class="h-2 w-9/12 rounded-sm bg-neutral-200"></div>
                </div>
                <div class="mt-5 space-y-3">
                    @foreach (range(1, 3) as $line)
                        <div>
                            <div class="h-2 w-24 rounded-sm bg-neutral-800"></div>
                            <div class="mt-2 h-2 w-full rounded-sm bg-neutral-200"></div>
                            <div class="mt-1 h-2 w-11/12 rounded-sm bg-neutral-200"></div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="p-4" style="background: {{ $sidebarBackground }}; color: {{ $sidebarText }};">
                @if ($hasPhoto)
                    <div class="mx-auto h-14 w-14 rounded-full border-4 border-white/70 bg-white/80"></div>
                @endif
                <div class="mt-5 space-y-2">
                    <div class="h-2 w-16 rounded-sm bg-current opacity-90"></div>
                    <div class="h-1.5 w-full rounded-sm bg-current opacity-40"></div>
                    <div class="h-1.5 w-10/12 rounded-sm bg-current opacity-40"></div>
                    <div class="h-1.5 w-8/12 rounded-sm bg-current opacity-40"></div>
                </div>
                <div class="mt-6 grid gap-1.5">
                    @foreach (range(1, 5) as $dot)
                        <div class="h-1.5 rounded-sm bg-current opacity-50"></div>
                    @endforeach
                </div>
            </div>
        </div>
    @elseif ($hasSidebar)
        <div class="grid h-full grid-cols-[0.72fr_1.5fr]">
            <div class="p-4" style="background: {{ $sidebarBackground }}; color: {{ $sidebarText }};">
                @if ($hasPhoto)
                    <div class="mx-auto h-16 w-16 rounded-full border-4 border-white/70 bg-white/80"></div>
                @else
                    <div class="h-7 w-16 rounded-sm bg-current opacity-90"></div>
                @endif
                <div class="mt-5 space-y-2">
                    <div class="h-2 w-16 rounded-sm bg-current opacity-90"></div>
                    <div class="h-1.5 w-full rounded-sm bg-current opacity-40"></div>
                    <div class="h-1.5 w-10/12 rounded-sm bg-current opacity-40"></div>
                    <div class="h-1.5 w-8/12 rounded-sm bg-current opacity-40"></div>
                </div>
                <div class="mt-6 grid gap-1.5">
                    @foreach (range(1, 5) as $dot)
                        <div class="h-1.5 rounded-sm bg-current opacity-50"></div>
                    @endforeach
                </div>
            </div>
            <div class="p-4">
                <div class="h-4 w-28 rounded-sm bg-neutral-900"></div>
                <div class="mt-2 h-2 w-20 rounded-sm" style="background: {{ $accent }};"></div>
                <div class="mt-5 space-y-2">
                    <div class="h-2 w-full rounded-sm bg-neutral-200"></div>
                    <div class="h-2 w-10/12 rounded-sm bg-neutral-200"></div>
                    <div class="h-2 w-9/12 rounded-sm bg-neutral-200"></div>
                </div>
                <div class="mt-5 space-y-3">
                    @foreach (range(1, 3) as $line)
                        <div>
                            <div class="h-2 w-24 rounded-sm bg-neutral-800"></div>
                            <div class="mt-2 h-2 w-full rounded-sm bg-neutral-200"></div>
                            <div class="mt-1 h-2 w-11/12 rounded-sm bg-neutral-200"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @elseif (in_array($style, ['top-band', 'photo-band', 'feature', 'dark-header'], true))
        <div class="h-full">
            <div class="relative flex h-24 items-center gap-4 px-5" style="background: {{ $style === 'dark-header' ? $dark : $soft }};">
                @if ($hasPhoto)
                    <div class="h-16 w-16 rounded-full border-4 border-white bg-white"></div>
                @endif
                <div class="min-w-0 flex-1">
                    <div class="h-4 w-32 rounded-sm" style="background: {{ $style === 'dark-header' ? '#ffffff' : '#111827' }};"></div>
                    <div class="mt-2 h-2 w-24 rounded-sm" style="background: {{ $accent }};"></div>
                </div>
            </div>
            <div class="grid gap-4 p-5 md:grid-cols-[1.1fr_0.8fr]">
                <div class="space-y-3">
                    @foreach (range(1, 3) as $section)
                        <div>
                            <div class="h-2 w-24 rounded-sm bg-neutral-900"></div>
                            <div class="mt-2 h-2 rounded-sm bg-neutral-200"></div>
                            <div class="mt-1 h-2 w-10/12 rounded-sm bg-neutral-200"></div>
                        </div>
                    @endforeach
                </div>
                <div class="space-y-2">
                    <div class="h-2 w-16 rounded-sm bg-neutral-900"></div>
                    @foreach (range(1, 7) as $skill)
                        <div class="h-1.5 rounded-sm" style="background: {{ $skill % 2 === 0 ? $soft : '#e5e7eb' }};"></div>
                    @endforeach
                </div>
            </div>
        </div>
    @elseif ($style === 'timeline')
        <div class="h-full p-5">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-full bg-neutral-200"></div>
                <div>
                    <div class="h-4 w-32 rounded-sm bg-neutral-950"></div>
                    <div class="mt-2 h-2 w-24 rounded-sm" style="background: {{ $accent }};"></div>
                </div>
            </div>
            <div class="relative mt-6 space-y-4 pl-6">
                <div class="absolute bottom-0 left-2 top-0 w-px bg-neutral-300"></div>
                @foreach (range(1, 4) as $item)
                    <div class="relative">
                        <span class="absolute -left-[1.35rem] top-0 h-3 w-3 rounded-full" style="background: {{ $accent }};"></span>
                        <div class="h-2 w-24 rounded-sm bg-neutral-900"></div>
                        <div class="mt-2 h-2 rounded-sm bg-neutral-200"></div>
                        <div class="mt-1 h-2 w-9/12 rounded-sm bg-neutral-200"></div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="h-full p-5">
            <div @class(['text-center' => data_get($settings, 'header') === 'centered' || data_get($settings, 'header') === 'small-center'])>
                <div @class(['mx-auto' => data_get($settings, 'header') === 'centered' || data_get($settings, 'header') === 'small-center', 'h-4 w-36 rounded-sm bg-neutral-950'])></div>
                <div @class(['mx-auto' => data_get($settings, 'header') === 'centered' || data_get($settings, 'header') === 'small-center', 'mt-2 h-2 w-28 rounded-sm']) style="background: {{ $accent }};"></div>
            </div>
            <div class="mt-6 space-y-4">
                @foreach (range(1, $style === 'dense' ? 6 : 5) as $section)
                    <div>
                        <div class="h-2 w-24 rounded-sm bg-neutral-900"></div>
                        <div class="mt-2 h-2 rounded-sm bg-neutral-200"></div>
                        <div class="mt-1 h-2 w-10/12 rounded-sm bg-neutral-200"></div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
