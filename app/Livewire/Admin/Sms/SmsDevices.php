<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Sms;

use App\Models\SmsDevice;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class SmsDevices extends Component
{
    public function render(): View
    {
        return view('livewire.admin.sms.sms-devices', [
            'devices' => SmsDevice::latest()->get(),
        ])->layout('components.layouts.app');
    }

    public function toggleActive(int $id): void
    {
        $device = SmsDevice::findOrFail($id);

        // If we are activating this one, we might want to deactivate others
        // for simplicity, we allow only one active device for now in the service
        if (!$device->is_active) {
            SmsDevice::where('id', '!=', $id)->update(['is_active' => false]);
        }

        $device->update(['is_active' => !$device->is_active]);

        $this->dispatch('toast', ['message' => __('Device status updated!'), 'type' => 'success']);
    }

    public function delete(int $id): void
    {
        SmsDevice::findOrFail($id)->delete();
        $this->dispatch('toast', ['message' => __('Device removed.'), 'type' => 'success']);
    }
}
