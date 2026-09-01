<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use Livewire\Component;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Title;

#[Title('Notification Settings')]
class NotificationSettings extends Component
{
    public array $modules = [];
    public array $activeNotifications = [];

    public function mount()
    {
        // Gjejmë të gjithë modulet e krijuara te app/Domain
        $domainPath = app_path('Domain');
        if (File::exists($domainPath)) {
            foreach (File::directories($domainPath) as $dir) {
                $name = basename($dir);
                if ($name !== 'Shared') {
                    $this->modules[] = $name;
                    $this->activeNotifications[$name] = (bool) Setting::where('key', "notify_firebase_$name")->value('value');
                }
            }
        }
    }

    public function toggleNotification($module)
    {
        $newValue = !($this->activeNotifications[$module] ?? false);
        $this->activeNotifications[$module] = $newValue;

        Setting::updateOrCreate(
            ['key' => "notify_firebase_$module"],
            ['value' => $newValue]
        );

        $this->dispatch('toast', ['message' => "Notifications for $module updated!", 'type' => 'success']);
    }

    public function render()
    {
        return view('livewire.admin.settings.notification-settings')->layout('components.layouts.app');
    }
}
