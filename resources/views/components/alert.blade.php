@php
    $class = "rounded-2xl text-sm p-4 my-6 ";

    $class .= " " . match($attributes->get("variant")) {
        default => "bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400",
        'gray' => "bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-300",
        'red', 'danger' => "bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400",
        'yellow' => "bg-yellow-50 text-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-400",
        'green', 'success' => "bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400",
        'blue', 'info' => "bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400",
    };
@endphp

<div {{$attributes->merge(["class" => $class])->except(['variant'])}}>
    <div class="flex items-center gap-3">
        {{ $slot }}
    </div>
</div>
