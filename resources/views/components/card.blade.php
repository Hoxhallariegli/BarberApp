@props([
    'padding' => 'p-6 md:p-8',
    'rounded' => 'rounded-[2rem]',
    'shadow' => 'shadow-sm',
    'border' => 'border border-gray-100 dark:border-gray-700'
])

<div {{ $attributes->merge(['class' => "bg-white dark:bg-gray-800 $padding $rounded $shadow $border"]) }}>
    {{ $slot }}
</div>
