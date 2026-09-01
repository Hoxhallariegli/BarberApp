@props([
    'type' => 'button',
    'variant' => 'default',
    'size' => 'default',
    'href' => null,
    'icon' => null,
    'navigate' => true,
    'responsive' => true,
])

@php
$class = "inline-flex items-center justify-center gap-2 font-semibold disabled:opacity-50
disabled:cursor-not-allowed rounded-2xl cursor-pointer ";

$class .= " " . match($variant) {
    default => "bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-sm hover:opacity-90",
    'primary' => "bg-primary text-white shadow-sm hover:opacity-90",
    'gray' => "bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700",
    'red' => "bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/50",
    'green' => "bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900/50",
    'blue' => "bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50",
    'link' => "text-primary hover:underline p-0 !bg-transparent shadow-none"
};

$class .= " " . match($size){
    default => "px-4 py-2 sm:px-5 sm:py-2.5 text-sm",
    'xs' => "px-2 py-1 text-[10px]",
    'sm' => "px-3 py-2 text-xs",
    'lg' => "px-6 py-3 text-base",
    'icon' => "size-10 p-0"
};
@endphp

@if($href)
    <a href="{{ $href }}" {{ $navigate ? 'wire:navigate' : '' }} {{$attributes->merge(["class" => $class])->except(['size', 'variant', 'navigate', 'icon', 'responsive'])}}>
        @if($icon)
            <x-dynamic-component :component="'heroicon-o-' . $icon" class="{{ $size === 'xs' ? 'w-3.5 h-3.5' : 'w-5 h-5' }}" />
        @endif
        <span class="{{ ($responsive && $icon) ? 'hidden sm:inline' : '' }}">{{$slot}}</span>
    </a>
@else
    <button type="{{ $type }}" {{$attributes->merge(["class" => $class])->except(['size', 'variant', 'icon', 'responsive'])}}>
        @if($icon)
            <x-dynamic-component :component="'heroicon-o-' . $icon" class="{{ $size === 'xs' ? 'w-3.5 h-3.5' : 'w-5 h-5' }}" />
        @endif
        <span class="{{ ($responsive && $icon) ? 'hidden sm:inline' : '' }}">{{$slot}}</span>
    </button>
@endif
