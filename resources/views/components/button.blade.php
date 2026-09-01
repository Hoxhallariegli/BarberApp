@props([
    'type' => 'submit',
    'variant' => 'default',
    'size' => 'default',
    'disabled' => false,
])

@php
$class = "inline-flex items-center justify-center font-semibold disabled:opacity-50
disabled:cursor-not-allowed rounded-2xl cursor-pointer transition-none ";

$class .= " " . match($variant) {
    default => "bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow-sm",
    'primary' => "bg-primary text-white shadow-sm",
    'gray' => "bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300",
    'red' => "bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400",
    'green' => "bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400",
    'blue' => "bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400",
    'link' => "text-primary hover:underline p-0 !bg-transparent"
};

$class .= " " . match($size){
    default => "px-5 py-2.5 text-sm",
    'xs' => "px-2 py-1 text-[10px]",
    'sm' => "px-3 py-2 text-xs",
    'lg' => "px-7 py-3.5 text-base",
    'icon' => "size-10 p-0"
};
@endphp

<button
    type="{{ $type }}"
    @disabled($disabled)
    {{$attributes->merge(["class" => $class])->except(['size', 'variant'])}}
    >
    {{$slot}}
</button>
