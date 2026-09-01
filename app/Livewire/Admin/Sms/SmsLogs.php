<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Sms;

use App\Models\SmsLog;
use App\Services\SmsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class SmsLogs extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public int $perPage = 15;

    public function render(): View
    {
        $query = SmsLog::query()
            ->when($this->search, function ($q) {
                $q->where('phone_number', 'like', '%' . $this->search . '%')
                  ->orWhere('body', 'like', '%' . $this->search . '%');
            })
            ->when($this->status, function ($q) {
                $q->where('status', $this->status);
            })
            ->latest();

        return view('livewire.admin.sms.sms-logs', [
            'logs' => $query->paginate($this->perPage),
        ])->layout('components.layouts.app');
    }

    public function retry(int $id, SmsService $smsService): void
    {
        $log = SmsLog::findOrFail($id);

        $sent = $smsService->dispatchToGateway($log);

        if ($sent) {
            $this->dispatch('toast', ['message' => __('Message retried successfully!'), 'type' => 'success']);
        } else {
            $this->dispatch('toast', ['message' => __('Retry failed. Check gateway.'), 'type' => 'error']);
        }
    }
}
