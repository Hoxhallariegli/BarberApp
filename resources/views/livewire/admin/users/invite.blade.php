<x-modal>
    <x-slot name="trigger">
        <x-button @click="on = true">{{ __('users.Add User') }}</x-button>
    </x-slot>

    <x-slot name="modalTitle">{{ __('users.Add User') }}</x-slot>

    <x-slot name="content">

        @include('errors.success')

        <x-form.input tabindex="1" wire:model="name" :label="__('users.Name')" name="name" required />
        <x-form.input tabindex="3" wire:model="email" :label="__('users.Email')" name="email" required />

        <p class="font-bold">{{ __('roles.Roles') }}</p>

        @error('rolesSelected')
            <p class="error">{{ $message }}</p>
        @enderror

        @foreach($roles as $role)
            <x-form.checkbox
                wire:model="rolesSelected"
                id="{{ $role->id }}"
                value="{{ $role->id }}"
                label="{{ $role->label }}"
            />
        @endforeach

    </x-slot>

    <x-slot name="footer">
        <x-button variant="gray" @click="on = false">{{ __('users.Close') }}</x-button>
        <x-button wire:click="store">{{ __('users.Resend Invite') }}</x-button>
    </x-slot>

</x-modal>
