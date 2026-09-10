@props(['value' => 0, 'label' => null])

<div class="relative grid size-20 place-items-center rounded-full" style="background: conic-gradient(#111827 {{ (int) $value }}%, #e7e5df 0)">
    <div class="grid size-16 place-items-center rounded-full bg-white">
        <span class="text-lg font-black">{{ (int) $value }}%</span>
        @if ($label)
            <span class="sr-only">{{ $label }}</span>
        @endif
    </div>
</div>
