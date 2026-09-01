<div>
    <x-2col>
        <x-slot name="left">
            <h3>{{ __('users.Two Factor Authentication') }}</h3>

            @if (auth()->user()->two_fa_active === false)
                <p>{{ __('users.Add additional security to your account using two-factor authentication.') }}</p>

                <p><b>{{ __('users.Why do I need this?') }}</b></p>

                <p>{{ __('users.Passwords can get stolen – especially if you use the same password for multiple sites.') }}
                    {{ __('users.Adding Two-Step Verification means that even if your password gets stolen, your account will remain secure.') }}</p>

                    <p><b>{{ __('users.How does it work?') }}</b></p>

                    <p>{{ __("users.After you turn on Two-Step Verification for your account, signing in will be a little different: You will enter your password, as usual.") }}</p>

                    <p>{{ __('users.Next open your Authenticator app and copy the code number into the form and submit.') }}</p>
                @endif
            </x-slot>
            <x-slot name="right">

                <div class="card">

                    @if (auth()->user()->two_fa_active === true && auth()->user()->two_fa_secret_key !='')
                        <p>{{ __('users.Your 2-Factor Authentication is in place, to remove this click the button below.') }}</p>
                        <x-button wire:click="remove">{{ __('users.Turn off 2FA') }}</x-button>
                    @else
                        <p>{{ __('users.Authenticator apps generate random codes that you can use to sign in. They do not have access to your password or account information.') }}</p>
                        <p>{{ __('users.1Password is a good authenticator app as is Authy.') }}</p>

                        <p><img src='{{ $inlineUrl }}' alt='code'></p>

                    <p>{{ __('users.Scan bar code in your authenticator app or manually enter this key') }} {{ $secretKey }}</p>

                    <x-form.input wire:model="code" :label="__('users.Code')" name='code' />

                    <x-form wire:submit="update" method="put">

                    <x-button>{{ __('users.Turn on 2FA') }}</x-button>

                @endif

                @include('errors.messages')

                </x-form>
            </div>

        </x-slot>
    </x-2col>
</div>
