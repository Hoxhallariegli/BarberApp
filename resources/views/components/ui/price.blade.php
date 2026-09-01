@props([
    'value' => 0,
    'currency' => '€',
    'size' => 'sm',
    'variant' => 'slate'
])

@php
$class = "font-black tracking-tighter ";
$class .= match($size) {
    'xs' => 'text-[9px]',
    'sm' => 'text-xs',
    'md' => 'text-base',
    'lg' => 'text-xl italic',
    'xl' => 'ui-value-large !mt-0',
    default => 'text-xs'
};

$class .= " " . match($variant) {
    'slate' => 'text-slate-900 dark:text-white',
    'orange' => 'text-orange-500 dark:text-orange-400',
    'emerald' => 'text-emerald-600 dark:text-emerald-400',
    'blue' => 'text-blue-600 dark:text-blue-400',
    default => 'text-slate-900 dark:text-white'
};
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>
    {{ $currency }}{{ number_format((float)$value, 0) }}
</span>
