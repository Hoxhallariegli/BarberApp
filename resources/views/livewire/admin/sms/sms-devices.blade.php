<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <x-h1>{{ __('SMS Gateway Devices') }}</x-h1>
            <x-short-description>{{ __('Manage Android devices connected via FCM to send SMS messages.') }}</x-short-description>
        </div>
    </div>

    <x-card padding="p-0 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="size-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                    <x-heroicon-o-device-phone-mobile class="size-4" />
                </div>
                <h3 class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-xs">{{ __('Registered Devices') }}</h3>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50/50 dark:bg-gray-900/50 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                    <tr>
                        <th class="px-6 py-4">{{ __('Device Name') }}</th>
                        <th class="px-6 py-4">{{ __('FCM Token') }}</th>
                        <th class="px-6 py-4 text-center">{{ __('Status') }}</th>
                        <th class="px-6 py-4 text-center">{{ __('Last Seen') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($devices as $device)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="size-10 rounded-xl bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-400 border border-gray-100 dark:border-gray-800">
                                        <x-heroicon-o-device-phone-mobile class="size-5" />
                                    </div>
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $device->device_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-[10px] text-gray-400 font-mono break-all line-clamp-1 max-w-[200px]" title="{{ $device->fcm_token }}">
                                    {{ $device->fcm_token }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button wire:click="toggleActive({{ $device->id }})" class="focus:outline-none">
                                    <x-badge :variant="$device->is_active ? 'success' : 'danger'">
                                        {{ $device->is_active ? __('Active') : __('Inactive') }}
                                    </x-badge>
                                </button>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="text-[10px] text-gray-400 font-bold">
                                    {{ $device->updated_at ? $device->updated_at->diffForHumans() : __('Never') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-btn wire:click="delete({{ $device->id }})"
                                           wire:confirm="{{ __('Are you sure you want to remove this device?') }}"
                                           variant="danger" size="xs" icon="trash">
                                        {{ __('Delete') }}
                                    </x-btn>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 text-xs font-bold uppercase tracking-widest italic">
                                {{ __('No devices registered yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
