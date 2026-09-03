<?php

namespace App\Livewire\Admin\BerberApp\Reminders;

use App\Models\BerberApp\Reminder;
use App\Domain\BerberApp\Reminder\Queries\ReminderListQuery;
use App\Domain\BerberApp\Reminder\Actions\DeleteReminderAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class Reminders extends Component
{
        use WithPagination;

    public int $paginate = 10;
    #[Url(history: true)] public string $search = '';
    #[Url(history: true)] public $booking_id = '';
    public bool $openFilter = false;
    public string $sortField = 'id';
    public bool $sortAsc = false;

    public function resetFilters() { $this->reset(['search', 'openFilter', 'booking_id', ]); $this->resetPage(); }

    public function render()
    {
        abort_if_cannot('view_reminders');
        $query = (new ReminderListQuery())->handle(['search' => $this->search,             'booking_id' => $this->booking_id,
], $this->sortField, $this->sortAsc ? 'asc' : 'desc');

        return view('livewire.admin.berber-app.reminders.index', [
            'items' => $query->with(['booking.barber', 'booking.customer', 'booking.service'])->paginate($this->paginate),
            'sortableFields' => Reminder::sortable(),
            'bookings' => \App\Models\BerberApp\Booking::pluck('id', 'id')->toArray(),
        ])->layout('components.layouts.app')->title(__('reminders.Reminders'));
    }

    public function sortBy($field) { if (!in_array($field, Reminder::sortable(), true)) return; if ($this->sortField === $field) { $this->sortAsc = ! $this->sortAsc; } $this->sortField = $field; }

    public function deleteReminder($id, DeleteReminderAction $action)
    {
        abort_if_cannot('delete_reminders');
        $item = Reminder::find($id);
        if (!$item) { $this->dispatch('toast', message: __('reminders.not_found'), type: 'error'); return; }
        try { $action->execute($item); $this->dispatch('toast', message: __('reminders.deleted'), type: 'success'); $this->resetPage(); }
        catch (\Illuminate\Database\QueryException $e) { $this->dispatch('toast', message: __('reminders.delete_error_referenced'), type: 'error'); }
        catch (\Exception $e) { $this->dispatch('toast', message: __('reminders.delete_error'), type: 'error'); }
    }
}
