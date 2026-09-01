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

class QuickCreate extends Component
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

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.berber-app.payments.quick-create', [
            'bookings' => $this->getbookingsList(),
        ]); }

    public function store(CreatePaymentAction $action)
    {
        $this->validate();
        $dto = PaymentDTO::fromArray([
            'booking_id' => $this->booking_id,
            'amount' => $this->amount,
            'status' => $this->status,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('payment-created', id: $item->id);
        $this->js("Livewire.dispatch('payment-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('payments.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['booking_id', 'amount', 'status']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { return Payment::rules(); }
}
