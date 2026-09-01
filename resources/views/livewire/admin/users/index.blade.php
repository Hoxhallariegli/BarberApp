<div>
    <div class="flex justify-between">
        <h1>{{ __('users.Users') }}</h1>
        <div>
            @can('add_users')
                <livewire:admin.users.invite/>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="mt-5 grid sm:grid-cols-1 md:grid-cols-3 gap-4">
            <div class="col-span-2">
                <x-form.input type="search" name="name" wire:model.live="name" label="none" :placeholder="__('users.Search Users')" />
            </div>
        </div>

        <div class="mb-5" x-data="{ isOpen: @if($openFilter || request('openFilter')) true @else false @endif }">
            <button type="button" @click="isOpen = !isOpen" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs leading-4 font-medium rounded-t text-grey-700 bg-gray-200 hover:bg-grey-300 dark:bg-gray-700 dark:text-gray-200 transition ease-in-out duration-150">
                <svg class="h-5 w-5 text-gray-500 dark:text-gray-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                {{ __('users.Advanced Search') }}
            </button>

            <button type="button" wire:click="resetFilters" @click="isOpen = false" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs leading-4 font-medium rounded text-grey-700 bg-gray-200 hover:bg-grey-300 dark:bg-gray-700 dark:text-gray-200 transition ease-in-out duration-150">
                <svg class="h-5 w-5 text-gray-500 dark:text-gray-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                {{ __('users.Reset form') }}
            </button>

            <div x-show="isOpen" x-cloak x-transition class="bg-gray-200 dark:bg-gray-700 rounded-b-md p-5" wire:ignore.self>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <x-form.input type="email" id="email" name="email" :label="__('users.Email')" wire:model.live="email" />
                    <x-form.daterange id="joined" name="joined" :label="__('users.Joined Date Range')" wire:model.blur="joined" />
                </div>
            </div>
        </div>

        <div class="overflow-x-scroll">
            <table>
            <thead>
            <tr>
                <th><a href="#" wire:click="sortBy('name')">{{ __('users.Name') }}</a></th>
                <th><a href="#" wire:click="sortBy('email')">{{ __('users.Email') }}</a></th>
                <th>{{ __('users.Joined') }}</th>
                <th>{{ __('users.Roles') }}</th>
                <th>{{ __('users.Action') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($this->users() as $user)
                <tr wire:key="{{ $user->id }}">
                    <td class="flex">
                        <div>
                            @if (storage_exists($user->image))
                                <img src="{{ storage_url($user->image) }}" alt="{{ $user->name }}" width="30" class="h-8 w-8 rounded-full">
                            @endif
                        </div>
                        <div class="pl-1 pt-1">{{ $user->name }}</div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if (! empty($user->invite_token))
                            <small class="dark:text-gray-300">{{ __('users.Invited by') }} {{ $user->invite->name }}<br> {{ date('jS M Y H:i', strtotime($user->invited_at)) }}</small>
                        @else
                            {{ $user->created_at !=='' ? date('jS M Y', strtotime($user->created_at)) : '' }}
                        @endif
                    </td>
                    <td>
                        @foreach($user->roles as $role)
                            <x-badge variant="blue">{{ $role->label }}</x-badge>
                        @endforeach
                    </td>
                    <td>
                        <div class="flex space-x-2">
                                @can('view_users_profiles')
                                    <x-a href="{{ route('admin.users.show', $user) }}" class="!transition-none">{{ __('users.Profile') }}</x-a>
                                @endcan

                                @if(can('edit_users'))
                                    <x-a href="{{ route('admin.users.edit', $user) }}" class="!transition-none">{{ __('admin.Edit') }}</x-a>
                                @elseif(auth()->id() === $user->id && can('edit_own_account'))
                                    <x-a href="{{ route('admin.users.edit', $user) }}" class="!transition-none">{{ __('admin.Edit') }}</x-a>
                                @endif

                                @if(can('add_users') && !empty($user->invite_token))
                                        <x-modal>
                                            <x-slot name="trigger">
                                                <a href="#" @click="on = true">{{ __('users.Resend Invite') }}</a>
                                            </x-slot>

                                            @if($sentEmail === false)
                                                <x-slot name="modalTitle">Send {{ $user->name }} {{ __('users.another invite email') }}.</x-slot>
                                                <x-slot name="content"></x-slot>
                                                <x-slot name="footer">
                                                    <x-button variant="gray" @click="on = false">{{ __('users.Cancel') }}</x-button>
                                                    <x-button wire:click="resendInvite('{{ $user->id }}')">{{ __('users.Yes, Send Email') }}</x-button>
                                                </x-slot>
                                            @else
                                                <x-slot name="modalTitle">{{ __('users.Invite email sent') }}</x-slot>
                                                <x-slot name="content"></x-slot>
                                                <x-slot name="footer">
                                                    <x-button variant="gray" @click="on = false">{{ __('users.Close') }}</x-button>
                                                </x-slot>
                                            @endif
                                        </x-modal>
                                @endif

                                @if(can('delete_users') && auth()->id() !== $user->id)
                                    <div x-data="{ confirmation: '' }" x-cloak class="inline-block !transition-none">
                                        <x-modal>
                                            <x-slot name="trigger">
                                                <a href="#" @click="on = true" class="text-red-600 hover:text-red-900 !transition-none">{{ __('admin.Delete') }}</a>
                                            </x-slot>

                                            <x-slot name="modalTitle">
                                                <div class="py-2 text-lg font-black uppercase tracking-tighter text-gray-900 dark:text-white whitespace-normal text-left">
                                                    {{ __('users.Are you sure you want to delete') }}: <span class="text-red-600">{{ $user->name }}</span>?
                                                </div>
                                            </x-slot>

                                            <x-slot name="content">
                                                <div class="space-y-4 text-left whitespace-normal">
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                                                        {{ __('users.Delete Warning') }}
                                                    </p>
                                                    <div class="flex flex-col gap-2">
                                                        <div class="text-xs text-gray-600 dark:text-gray-400">{{ __('users.Type the name') }} <span class="font-bold text-red-600">"{{ $user->name }}"</span> {{ __('users.to confirm') }}</div>
                                                        <input x-model="confirmation" class="px-3 py-2 text-sm border border-slate-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white w-full focus:ring-2 focus:ring-red-500 outline-none transition-all">
                                                    </div>
                                                </div>
                                            </x-slot>

                                            <x-slot name="footer">
                                                <x-button variant="gray" @click="on = false">{{ __('admin.Cancel') }}</x-button>
                                                <x-button variant="red" x-bind:disabled="confirmation !== '{{ $user->name }}'" wire:click="deleteUser('{{ $user->id }}')">
                                                    {{ __('admin.Delete') }}
                                                </x-button>
                                            </x-slot>
                                        </x-modal>
                                    </div>
                                @endif
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $this->users()->links() }}
        </div>
    </div>
</div>
