@php
    $class = "inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold tracking-tight ";

    $class .= " " . match($attributes->get("variant")) {
        default => "bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300",
        'primary' => "bg-primary/10 text-primary dark:bg-primary/20 dark:text-primary-light",
        'gray' => "bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300",
        'red', 'danger' => "bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400",
        'yellow' => "bg-yellow-50 text-yellow-600 dark:bg-yellow-900/30 dark:text-yellow-400",
        'green', 'success' => "bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400",
        'blue', 'info' => "bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400",
    };
@endphp

<div {{ $attributes->merge(["class" => $class])->except(['variant']) }}>
    {{ $slot }}
</div>
