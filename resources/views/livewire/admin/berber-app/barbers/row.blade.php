<tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none">
    <td class="px-6 py-5 font-bold text-blue-600 dark:text-blue-400">{{ $item->id }}</td>
    <td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->name }}</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->specialization }}</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->phone }}</td>
<td class="px-6 py-5 text-gray-600 dark:text-gray-300">{{ $item->commission_rate }}</td>
<td class="px-6 py-5">@if($item->photo) <a href="{{ asset($item->photo) }}" target="_blank" rel="noopener"><img src="{{ asset($item->photo) }}" class="w-10 h-10 object-cover rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm"></a> @else - @endif</td>
    <td class="px-6 py-5 text-right !transition-none">
        <div class="flex justify-end gap-3 !transition-none">
            @can('edit_barbers')
                <x-a href="{{ route('admin.barbers.schedule', $item) }}" class="!rounded-xl !bg-green-50 dark:!bg-green-900/30 !text-green-600 dark:!text-green-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none">{{ __('barbers.Edit Hours') }}</x-a>
                <x-a href="{{ route('admin.barbers.edit', $item) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none">{{ __('admin.Edit') }}</x-a>
            @endcan
            @can('delete_barbers')
                <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                    <x-modal>
                        <x-slot name="trigger"><button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300">{{ __('admin.Delete') }}</button></x-slot>
                        <x-slot name="modalTitle"><div class="text-left dark:text-white">{{ __('admin.Delete') }} {{ $item->name }}?</div></x-slot>
                        <x-slot name="content"><div class="text-left space-y-2"><p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.This action cannot be undone.') }}</p><input x-model="confirmation" :placeholder="__('admin.Type :name to confirm', ['name' => $item->name])" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none"></div></x-slot>
                        <x-slot name="footer"><x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button><x-button variant="red" x-bind:disabled="confirmation !== '{{ $item->name }}'" wire:click="$parent.deleteBarber('{{ $item->id }}')" @click="on = false">{{ __('admin.Delete') }}</x-button></x-slot>
                    </x-modal>
                </div>
            @endcan
        </div>
    </td>
</tr>
