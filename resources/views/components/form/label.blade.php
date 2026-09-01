@props([
    'label' => '',
    'name' => '',
    'required' => ''
])
<label aria-label="{{ $label }}" for="{{ $name }}" {{ $attributes->merge(['class' => 'ui-label']) }}>
    {{ $label }}
    @if ($required != '') <span class="text-red-500">*</span> @endif
</label>
