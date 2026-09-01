<div>
    <div class="flex items-center justify-between gap-4 px-1 mb-6">
        <div>
            <x-h1>{{ __('admin.Notification Settings') }}</x-h1>
            <x-short-description>{{ __('admin.Configure which modules trigger Firebase Push Notifications') }}</x-short-description>
        </div>
    </div>

    <div class="card !p-0 overflow-hidden shadow-none border-gray-200">
        <div class="overflow-x-auto border-t border-gray-100 dark:border-gray-700">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="bg-gray-100/50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('admin.Modules') }}</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('admin.Description') }}</th>
                        <th class="px-6 py-4 text-right text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('admin.Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                    @foreach($modules as $module)
                        <tr wire:key="notify-{{ $module }}" class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none">
                            <td class="px-6 py-5 font-bold text-gray-900 dark:text-white uppercase">{{ $module }}</td>
                            <td class="px-6 py-5 text-sm text-gray-500 italic">{{ __('admin.Send push notification on create/update') }}</td>
                            <td class="px-6 py-5 text-right">
                                <button wire:click="toggleNotification('{{ $module }}')"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $activeNotifications[$module] ? 'bg-primary' : 'bg-gray-200 dark:bg-gray-700' }}">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $activeNotifications[$module] ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
