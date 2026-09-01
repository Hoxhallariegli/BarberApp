<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FirebaseSettings extends Component
{
    public string $firebaseCredentials = '';
    public bool $isFirebaseEnabled = false;
    public string $firebaseProjectId = '';
    public string $firebaseWebConfig = '';
    public bool $isFirebaseDebugEnabled = false;
    public string $browserToken = ''; // Ruajmë tokenin e browserit për test

    protected $listeners = ['fcm-token-received' => 'setBrowserToken'];

    public function setBrowserToken($token): void
    {
        $this->browserToken = $token;
    }

    public function mount(): void
    {
        $this->firebaseCredentials = Setting::where('key', 'firebase_credentials')->value('value') ?? '';
        $this->isFirebaseEnabled = (bool) Setting::where('key', 'firebase_enabled')->value('value');
        $this->firebaseProjectId = Setting::where('key', 'firebase_project_id')->value('value') ?? '';
        $this->firebaseWebConfig = Setting::where('key', 'firebase_web_config')->value('value') ?? '';
        $this->isFirebaseDebugEnabled = (bool) Setting::where('key', 'firebase_debug')->value('value');
    }

    public function render(): View
    {
        return view('livewire.admin.settings.firebase-settings');
    }

    public function update(): void
    {
        Setting::updateOrCreate(['key' => 'firebase_credentials'], ['value' => $this->firebaseCredentials]);
        Setting::updateOrCreate(['key' => 'firebase_enabled'], ['value' => $this->isFirebaseEnabled]);
        Setting::updateOrCreate(['key' => 'firebase_project_id'], ['value' => $this->firebaseProjectId]);
        Setting::updateOrCreate(['key' => 'firebase_web_config'], ['value' => $this->firebaseWebConfig]);
        Setting::updateOrCreate(['key' => 'firebase_debug'], ['value' => $this->isFirebaseDebugEnabled]);

        \Illuminate\Support\Facades\Cache::forget('settings');

        add_user_log([
            'title' => 'updated firebase settings',
            'link' => route('admin.settings'),
            'reference_id' => auth()->id(),
            'section' => 'Settings',
            'type' => 'Update',
        ]);

        $this->dispatch('toast', ['message' => __('settings.updated'), 'type' => 'success']);
    }

    public function testNotification(\App\Services\FirebaseService $service): void
    {
        // Dërgojmë te tokeni specifik nese e kemi, perndryshe te 'all' topic
        $target = $this->browserToken ?: 'all';

        $sent = $service->sendNotification(
            'Test Notification',
            'If you see this, Firebase is working correctly! 🍎🚀',
            $target
        );

        if ($sent) {
            $this->dispatch('toast', ['message' => 'Test notification sent! Target: ' . ($this->browserToken ? 'Device' : 'Topic'), 'type' => 'success']);
        } else {
            $this->dispatch('toast', ['message' => 'Failed to send notification. Check logs.', 'type' => 'error']);
        }
    }
}
