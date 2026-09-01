<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('barbers.Edit Barber') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('barbers.Update info') }}</x-short-description></div><x-back-btn route="admin.barbers.index" /></div>
    @include('errors.errors')
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700"><form wire:submit.prevent="update" class="space-y-8"><div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div><x-form.input name="name" type="text" wire:model="name" :label="__('barbers.Name')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="specialization" type="text" wire:model="specialization" :label="__('barbers.Specialization')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="phone" type="text" wire:model="phone" :label="__('barbers.Phone')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="commission_rate" type="text" wire:model="commission_rate" :label="__('barbers.Commission Rate')" class="dark:bg-gray-900" /></div>
<div><x-form.file-upload name="photo" wire:model="photo" :label="__('barbers.Photo')" id="photo" :isEditing="true" /></div></div><div class="mt-10 flex justify-end"><x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('barbers.Update') }}</x-button></div></form></div>
</div>
