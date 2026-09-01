<div>
    <div class="card">

        <div class="flex justify-between">
            <h2 class="mb-5">{{ __('users.Account Settings') }}</h2>
            <div>
                <span class="error">*</span>
                <span class="dark:text-gray-200"> = {{ __('users.required') }}</span>
            </div>
        </div>

        <x-form wire:submit="update" method="put">

            <x-form.input wire:model="name" :label="__('users.Name')" name='name' required />
            <x-form.input wire:model="email" :label="__('users.Email')" name='email' required />
            <x-form.input wire:model="image" type="file" :label="__('users.Image')" name='image' />
            @if ($image)
                {{ __('users.Photo Preview') }}:
                <img src="{{ $image->temporaryUrl() }}" width="100px" class="mb-5">
            @elseif(storage_exists($user->image))
                <img src="{{ storage_url($user->image) }}" width="100px" class="mb-5">
            @endif

            <x-button>{{ __('users.Update Profile') }}</x-button>

            @include('errors.messages')

        </x-form>

    </div>
</div>
