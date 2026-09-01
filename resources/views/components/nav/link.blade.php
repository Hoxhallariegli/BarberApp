@props([
    'route' => '',
    'icon' => '',
    'navEnabled' => true,
])

@php
    $isActive = request()->routeIs($route);
@endphp

<a @if($navEnabled) wire:navigate @endif
    href="{{ route($route) }}"
    class="group flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-semibold transition-none
    {{ $isActive
        ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-sm'
        : 'text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-900/50 hover:text-gray-900 dark:hover:text-white'
    }}">

    @if ($icon)
        <x-dynamic-component :component="'heroicon-o-' . $icon" class="size-5 shrink-0 {{ $isActive ? '' : 'text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300' }}" />
    @endif

    <span class="truncate">{{ $slot }}</span>
</a>
