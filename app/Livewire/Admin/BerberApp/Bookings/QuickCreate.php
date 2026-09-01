<?php

namespace App\Livewire\Admin\BerberApp\Bookings;

use App\Models\BerberApp\Booking;
use App\Domain\BerberApp\Booking\DTOs\BookingDTO;
use App\Domain\BerberApp\Booking\Actions\CreateBookingAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class QuickCreate extends Component
{
        use WithPagination;
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

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.berber-app.bookings.quick-create', [
            'customers' => $this->getcustomersList(),
            'barbers' => $this->getbarbersList(),
            'services' => $this->getservicesList(),
        ]); }

    public function store(CreateBookingAction $action)
    {
        $this->validate();
        $dto = BookingDTO::fromArray([
            'customer_id' => $this->customer_id,
            'barber_id' => $this->barber_id,
            'service_id' => $this->service_id,
            'appointment_datetime' => $this->appointment_datetime,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('booking-created', id: $item->id);
        $this->js("Livewire.dispatch('booking-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('bookings.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['customer_id', 'barber_id', 'service_id', 'appointment_datetime']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { return Booking::rules(); }
}
