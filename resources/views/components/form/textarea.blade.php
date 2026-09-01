@props([
    'required' => '',
    'name' => '',
    'label' => '',
    'rows' => 3
])

@if ($label === 'none')

@elseif ($label === '')
    @php
        $label = str_replace('_', ' ', $name);
        $label = preg_split('/(?=[A-Z])/', $label);
        $label = implode(' ', $label);
        $label = ucwords(strtolower($label));
    @endphp
@endif

<div class="space-y-1 mb-6">
    @if ($label !='none')
        <x-form.label :$label :$required :$name />
    @endif

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        {{ $required }}
        @class([
            'block w-full bg-gray-50/50 dark:bg-gray-900/50 dark:text-gray-200 dark:placeholder-gray-500 border rounded-2xl py-3 px-4 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-none sm:text-sm shadow-none',
            'border-gray-200 dark:border-gray-700' => !$errors->has($name),
            'border-red-500 dark:border-red-500/50 ring-2 ring-red-500/10' => $errors->has($name),
        ])
        {{ $attributes }}
    >{{ $slot }}</textarea>

    @error($name)
        <p class="text-xs font-medium text-red-600 dark:text-red-400 mt-1 ml-1" aria-live="assertive">{{ $message }}</p>
    @enderror
</div>
