<div class="space-y-10">
    <div class="flex items-center justify-between gap-4 px-1"><div><x-h1>{{ __('device-tokens.Edit DeviceToken') }}</x-h1><x-short-description class="dark:text-gray-400">{{ __('device-tokens.Update info') }}</x-short-description></div><x-back-btn route="admin.device-tokens.index" /></div>
    @include('errors.errors')
    <div class="bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700"><form wire:submit.prevent="update" class="space-y-8"><div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8"><div><x-form.input name="user_id" type="text" wire:model="user_id" :label="__('device-tokens.User Id')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="fcm_token" type="text" wire:model="fcm_token" :label="__('device-tokens.Fcm Token')" class="dark:bg-gray-900" /></div>
<div><x-form.input name="device_type" type="text" wire:model="device_type" :label="__('device-tokens.Device Type')" class="dark:bg-gray-900" /></div></div><div class="mt-10 flex justify-end"><x-button type="submit" variant="blue" class="w-full sm:w-auto !px-12 !py-4 !rounded-2xl">{{ __('device-tokens.Update') }}</x-button></div></form></div>
</div>
