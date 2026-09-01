<div>
    <div class="card">
        <h3 class="mb-5">{{ __('settings.Office lockdown by IP Address') }}</h3>

        <div class="bg-primary mb-5 p-2 text-gray-100 rounded">
            {{ __("settings.When a user is set to office login only the IP's listed below will allow access.") }}
            {{ __("settings.If you are not in the office you will not be able to login.") }}
        </div>

        <p>{{ __('settings.Your current IP address is') }} {{ request()->ip() }}</p>

        <x-form wire:submit="update" method="put">

        <table>
            <tr>
                <th>{{ __('settings.IP Address') }}</th>
                <th>{{ __('settings.Comment') }}</th>
                <th></th>
            </tr>
            @foreach($ips as $index => $row)
                @error("ips.$index.ip")
                    <tr wire:key="{{ $index }}">
                        <td colspan="3">
                            <span class="error">{{ $message }}</span>
                        </td>
                    </tr>
                @enderror
                <tr>
                    <td><x-form.input wire:model="ips.{{ $index }}.ip" label="none">{{ $row['ip'] }}</x-form.input></td>
                    <td><x-form.input wire:model="ips.{{ $index }}.comment" label="none">{{ $row['comment'] }}</x-form.input></td>
                    <td class="flex justify-center pt-2"><button type="button" wire:click="remove({{ $index }})" wire:confirm="{{ __('users.Are you sure?') }}" class="error">X</button></td>
                </tr>
            @endforeach
        </table>

        <p>
            <x-button type="button" variant="secondary" size="sm" wire:click="add">
                <x-heroicon-s-plus class="size-5" />
                {{ __('settings.Add Row') }}
            </x-button>
        </p>

        <p>
            <x-button>
                {{ __('settings.Save') }}
            </x-button>
        </p>

        </x-form>

        @include('errors.messages')

    </div>
</div>
