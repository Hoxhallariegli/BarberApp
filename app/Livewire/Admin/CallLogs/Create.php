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

#[Title('Add CallLog')]
class Create extends Component
{
        use WithPagination;
     public $phone_number = '';
    public $caller_name = '';
    public $type = '';
    public $call_time = '';
    public $is_client = '';
   
    public function render() { abort_if_cannot('add_call_logs'); return view('livewire.admin.call-logs.create', [
        ])->layout('components.layouts.app'); }
    public function store(CreateCallLogAction $action) { $this->validate();  $dto = CallLogDTO::fromArray([
            'phone_number' => $this->phone_number,
            'caller_name' => $this->caller_name,
            'type' => $this->type,
            'call_time' => $this->call_time,
            'is_client' => $this->is_client,
        ]); $action->execute($dto); session()->flash('success', __('call-logs.created')); return to_route('admin.call-logs.index'); }
    protected function rules(): array { return CallLog::rules(); }
}