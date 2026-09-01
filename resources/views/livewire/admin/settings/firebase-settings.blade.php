<div>
    <div class="card" x-on:fcm-token-received.window="$wire.setBrowserToken($event.detail)">
        <h3>{{ __('settings.Firebase Cloud Messaging') }}</h3>

        <x-form wire:submit="update" method="put">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="space-y-4">
                    <x-form.input wire:model="firebaseProjectId" name="firebaseProjectId" :label="__('settings.Firebase Project ID')" />

                    <div class="mt-1 bg-white dark:bg-gray-500 dark:text-gray-200 rounded-md shadow-sm border border-gray-200">
                        <div class="p-4 flex items-center">
                            <div class="flex items-center h-5">
                                <input wire:model="isFirebaseEnabled" id="isFirebaseEnabled" type="checkbox" class="h-4 w-4 text-light-blue-600 cursor-pointer focus:ring-light-blue-500 border-gray-300">
                            </div>
                            <label for="isFirebaseEnabled" class="ml-3 cursor-pointer">
                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-300">
                                    {{ __('settings.Enable Firebase Notifications') }}
                                </span>
                            </label>
                        </div>
                        <div class="border-t border-gray-200 p-4 flex items-center">
                            <div class="flex items-center h-5">
                                <input wire:model="isFirebaseDebugEnabled" id="isFirebaseDebugEnabled" type="checkbox" class="h-4 w-4 text-light-blue-600 cursor-pointer focus:ring-light-blue-500 border-gray-300">
                            </div>
                            <label for="isFirebaseDebugEnabled" class="ml-3 cursor-pointer">
                                <span class="block text-sm font-medium text-gray-900 dark:text-gray-300">
                                    {{ __('settings.Enable Debug Mode') }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Firebase Web Config (JS Object)') }}</label>
                        <x-form.textarea wire:model="firebaseWebConfig" name="firebaseWebConfig" label="none" rows="5" />
                        <small class="text-gray-500 italic">{{ __('settings.Paste the Firebase Web App configuration object here.') }}</small>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('settings.Service Account JSON') }}</label>
                        <x-form.textarea wire:model="firebaseCredentials" name="firebaseCredentials" label="none" rows="5" />
                        <small class="text-gray-500 italic">{{ __('settings.Paste the content of your Firebase Service Account JSON file here.') }}</small>
                    </div>
                </div>
            </div>

            <div class="mt-5 flex gap-3">
                <x-button>{{ __('settings.Update Firebase Settings') }}</x-button>

                @if($isFirebaseEnabled && $firebaseCredentials && $firebaseProjectId)
                    <x-button type="button" variant="secondary" wire:click="testNotification" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="testNotification">{{ __('settings.Test Notification') }}</span>
                        <span wire:loading wire:target="testNotification">{{ __('settings.Sending...') }}</span>
                    </x-button>
                @endif
            </div>
        </x-form>

        @include('errors.messages')
    </div>
</div>
