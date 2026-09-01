@props(['route', 'label' => __('admin.Back to List')])

<x-btn :href="Route::has($route) ? route($route) : $route" variant="gray" icon="arrow-left" size="sm" {{ $attributes }}>
    <span class="hidden sm:inline">{{ $label }}</span>
</x-btn>
