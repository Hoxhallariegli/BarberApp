<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('bookings.Edit Booking') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('bookings.Update info') }}</x-short-description></div><x-back-btn route="admin.bookings.index" /></div>
    @include('errors.errors')
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700"><form wire:submit.prevent="update" class="space-y-8"><div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div>
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
<div><x-form.input name="appointment_datetime" type="datetime-local" wire:model="appointment_datetime" :label="__('bookings.Appointment Datetime')" class="dark:bg-gray-900" /></div></div><div class="mt-10 flex justify-end"><x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('bookings.Update') }}</x-button></div></form></div>
</div>
