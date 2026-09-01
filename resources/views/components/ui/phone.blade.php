@props([
    'number' => '',
    'iconSize' => 'size-3'
])

<div {{ $attributes->merge(['class' => 'flex items-center gap-2 text-slate-900 dark:text-white']) }}>
    <x-heroicon-o-phone class="{{ $iconSize }} text-slate-400" />
    <span class="text-xs font-bold tracking-tighter">{{ $number }}</span>
</div>
