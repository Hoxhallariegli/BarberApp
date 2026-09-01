<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Sms;

use App\Models\Setting;
use App\Models\SmsDevice;
use App\Services\SmsService;
use App\Services\FirebaseService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;

class SmsSettings extends Component
{
    public string $testPhone = '';
    public string $testMessage = '';
    public bool $isFirebaseEnabled = false;
    public string $firebaseProjectId = '';

    public function mount(): void
    {
        $this->isFirebaseEnabled = (bool) Setting::where('key', 'firebase_enabled')->value('value');
        $this->firebaseProjectId = Setting::where('key', 'firebase_project_id')->value('value') ?? '';
    }

    public function render(): View
    {
        $activeDevice = SmsDevice::where('is_active', true)->first();

        return view('livewire.admin.sms.sms-settings', [
            'activeDevice' => $activeDevice,
        ])->layout('components.layouts.app');
    }

    public function sendTestSms(SmsService $smsService): void
    {
        $this->validate([
            'testPhone' => 'required',
            'testMessage' => 'required',
        ]);

        $sent = $smsService->send($this->testPhone, $this->testMessage, 'test');

        if ($sent) {
            $this->dispatch('toast', ['message' => __('SMS sent successfully!'), 'type' => 'success']);
            $this->testMessage = '';
        } else {
            $this->dispatch('toast', ['message' => __('Failed to send SMS. Check device status.'), 'type' => 'error']);
        }
    }

    public function triggerReminders(): void
    {
        Artisan::call('sms:reminders');

        $this->dispatch('toast', ['message' => __('Reminder command triggered!'), 'type' => 'success']);
    }
}
