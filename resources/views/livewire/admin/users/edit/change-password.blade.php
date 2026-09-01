<div>
    <x-2col>
        <x-slot name="left">
            <h3>{{ __('users.Change Password') }}</h3>
            <p>{{ __('users.Ensure your account is using a long, random password to stay secure.') }}</p>
            <p>{{ __('users.Use a password manager, we recommend using 1Password for creating and storing passwords or') }} <a href="https://1password.com/password-generator/" target="blank">1password.com/password-generator</a></p>
        </x-slot>
        <x-slot name="right">

            <div class="card">
                <x-form wire:submit="update" method="put">

                    <x-alert class="text-white">
                        <div>{{ __('users.New password must be at least 8 characters in length') }}<br>
                        {{ __('users.at least one lowercase letter') }}<br>
                        {{ __('users.at least one uppercase letter') }}<br>
                        {{ __('users.at least one digit') }}</div>
                    </x-alert>

                    <x-form.input wire:model="newPassword" type="password" :label="__('users.New Password')" name='newPassword' />
                    <x-form.input wire:model="confirmPassword" type="password" :label="__('users.Confirm Password')" name='confirmPassword' />

                    <x-button>{{ __('users.Change Password') }}</x-button>

                    @include('errors.messages')

                </x-form>
            </div>

        </x-slot>
    </x-2col>
</div>
