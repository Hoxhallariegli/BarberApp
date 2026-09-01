<?php

namespace App\Livewire\Admin\BerberApp\Bookings;

use App\Models\BerberApp\Booking;
use App\Domain\BerberApp\Booking\DTOs\BookingDTO;
use App\Domain\BerberApp\Booking\Actions\UpdateBookingAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Edit Booking')]
class Edit extends Component
{
        use WithPagination;
 public Booking $item;
    public $customer_id = '';
    public $barber_id = '';
    public $service_id = '';
    public $appointment_datetime = '';

    #[On('customer-created')]
    public function refreshCustomers($id) { $this->customer_id = $id; $this->updatedCustomerId($id); }

    #[On('barber-created')]
    public function refreshBarbers($id) { $this->barber_id = $id; $this->updatedBarberId($id); }

    #[On('service-created')]
    public function refreshServices($id) { $this->service_id = $id; $this->updatedServiceId($id); }

    public function updatedCustomerId($value)
    {
        if (!$value) return;
        $related = \App\Models\BerberApp\Customer::find($value);
        if (!$related) return;
    }

    public function updatedBarberId($value)
    {
        if (!$value) return;
        $related = \App\Models\BerberApp\Barber::find($value);
        if (!$related) return;
    }

    public function updatedServiceId($value)
    {
        if (!$value) return;
        $related = \App\Models\BerberApp\Service::find($value);
        if (!$related) return;
    }

    protected function getcustomersList() {
        return \App\Models\BerberApp\Customer::pluck('name', 'id')->toArray();
    }

    protected function getbarbersList() {
        return \App\Models\BerberApp\Barber::pluck('name', 'id')->toArray();
    }

    protected function getservicesList() {
        return \App\Models\BerberApp\Service::pluck('name', 'id')->toArray();
    }

    public function mount(Booking $booking) { $this->item = $booking; $this->fill($booking->toArray()); $this->appointment_datetime = $booking->appointment_datetime?->format('Y-m-d\TH:i'); }
    public function render() {
        abort_if_cannot('edit_bookings');
        return view('livewire.admin.berber-app.bookings.edit', [
            'customers' => $this->getcustomersList(),
            'barbers' => $this->getbarbersList(),
            'services' => $this->getservicesList(),
        ])->layout('components.layouts.app');
    }
    public function update(UpdateBookingAction $action) { $this->validate();  $dto = BookingDTO::fromArray([
            'customer_id' => $this->customer_id,
            'barber_id' => $this->barber_id,
            'service_id' => $this->service_id,
            'appointment_datetime' => $this->appointment_datetime,
        ]); $action->execute($this->item, $dto); session()->flash('success', __('bookings.updated')); return to_route('admin.bookings.index'); }
    protected function rules(): array { return Booking::rules($this->item->id); }
}
