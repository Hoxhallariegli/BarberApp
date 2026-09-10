<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none">
    <td class="px-6 py-5 font-bold text-blue-600 dark:text-blue-400">{{ $item->id }}</td>
    <td class="px-6 py-5">
        <div class="font-bold text-gray-900 dark:text-white">{{ $item->customer_name ?: ($item->customer?->name ?? '-') }}</div>
        <div class="text-[10px] text-gray-400 font-medium">{{ $item->customer_phone ?: ($item->customer?->phone ?? '-') }}</div>
    </td>
    <td class="px-6 py-5 font-bold text-gray-900 dark:text-white">
        <div class="flex items-center gap-2">
            <div class="size-6 rounded-lg bg-brass/10 flex items-center justify-center text-brass-deep text-[10px]">{{ substr($item->barber?->name ?? 'A', 0, 1) }}</div>
            {{ $item->barber?->name ?? '-' }}
        </div>
    </td>
    <td class="px-6 py-5">
        <div class="flex flex-wrap gap-1">
            @if($item->services->isNotEmpty())
                @foreach($item->services as $svc)
                    <span class="px-2 py-1 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-[10px] font-bold uppercase">{{ $svc->translated_name }}</span>
                @endforeach
            @else
                <span class="px-2 py-1 rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-[11px] font-bold">{{ $item->service?->translated_name ?? '-' }}</span>
            @endif
        </div>
    </td>
    <td class="px-6 py-5 text-gray-600 dark:text-gray-300 font-medium">
        {{ $item->appointment_datetime?->format('d/m/Y') }}
        <div class="text-[10px] font-black text-blue-500 uppercase tracking-widest">{{ $item->appointment_datetime?->format('H:i') }}</div>
    </td>
    <td class="px-6 py-5">
        @php
            $statusVariant = match($item->status) {
                'confirmed' => 'success',
                'completed' => 'blue',
                'cancelled' => 'danger',
                default => 'warning',
            };
        @endphp
        <x-badge :variant="$statusVariant">
            {{ __($item->status ?? 'pending') }}
        </x-badge>
    </td>
    <td class="px-6 py-5 text-right font-black text-gray-900 dark:text-white">
        {{ number_format($item->total_price ?: ($item->service?->price ?? 0), 0) }} <span class="text-[9px] text-gray-400">Lek</span>
    </td>
    <td class="px-6 py-5 text-right !transition-none">
        <div class="flex justify-end gap-3 !transition-none">
            @if(in_array($item->status, ['pending', 'confirmed']))
                <button
                    wire:click="$parent.openDoneModal('{{ $item->id }}')"
                    class="!rounded-xl !bg-emerald-50 dark:!bg-emerald-900/30 !text-emerald-600 dark:!text-emerald-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none hover:scale-105 transition-transform"
                >
                    {{ __('Done') }}
                </button>

                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger">
                            <button @click="on = true" class="!rounded-xl !bg-red-50 dark:!bg-red-900/30 !text-red-600 dark:!text-red-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none hover:scale-105 transition-transform">
                                {{ __('Anullo') }}
                            </button>
                        </x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">{{ __('Anullo Rezervimin') }} #{{ $item->id }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Jeni i sigurt që dëshironi të anulloni këtë rezervim?') }}</p></div></x-slot>
                        <x-slot name="footer">
                            <x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button>
                            <x-button variant="red" wire:click="$parent.cancelBooking('{{ $item->id }}')" @click="on = false">
                                {{ __('Konfirmo Anullimin') }}
                            </x-button>
                        </x-slot>
                    </x-modal>
                </div>
            @endif

            @can('edit_bookings')
                <x-a href="{{ route('admin.bookings.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none">{{ __('admin.Edit') }}</x-a>
            @endcan
            @can('delete_bookings')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">{{ __('admin.Delete') }}</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">{{ __('admin.Delete') }} #{{ $item->id }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.This action cannot be undone.') }}</p><input x-model="confirmation" :placeholder="__('admin.Type :name to confirm', ['name' => $item->id])" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->id }}'" wire:click="$parent.deleteBooking('{{ $item->id }}')" @click="on = false">{{ __('admin.Delete') }}</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>
</tr>
