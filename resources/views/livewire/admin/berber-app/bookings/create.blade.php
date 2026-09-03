<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1">
        <div>
            <x-h1>{{ __('bookings.Add Booking') }}</x-h1>
            <x-short-description class="dark:text-gray-400">{{ __('bookings.New record') }}</x-short-description>
        </div>
        <x-back-btn route="admin.bookings.index" />
    </div>

    @include('errors.errors')
    @include('errors.messages')

    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700">
        <form wire:submit.prevent="store" class="space-y-8">
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

                <div>
                    <div class="flex items-end gap-2">
                        <div class="flex-1"><x-form.dropdown-search name="service_id" wire:model.live="service_id" :label="__('bookings.Service Id')" :data="$services" /></div>
                        <x-modal>
                            <x-slot name="trigger"><button type="button" @click="on = true" class="mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform"><x-heroicon-o-plus class="w-5 h-5" /></button></x-slot>
                            <x-slot name="modalTitle"><div class="dark:text-white px-6 pt-6">{{ __('services.Add Service') }}</div></x-slot>
                            <x-slot name="content"><livewire:admin.berber-app.services.quick-create /></x-slot>
                        </x-modal>
                    </div>
                </div>

                <div>
                    <x-form.input name="selectedDate" type="date" wire:model.live="selectedDate" :label="__('bookings.Date')" class="dark:bg-gray-900" />
                </div>
            </div>

            {{-- Availability Slots --}}
            <div class="mt-8 space-y-4">
                <label class="block text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 ml-1">{{ __('bookings.Available Slots') }}</label>

                @if($barber_id && $service_id && $selectedDate)
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
                        <p class="text-xs font-bold text-gray-400 italic uppercase tracking-widest">{{ __('bookings.Select Barber, Service and Date to see availability.') }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-12 flex justify-end">
                <x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl" wire:loading.attr="disabled">
                    {{ __('bookings.Save') }}
                </x-button>
            </div>
        </form>
    </div>
</div>
