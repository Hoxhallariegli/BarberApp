@props([
    'name' => null,
    'label' => '',
    'sortField' => null,
    'sortAsc' => true,
    'sortable' => true,
])

<th {{ $attributes->merge(['class' => 'px-6 py-4 text-[10px] font-black uppercase tracking-widest  group ' . ($sortable && $name ? 'cursor-pointer' : 'cursor-default')]) }}
    @if($sortable && $name) wire:click="sortBy('{{ $name }}')" @endif
>
    @if($sortable && $name)
        <div
            @class([
                'flex items-center gap-1.5 transition-colors duration-200 uppercase tracking-widest font-black',
                'text-blue-600 dark:text-blue-400' => true,
                'group-hover:text-blue-700 dark:group-hover:text-blue-300' => true,
            ])
        >
            <span>{{ $label }}</span>

            <div class="flex items-center transition-all duration-200 {{ $sortField === $name ? 'opacity-100' : 'opacity-0 group-hover:opacity-40' }}">
                @if($sortField === $name)
                    @if($sortAsc)
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 15l7-7 7 7"></path></svg>
                    @else
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M19 9l-7 7-7-7"></path></svg>
                    @endif
                @else
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path></svg>
                @endif
            </div>
        </div>
    @else
        <span class="text-gray-400 dark:text-gray-500 uppercase tracking-widest font-black">
            {{ $label }}
        </span>
    @endif
</th>
