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

class QuickCreate extends Component
{
        use WithPagination;
     public $user_id = '';
    public $fcm_token = '';
    public $device_type = '';

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.berber-app.device-tokens.quick-create', [
        ]); }

    public function store(CreateDeviceTokenAction $action)
    {
        $this->validate();
        $dto = DeviceTokenDTO::fromArray([
            'user_id' => $this->user_id,
            'fcm_token' => $this->fcm_token,
            'device_type' => $this->device_type,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('device-token-created', id: $item->id);
        $this->js("Livewire.dispatch('device-token-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('device-tokens.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['user_id', 'fcm_token', 'device_type']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { return DeviceToken::rules(); }
}
