<?php

namespace App\Livewire\Admin\CallLogs;

use App\Models\CallLog;
use App\Domain\CallLog\DTOs\CallLogDTO;
use App\Domain\CallLog\Actions\CreateCallLogAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class QuickCreate extends Component
{
        use WithPagination;
     public $phone_number = '';
    public $caller_name = '';
    public $type = '';
    public $call_time = '';
    public $is_client = '';
   
    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.call-logs.quick-create', [
        ]); }

    public function store(CreateCallLogAction $action)
    {
        $this->validate();
        $dto = CallLogDTO::fromArray([
            'phone_number' => $this->phone_number,
            'caller_name' => $this->caller_name,
            'type' => $this->type,
            'call_time' => $this->call_time,
            'is_client' => $this->is_client,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('call-log-created', id: $item->id);
        $this->js("Livewire.dispatch('call-log-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('call-logs.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['phone_number', 'caller_name', 'type', 'call_time', 'is_client']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { return CallLog::rules(); }
}