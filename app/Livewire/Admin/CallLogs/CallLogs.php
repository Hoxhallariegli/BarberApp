<?php

namespace App\Livewire\Admin\CallLogs;

use App\Models\CallLog;
use App\Domain\CallLog\Queries\CallLogListQuery;
use App\Domain\CallLog\Actions\DeleteCallLogAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('CallLogs')]
class CallLogs extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    public string $testPhone = '';
    public bool $consentConfirmed = false;
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = true;

    public function testCall(\App\Services\FirebaseService $firebase): void
    {
        $this->validate([
            'testPhone' => 'required|string',
            'consentConfirmed' => 'accepted',
        ]);

        $job = \App\Models\CallJob::create([
            'phone_number' => $this->testPhone,
            'status' => 'pending',
        ]);

        $device = \App\Models\SmsDevice::where('is_active', true)->first();
        if ($device) {
            $firebase->sendNotification(
                "New Call Task",
                "Initiating call to {$job->phone_number}",
                $device->fcm_token,
                [
                    'action' => 'START_CALL',
                    'phone' => $job->phone_number,
                    'job_id' => (string)$job->id,
                ]
            );
            $this->dispatch('toast', ['message' => 'Call command sent to device!', 'type' => 'success']);
            $this->testPhone = '';
            $this->consentConfirmed = false;
        } else {
            $this->dispatch('toast', ['message' => 'No active device found.', 'type' => 'error']);
        }
    }

    public function resetFilters() { $this->reset(['search', 'openFilter', ]); $this->resetPage(); }

    public function render()
    {
        abort_if_cannot('view_call_logs');
        $query = (new CallLogListQuery())->handle(['search' => $this->search, ], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.call-logs.index', [
            'items' => $query->paginate($this->paginate),
            'sortableFields' => CallLog::sortable(),
        ])->layout('components.layouts.app');
    }

    public function sortBy($field) { if (!in_array($field, CallLog::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteCallLog($id, DeleteCallLogAction $action)
    {
        abort_if_cannot('delete_call_logs');
        $item = CallLog::find($id);
        if (!$item) { $this->dispatch('toast', message: __('call-logs.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('call-logs.deleted'), type: 'success'); $this->resetPage(); }
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('call-logs.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('call-logs.delete_error'), type: 'error'); }
    }
}
