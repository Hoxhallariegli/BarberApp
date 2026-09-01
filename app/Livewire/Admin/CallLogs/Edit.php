<?php

namespace App\Livewire\Admin\CallLogs;

use App\Models\CallLog;
use App\Domain\CallLog\DTOs\CallLogDTO;
use App\Domain\CallLog\Actions\UpdateCallLogAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit CallLog')]
class Edit extends Component
{
        use WithPagination;
 public CallLog $item;
    public $phone_number = '';
    public $caller_name = '';
    public $type = '';
    public $call_time = '';
    public $is_client = '';
   
    public function mount(CallLog $callLog) { $this->item = $callLog; $this->fill($callLog->toArray()); $this->call_time = $callLog->call_time?->format('Y-m-d\TH:i'); }
    public function render() { abort_if_cannot('edit_call_logs'); return view('livewire.admin.call-logs.edit', [
        ])->layout('components.layouts.app'); }
    public function update(UpdateCallLogAction $action) { $this->validate();  $dto = CallLogDTO::fromArray([
            'phone_number' => $this->phone_number,
            'caller_name' => $this->caller_name,
            'type' => $this->type,
            'call_time' => $this->call_time,
            'is_client' => $this->is_client,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('call-logs.updated')); return to_route('admin.call-logs.index'); }
    protected function rules(): array { return CallLog::rules($this->item->id); }
}