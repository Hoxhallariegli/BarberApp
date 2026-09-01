<?php

namespace App\Livewire\Admin\BerberApp\Reminders;

use App\Models\BerberApp\Reminder;
use App\Domain\BerberApp\Reminder\DTOs\ReminderDTO;
use App\Domain\BerberApp\Reminder\Actions\CreateReminderAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class Create extends Component
{
        use WithPagination;
     public $booking_id = '';
    public $reminder_type = '';
    public $sent_at = '';

    #[On('booking-created')]
    public function refreshBookings($id) { $this->booking_id = $id; $this->updatedBookingId($id); }

    public function updatedBookingId($value)
    {
        if (!$value) return;
        $related = \App\Models\BerberApp\Booking::find($value);
        if (!$related) return;
    }

    protected function getbookingsList() {
        return \App\Models\BerberApp\Booking::pluck('id', 'id')->toArray();
    }

    public function render() {
        abort_if_cannot('add_reminders');
        return view('livewire.admin.berber-app.reminders.create', [
            'bookings' => $this->getbookingsList(),
        ])->layout('components.layouts.app')->title(__('reminders.Add Reminder'));
    }
    public function store(CreateReminderAction $action) { $this->validate();  $dto = ReminderDTO::fromArray([
            'booking_id' => $this->booking_id,
            'reminder_type' => $this->reminder_type,
            'sent_at' => $this->sent_at,
        ]); $action->execute($dto); session()->flash('success', __('reminders.created')); return to_route('admin.reminders.index'); }
    protected function rules(): array { return Reminder::rules(); }
}
