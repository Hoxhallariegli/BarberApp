<tr>
    <td class="px-6 py-5">{{ $role->label }}</td>
    <td class="px-6 py-5 text-right">
        <div class="flex justify-end space-x-2">

            @can('edit_roles')
                <x-a href="{{ route('admin.settings.roles.edit', ['role' => $role->id]) }}" class="!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none !transition-none">{{ __('admin.Edit') }}</x-a>
            @endcan

            @if ($role->name !== 'admin')
                @can('delete_roles')
                    <div x-data="{ confirmation: '' }" x-cloak class="inline-block">
                        <x-modal>
                            <x-slot name="trigger">
                                <button @click="on = true" class="text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300 !transition-none">{{ __('admin.Delete') }}</button>
                            </x-slot>

                            <x-slot name="modalTitle">
                                <div class="py-2 text-lg font-black uppercase tracking-tighter text-gray-900 dark:text-white whitespace-normal text-left">
                                    {{ __('users.Are you sure you want to delete') }}: <span class="text-red-600">{{ $role->label }}</span>?
                                </div>
                            </x-slot>

                            <x-slot name="content">
                                <div class="space-y-4 text-left whitespace-normal">
                                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                        {{ __('users.Delete Warning') }}
                                    </p>
                                    <div class="flex flex-col gap-2">
                                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ __('users.Type the name') }} <span class="font-bold text-red-600">"{{ $role->label }}"</span> {{ __('users.to confirm') }}</div>
                                        <input x-model="confirmation" class="px-3 py-2 text-sm border border-slate-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white w-full focus:ring-2 focus:ring-red-500 outline-none !transition-none">
                                    </div>
                                </div>
                            </x-slot>

                            <x-slot name="footer">
                                <x-button variant="gray" @click="on = false" class="!transition-none">{{ __('admin.Cancel') }}</x-button>
                                <x-button
                                    variant="red"
                                    x-bind:disabled="confirmation !== '{{ $role->label }}'"
                                    wire:click="$parent.deleteRole('{{ $role->id }}')"
                                    @click="on = false"
                                    class="!transition-none"
                                >
                                    {{ __('admin.Delete') }}
                                </x-button>
                            </x-slot>
                        </x-modal>
                    </div>
                @endcan
            @endif
        </div>
    </td>
</tr>
