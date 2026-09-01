<?php

namespace App\Livewire\Admin\BerberApp\Reminders;

use App\Models\BerberApp\Reminder;
use App\Domain\BerberApp\Reminder\DTOs\ReminderDTO;
use App\Domain\BerberApp\Reminder\Actions\UpdateReminderAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit Reminder')]
class Edit extends Component
{
        use WithPagination;
 public Reminder $item;
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

    public function mount(Reminder $reminder) { $this->item = $reminder; $this->fill($reminder->toArray()); $this->sent_at = $reminder->sent_at?->format('Y-m-d\TH:i'); }
    public function render() {
        abort_if_cannot('edit_reminders');
        return view('livewire.admin.berber-app.reminders.edit', [
            'bookings' => $this->getbookingsList(),
        ])->layout('components.layouts.app');
    }
    public function update(UpdateReminderAction $action) { $this->validate();  $dto = ReminderDTO::fromArray([
            'booking_id' => $this->booking_id,
            'reminder_type' => $this->reminder_type,
            'sent_at' => $this->sent_at,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('reminders.updated')); return to_route('admin.reminders.index'); }
    protected function rules(): array { return Reminder::rules($this->item->id); }
}
