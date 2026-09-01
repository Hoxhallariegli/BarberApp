<?php

namespace App\Livewire\Admin\BerberApp\Payments;

use App\Models\BerberApp\Payment;
use App\Domain\BerberApp\Payment\DTOs\PaymentDTO;
use App\Domain\BerberApp\Payment\Actions\UpdatePaymentAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit Payment')]
class Edit extends Component
{
        use WithPagination;
 public Payment $item;
    public $booking_id = '';
    public $amount = '';
    public $status = '';

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

    public function mount(Payment $payment) { $this->item = $payment; $this->fill($payment->toArray());  }
    public function render() {
        abort_if_cannot('edit_payments');
        return view('livewire.admin.berber-app.payments.edit', [
            'bookings' => $this->getbookingsList(),
        ])->layout('components.layouts.app');
    }
    public function update(UpdatePaymentAction $action) { $this->validate();  $dto = PaymentDTO::fromArray([
            'booking_id' => $this->booking_id,
            'amount' => $this->amount,
            'status' => $this->status,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('payments.updated')); return to_route('admin.payments.index'); }
    protected function rules(): array { return Payment::rules($this->item->id); }
}
