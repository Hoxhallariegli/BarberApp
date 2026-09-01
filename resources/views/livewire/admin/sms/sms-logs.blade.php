<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <x-h1>{{ __('SMS Logs') }}</x-h1>
            <x-short-description>{{ __('View all sent and received SMS messages and their status.') }}</x-short-description>
        </div>
    </div>

    <x-card padding="p-0 overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="size-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                    <x-heroicon-o-list-bullet class="size-4" />
                </div>
                <h3 class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-xs">{{ __('Messages History') }}</h3>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-3">
                <x-form.input type="search" wire:model.live="search" placeholder="{{ __('Search phone or body...') }}" class="w-full md:w-64" />
                <x-form.select wire:model.live="status" class="w-full md:w-40">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="pending">{{ __('Pending') }}</option>
                    <option value="queued">{{ __('Queued') }}</option>
                    <option value="sent">{{ __('Sent') }}</option>
                    <option value="failed">{{ __('Failed') }}</option>
                </x-form.select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50/50 dark:bg-gray-900/50 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                    <tr>
                        <th class="px-6 py-4">{{ __('Phone Number') }}</th>
                        <th class="px-6 py-4">{{ __('Message Body') }}</th>
                        <th class="px-6 py-4 text-center">{{ __('Type') }}</th>
                        <th class="px-6 py-4 text-center">{{ __('Status') }}</th>
                        <th class="px-6 py-4 text-center">{{ __('Date') }}</th>
                        <th class="px-6 py-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors group">
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900 dark:text-white">{{ $log->phone_number }}</span>
                            </td>
                            <td class="px-6 py-4 max-w-xs lg:max-w-md">
                                <p class="text-gray-600 dark:text-gray-400 line-clamp-2 text-xs">{{ $log->body }}</p>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 text-[9px] font-bold uppercase">
                                    {{ $log->template_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $sStyles = [
                                        'sent' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/20 dark:text-emerald-400',
                                        'queued' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
                                        'pending' => 'bg-orange-50 text-orange-600 dark:bg-orange-900/20 dark:text-orange-400',
                                        'failed' => 'bg-red-50 text-red-600 dark:bg-red-900/20 dark:text-red-400'
                                    ];
                                @endphp
                                <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase {{ $sStyles[$log->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $log->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <span class="text-[10px] text-gray-400 font-bold">{{ $log->created_at->format('d M, H:i') }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($log->status === 'failed')
                                    <x-btn wire:click="retry({{ $log->id }})" variant="blue" size="xs" icon="arrow-path">
                                        {{ __('Retry') }}
                                    </x-btn>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400 text-xs font-bold uppercase tracking-widest italic">
                                {{ __('No logs found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-6 border-t border-gray-100 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
        @endif
    </x-card>
</div>
