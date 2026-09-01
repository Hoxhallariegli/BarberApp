@props([
    'alignment' => 'right',
    'label' => ''
])

@php
    $alignmentClasses = [
        'left' => 'left-0',
        'right' => 'right-0'
    ];
@endphp

<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open" type="button" {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-xl bg-gray-50/50 dark:bg-gray-900/50 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 border border-gray-100 dark:border-gray-800 transition-none']) }}>
        {{ $label }}
        <x-heroicon-o-chevron-down class="size-4" />
    </button>

    <div
        class="absolute {{ $alignmentClasses[$alignment] }} z-50 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl mt-2 min-w-[12rem] border border-gray-50 dark:border-gray-700 p-2 transition-none"
        x-show="open"
        x-cloak
    >
        <div class="flex flex-col gap-1">
            {{ $slot }}
        </div>
    </div>
</div>
