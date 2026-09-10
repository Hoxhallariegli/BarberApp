<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>{{ __('bookings.Edit Booking') }}</x-h1>
            <x-short-description class="dark:text-gray-400">{{ __('bookings.Update info') }}</x-short-description>
        </div>
        <x-back-btn route="admin.bookings.index" />
    </div>

    @include('errors.errors')
    @include('errors.messages')

    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700">
        <form wire:submit.prevent="update" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">
                <div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1"><x-form.dropdown-search name="customer_id" wire:model.live="customer_id" :label="__('bookings.Customer Id')" :data="$customers" /></div>
                        <x-modal>
                            <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
                            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">{{ __('customers.Add Customer') }}</div></x-slot>
                            <x-slot name="content"><livewire:admin.berber-app.customers.quick-create /></x-slot>
                        </x-modal>
                    </div>
                </div>

                <div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1"><x-form.dropdown-search name="barber_id" wire:model.live="barber_id" :label="__('bookings.Barber Id')" :data="$barbers" /></div>
                        <x-modal>
                            <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
                            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">{{ __('barbers.Add Barber') }}</div></x-slot>
                            <x-slot name="content"><livewire:admin.berber-app.barbers.quick-create /></x-slot>
                        </x-modal>
                    </div>
                </div>

                <div class="col-span-full">
                    <label class="block mb-4 text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">{{ __('bookings.Services') }}</label>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {{-- Selector --}}
                        <div class="space-y-4">
                            <x-form.dropdown-search
                                name="temp_service_id"
                                wire:model.live="service_id"
                                label="none"
                                :data="$services"
                                :placeholder="__('bookings.Select services')"
                            />
                            <button type="button"
                                wire:click="addService"
                                class="w-full py-4 bg-blue-600 text-white text-[11px] font-black uppercase rounded-2xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/20 active:scale-95"
                            >
                                + {{ __('bookings.Add to list') }}
                            </button>
                        </div>

                        {{-- Bucket --}}
                        <div class="lg:col-span-2 bg-gray-50/50 dark:bg-gray-900/30 rounded-[2.5rem] p-8 border border-gray-100 dark:border-gray-700 min-h-[200px]">
                            @if(count($service_ids) > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($service_ids as $index => $sid)
                                        @php $svc = \App\Models\BerberApp\Service::find($sid); @endphp
                                        @if($svc)
                                        <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-[1.5rem] border border-gray-100 dark:border-gray-700 shadow-sm animate-in fade-in slide-in-from-right-4">
                                            <div class="flex items-center gap-3">
                                                <div class="size-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                                    <x-heroicon-o-check-badge class="w-5 h-5" />
                                                </div>
                                                <div>
                                                    <div class="text-[11px] font-black text-gray-900 dark:text-white uppercase">{{ $svc->translated_name }}</div>
                                                    <div class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">{{ number_format($svc->price, 0) }} Lek</div>
                                                </div>
                                            </div>
                                            <button type="button" wire:click="removeService({{ $index }})" class="p-2 text-gray-300 hover:text-red-500 transition-colors">
                                                <x-heroicon-o-trash class="w-5 h-5" />
                                            </button>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>
                                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between px-2">
                                    <div class="text-[10px] font-black uppercase text-gray-400 tracking-widest">{{ __('bookings.Total') }}</div>
                                    <div class="text-xl font-black text-blue-600 dark:text-blue-400">
                                        {{ number_format(\App\Models\BerberApp\Service::whereIn('id', $service_ids)->sum('price'), 0) }}
                                        <span class="text-xs">Lek</span>
                                    </div>
                                </div>
                            @else
                                <div class="h-full flex flex-col items-center justify-center py-10 opacity-30">
                                    <x-heroicon-o-squares-plus class="w-12 h-12 mb-4 text-gray-400" />
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em]">{{ __('bookings.No services selected') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-span-full md:col-span-1">
                    <x-form.input name="selectedDate" type="date" wire:model.live="selectedDate" :label="__('bookings.Date')" class="dark:bg-gray-900" />
                </div>
            </div>

            {{-- Availability Slots --}}
            <div class="mt-8 space-y-4">
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">{{ __('bookings.Available Slots') }}</label>

                @if($barber_id && count($service_ids) > 0 && $selectedDate)
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-3">
                        @forelse($availableSlots as $slot)
                            <button type="button"
                                wire:click="$set('selectedTime', '{{ $slot }}')"
                                class="py-3 text-xs font-black rounded-2xl transition-all {{ $selectedTime === $slot ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30 scale-105' : 'bg-gray-50 dark:bg-gray-900 text-gray-600 dark:text-gray-400 hover:bg-blue-50 dark:hover:bg-blue-900/20' }}">
                                {{ $slot }}
                            </button>
                        @empty
                            <div class="col-span-full p-6 text-center bg-gray-50 dark:bg-gray-900 rounded-[2rem] border border-dashed border-gray-200 dark:border-gray-700">
                                <p class="text-xs font-bold text-gray-400 italic uppercase tracking-widest">{{ __('bookings.No available slots for this date.') }}</p>
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="p-6 text-center bg-gray-50 dark:bg-gray-900 rounded-[2rem] border border-dashed border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-bold text-gray-400 italic uppercase tracking-widest">{{ __('bookings.Select barber and services to see slots') }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-12 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl" wire:loading.attr="disabled">
                    {{ __('bookings.Update') }}
                </x-button>
            </div>
        </form>
    </div>
</div>
