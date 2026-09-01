<div class="space-y-6">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('admin.Berber App Dashboard') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('admin.module_desc_berber-app') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <x-btn href="/berber-app" target="_blank" icon="globe-alt" variant="secondary">{{ __('admin.View Landing Page') }}</x-btn>
            <x-btn route="admin.bookings.index" icon="plus">{{ __('admin.Add Booking') }}</x-btn>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="card p-6 bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-[2rem]">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <x-heroicon-o-calendar class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">{{ __('admin.Total Bookings') }}</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $totalBookings }}</p>
                </div>
            </div>
        </div>

        <div class="card p-6 bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-[2rem]">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <x-heroicon-o-user class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">{{ __('admin.Active Barbers') }}</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $totalBarbers }}</p>
                </div>
            </div>
        </div>

        <div class="card p-6 bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-[2rem]">
            <div class="flex items-center gap-4">
                <div class="size-12 rounded-2xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                    <x-heroicon-o-tag class="size-6" />
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">{{ __('admin.Services') }}</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $totalServices }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Bookings --}}
        <div class="lg:col-span-2 card !p-0 overflow-hidden bg-white dark:bg-gray-800 shadow-sm border border-gray-100 dark:border-gray-700 rounded-[2.5rem]">
            <div class="p-6 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
                <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-tight">{{ __('admin.Recent Bookings') }}</h3>
                <x-a route="admin.bookings.index" class="text-xs font-bold text-blue-600">{{ __('admin.View All') }}</x-a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50/50 dark:bg-gray-700/50 text-[10px] font-black uppercase text-gray-400 tracking-widest">
                        <tr>
                            <th class="px-6 py-4">{{ __('admin.Customer') }}</th>
                            <th class="px-6 py-4">{{ __('admin.Barber') }}</th>
                            <th class="px-6 py-4">{{ __('admin.Service') }}</th>
                            <th class="px-6 py-4">{{ __('admin.Date/Time') }}</th>
                            <th class="px-6 py-4 text-right">{{ __('admin.Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                        @foreach($recentBookings as $booking)
                            <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/30 transition-colors">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $booking->customer?->name }}</td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $booking->barber?->name ?? 'Any' }}</td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $booking->service?->name }}</td>
                                <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $booking->appointment_datetime->format('d/m H:i') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest bg-emerald-50 text-emerald-600">
                                        Active
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="card p-8 bg-gray-900 text-white rounded-[2.5rem] flex flex-col justify-between">
            <div>
                <h3 class="text-xl font-black uppercase tracking-tight mb-4 text-blue-400">{{ __('admin.Quick Actions') }}</h3>
                <p class="text-gray-400 text-sm mb-8 leading-relaxed italic font-serif">"{{ __('admin.Manage your salon with ease. Every confirmed booking automatically sends a push notification to the client.') }}"</p>
                <div class="space-y-4">
                    <a href="/berber-app" target="_blank" class="flex items-center gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors">
                        <div class="size-10 rounded-xl bg-blue-500 flex items-center justify-center"><x-heroicon-o-globe-alt class="size-5 text-white" /></div>
                        <span class="font-bold text-sm uppercase tracking-widest">{{ __('admin.Public Page') }}</span>
                    </a>
                    <x-a route="admin.barbers.index" class="flex items-center gap-4 p-4 rounded-2xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors !text-white !border-none !px-0 !py-0">
                         <div class="flex items-center gap-4 w-full">
                            <div class="size-10 rounded-xl bg-emerald-500 flex items-center justify-center"><x-heroicon-o-user-group class="size-5 text-white" /></div>
                            <span class="font-bold text-sm uppercase tracking-widest">{{ __('admin.Manage Team') }}</span>
                         </div>
                    </x-a>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-white/5">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-500">The Station Barbers • v1.0</p>
            </div>
        </div>
    </div>
</div>
