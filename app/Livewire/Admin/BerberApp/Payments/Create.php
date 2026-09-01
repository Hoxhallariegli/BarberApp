<?php

namespace App\Livewire\Admin\BerberApp\Payments;

use App\Models\BerberApp\Payment;
use App\Domain\BerberApp\Payment\DTOs\PaymentDTO;
use App\Domain\BerberApp\Payment\Actions\CreatePaymentAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class Create extends Component
{
        use WithPagination;
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

    public function render() {
        abort_if_cannot('add_payments');
        return view('livewire.admin.berber-app.payments.create', [
            'bookings' => $this->getbookingsList(),
        ])->layout('components.layouts.app')->title(__('payments.Add Payment'));
    }
    public function store(CreatePaymentAction $action) { $this->validate();  $dto = PaymentDTO::fromArray([
            'booking_id' => $this->booking_id,
            'amount' => $this->amount,
            'status' => $this->status,
        ]); $action->execute($dto); session()->flash('success', __('payments.created')); return to_route('admin.payments.index'); }
    protected function rules(): array { return Payment::rules(); }
}
