<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('customers.Add Customer') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('customers.New record') }}</x-short-description></div><x-back-btn route="admin.customers.index" /></div>
    @include('errors.errors')
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700"><form wire:submit.prevent="store" class="space-y-8"><div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div><x-form.input name="name" type="text" wire:model="name" :label="__('customers.Name')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="phone" type="text" wire:model="phone" :label="__('customers.Phone')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="email" type="text" wire:model="email" :label="__('customers.Email')" class="dark:bg-gray-900" /></div>
<div><x-form.file-upload name="photo" wire:model="photo" :label="__('customers.Photo')" id="photo" :isEditing="false" /></div></div><div class="mt-10 flex justify-end"><x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('customers.Save') }}</x-button></div></form></div>
</div>
