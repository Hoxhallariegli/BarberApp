<?php

namespace App\Livewire\Admin\BerberApp\DeviceTokens;

use App\Models\BerberApp\DeviceToken;
use App\Domain\BerberApp\DeviceToken\DTOs\DeviceTokenDTO;
use App\Domain\BerberApp\DeviceToken\Actions\CreateDeviceTokenAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Add DeviceToken')]
class Create extends Component
{
        use WithPagination;
     public $user_id = '';
    public $fcm_token = '';
    public $device_type = '';

    public function render() {
        abort_if_cannot('add_device_tokens');
        return view('livewire.admin.berber-app.device-tokens.create', [
        ])->layout('components.layouts.app');
    }
    public function store(CreateDeviceTokenAction $action) { $this->validate();  $dto = DeviceTokenDTO::fromArray([
            'user_id' => $this->user_id,
            'fcm_token' => $this->fcm_token,
            'device_type' => $this->device_type,
        ]); $action->execute($dto); session()->flash('success', __('device-tokens.created')); return to_route('admin.device-tokens.index'); }
    protected function rules(): array { return DeviceToken::rules(); }
}
