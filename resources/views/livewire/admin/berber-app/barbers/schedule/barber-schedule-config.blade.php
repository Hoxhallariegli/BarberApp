<div class="space-y-10">
    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>{{ __('barbers.Configure Hours') }}</x-h1>
            <x-short-description class="dark:text-gray-400">{{ __('admin.for') }}: {{ $barber->name }}</x-short-description>
        </div>
        <x-back-btn route="admin.barbers.index" />
    </div>

    @include('errors.messages')
    @include('errors.errors')

    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700 overflow-hidden">
        <form wire:submit.prevent="save">
            {{-- Desktop View --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-100/50 dark:bg-gray-700/50 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                        <tr>
                            <th class="px-6 py-4">{{ __('barbers.Day') }}</th>
                            <th class="px-6 py-4 text-center">{{ __('barbers.Working?') }}</th>
                            <th class="px-6 py-4">{{ __('barbers.Start') }}</th>
                            <th class="px-6 py-4">{{ __('barbers.End') }}</th>
                            <th class="px-6 py-4">{{ __('barbers.Break Start') }}</th>
                            <th class="px-6 py-4">{{ __('barbers.Break End') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach($schedules as $dayNum => $data)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none {{ !$schedules[$dayNum]['is_working'] ? 'bg-gray-50/30 dark:bg-gray-900/30' : '' }}">
                                <td class="px-6 py-5 font-bold text-gray-900 dark:text-white">
                                    {{ $data['day_name'] }}
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <input type="checkbox" wire:model.live="schedules.{{ $dayNum }}.is_working" class="rounded-lg border-gray-200 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500/20 dark:bg-gray-800">
                                </td>
                                <td class="px-6 py-5">
                                    <input type="time" wire:model="schedules.{{ $dayNum }}.start_time" class="p-2.5 text-xs font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 dark:text-white {{ !$schedules[$dayNum]['is_working'] ? 'opacity-30 cursor-not-allowed' : '' }}" {{ !$schedules[$dayNum]['is_working'] ? 'disabled' : '' }}>
                                </td>
                                <td class="px-6 py-5">
                                    <input type="time" wire:model="schedules.{{ $dayNum }}.end_time" class="p-2.5 text-xs font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 dark:text-white {{ !$schedules[$dayNum]['is_working'] ? 'opacity-30 cursor-not-allowed' : '' }}" {{ !$schedules[$dayNum]['is_working'] ? 'disabled' : '' }}>
                                </td>
                                <td class="px-6 py-5">
                                    <input type="time" wire:model="schedules.{{ $dayNum }}.break_start_time" class="p-2.5 text-xs font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 dark:text-white {{ !$schedules[$dayNum]['is_working'] ? 'opacity-30 cursor-not-allowed' : '' }}" {{ !$schedules[$dayNum]['is_working'] ? 'disabled' : '' }}>
                                </td>
                                <td class="px-6 py-5">
                                    <input type="time" wire:model="schedules.{{ $dayNum }}.break_end_time" class="p-2.5 text-xs font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 dark:text-white {{ !$schedules[$dayNum]['is_working'] ? 'opacity-30 cursor-not-allowed' : '' }}" {{ !$schedules[$dayNum]['is_working'] ? 'disabled' : '' }}>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile View --}}
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700/50">
                @foreach($schedules as $dayNum => $data)
                    <div class="p-6 space-y-4 {{ !$schedules[$dayNum]['is_working'] ? 'bg-gray-50/30 dark:bg-gray-900/30' : '' }}">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-widest text-gray-900 dark:text-white">{{ $data['day_name'] }}</span>
                            <div class="flex items-center gap-2">
                                <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">{{ __('barbers.Working?') }}</span>
                                <input type="checkbox" wire:model.live="schedules.{{ $dayNum }}.is_working" class="rounded-lg border-gray-200 dark:border-gray-700 text-blue-600 shadow-sm focus:ring-blue-500/20 dark:bg-gray-800">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4" x-show="$wire.schedules[{{ $dayNum }}].is_working">
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">{{ __('barbers.Start') }}</label>
                                <input type="time" wire:model="schedules.{{ $dayNum }}.start_time" class="w-full p-3 text-xs font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">{{ __('barbers.End') }}</label>
                                <input type="time" wire:model="schedules.{{ $dayNum }}.end_time" class="w-full p-3 text-xs font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">{{ __('barbers.Break Start') }}</label>
                                <input type="time" wire:model="schedules.{{ $dayNum }}.break_start_time" class="w-full p-3 text-xs font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                            </div>
                            <div class="space-y-1.5">
                                <label class="text-[9px] font-black uppercase tracking-widest text-gray-400">{{ __('barbers.Break End') }}</label>
                                <input type="time" wire:model="schedules.{{ $dayNum }}.break_end_time" class="w-full p-3 text-xs font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-8 sm:p-12 bg-gray-50/50 dark:bg-gray-900/50 border-t border-gray-50 dark:border-gray-700/50 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl flex items-center justify-center gap-2">
                    <x-heroicon-s-check class="w-4 h-4" />
                    {{ __('barbers.Save') }}
                </x-button>
            </div>
        </form>
    </div>
</div>
