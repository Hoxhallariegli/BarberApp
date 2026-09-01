<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <x-h1>{{ __('sms.SMS Gateway Settings') }}</x-h1>
            <x-short-description>{{ __('sms.Manage your SMS gateway, test connection and trigger reminders.') }}</x-short-description>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Connection Status --}}
        <x-card>
            <div class="flex items-center gap-3 mb-6">
                <div class="size-8 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600">
                    <x-heroicon-o-signal class="size-4" />
                </div>
                <h3 class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-xs">{{ __('sms.Gateway Status') }}</h3>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest">{{ __('sms.Firebase Enabled') }}</span>
                    <x-badge :variant="$isFirebaseEnabled ? 'success' : 'danger'">
                        {{ $isFirebaseEnabled ? __('sms.Enabled') : __('sms.Disabled') }}
                    </x-badge>
                </div>

                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                    <span class="text-xs font-bold text-gray-600 dark:text-gray-400 uppercase tracking-widest">{{ __('sms.Active Device') }}</span>
                    @if($activeDevice)
                        <div class="flex flex-col items-end">
                            <span class="text-sm font-black text-gray-900 dark:text-white">{{ $activeDevice->device_name }}</span>
                            <span class="text-[9px] text-gray-400 uppercase font-black tracking-tighter">{{ __('sms.Online') }}</span>
                        </div>
                    @else
                        <x-badge variant="danger">{{ __('sms.No Active Device') }}</x-badge>
                    @endif
                </div>

                <div class="pt-4">
                    <x-btn wire:click="triggerReminders" variant="blue" class="w-full justify-center" icon="bell">
                        {{ __('sms.Run Manual Reminders') }}
                    </x-btn>
                </div>
            </div>
        </x-card>

        {{-- Test SMS Form --}}
        <x-card>
            <div class="flex items-center gap-3 mb-6">
                <div class="size-8 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center text-green-600">
                    <x-heroicon-o-paper-airplane class="size-4" />
                </div>
                <h3 class="font-black text-gray-900 dark:text-white uppercase tracking-tight text-xs">{{ __('sms.Test SMS Gateway') }}</h3>
            </div>

            <form wire:submit.prevent="sendTestSms" class="space-y-4">
                <x-form.group label="{{ __('sms.Phone Number') }}" for="testPhone">
                    <x-form.input wire:model="testPhone" id="testPhone" placeholder="+38349123456" />
                </x-form.group>

                <x-form.group label="{{ __('sms.Message Body') }}" for="testMessage">
                    <x-form.textarea wire:model="testMessage" id="testMessage" placeholder="{{ __('sms.Type your test message here...') }}" />
                </x-form.group>

                <div class="pt-2">
                    <x-btn type="submit" variant="green" class="w-full justify-center" icon="paper-airplane">
                        {{ __('sms.Send Test SMS') }}
                    </x-btn>
                </div>
            </form>
        </x-card>
    </div>
</div>
