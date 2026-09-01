<div class="p-6">
    @if($created)
        <div class="flex flex-col items-center text-center py-10">
            <div class="w-12 h-12 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mb-4">
                <x-heroicon-o-check class="w-6 h-6 text-green-500" />
            </div>
            <p class="font-bold text-gray-900 dark:text-white">{{ __('users.created') }}</p>
            @if($createdLabel)<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $createdLabel }}</p>@endif
            <button type="button" wire:click="addAnother" class="mt-6 text-xs font-black uppercase tracking-widest text-blue-600 dark:text-blue-400">{{ __('users.Add User') }}</button>
        </div>
    @else
        <div class="space-y-6">
            <x-form.input wire:model="name" :label="__('users.Name')" name="name" required class="dark:bg-gray-900" />
            <x-form.input wire:model="email" :label="__('users.Email')" name="email" required class="dark:bg-gray-900" />

            <div>
                <p class="text-[10px] font-bold uppercase tracking-widest mb-3 ml-1 text-gray-400">{{ __('roles.Roles') }}</p>
                <div class="grid grid-cols-2 gap-4">
                    @foreach($roles as $role)
                        <x-form.checkbox
                            wire:model="rolesSelected"
                            id="qc-role-{{ $role->id }}"
                            value="{{ $role->id }}"
                            label="{{ $role->label }}"
                        />
                    @endforeach
                </div>
                @error('rolesSelected')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-8 flex justify-end border-t border-gray-100 dark:border-gray-700 pt-6">
                <x-button wire:click="store" variant="blue">{{ __('users.Save') }}</x-button>
            </div>
        </div>
    @endif
</div>
