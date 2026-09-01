@props([
    'name' => '',
    'phone' => '',
    'label' => null
])

<div {{ $attributes->merge(['class' => 'flex flex-col']) }}>
    @if($label)
        <span class="ui-label">{{ $label }}</span>
    @endif
    <p class="text-base font-black text-gray-900 dark:text-white uppercase leading-none">{{ $name }}</p>
    @if($phone)
        <x-ui.phone :number="$phone" class="mt-1" />
    @endif
</div>
