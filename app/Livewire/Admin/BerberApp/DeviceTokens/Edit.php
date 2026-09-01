<?php

namespace App\Livewire\Admin\BerberApp\DeviceTokens;

use App\Models\BerberApp\DeviceToken;
use App\Domain\BerberApp\DeviceToken\DTOs\DeviceTokenDTO;
use App\Domain\BerberApp\DeviceToken\Actions\UpdateDeviceTokenAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit DeviceToken')]
class Edit extends Component
{
        use WithPagination;
 public DeviceToken $item;
    public $user_id = '';
    public $fcm_token = '';
    public $device_type = '';

    public function mount(DeviceToken $deviceToken) { $this->item = $deviceToken; $this->fill($deviceToken->toArray());  }
    public function render() {
        abort_if_cannot('edit_device_tokens');
        return view('livewire.admin.berber-app.device-tokens.edit', [
        ])->layout('components.layouts.app');
    }
    public function update(UpdateDeviceTokenAction $action) { $this->validate();  $dto = DeviceTokenDTO::fromArray([
            'user_id' => $this->user_id,
            'fcm_token' => $this->fcm_token,
            'device_type' => $this->device_type,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('device-tokens.updated')); return to_route('admin.device-tokens.index'); }
    protected function rules(): array { return DeviceToken::rules($this->item->id); }
}
