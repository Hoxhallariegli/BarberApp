<?php

namespace App\Livewire\Admin\BerberApp\Bookings;

use App\Models\BerberApp\Booking;
use App\Models\BerberApp\Barber;
use App\Models\BerberApp\Service;
use App\Domain\BerberApp\Booking\DTOs\BookingDTO;
use App\Domain\BerberApp\Booking\Actions\CreateBookingAction;
use App\Services\AvailabilityService;
use Carbon\Carbon;
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
    public $selectedDate = '';
    public $selectedTime = '';

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
    }

    #[On('customer-created')]
    public function refreshCustomers($id) { $this->customer_id = $id; }

    #[On('barber-created')]
    public function refreshBarbers($id) { $this->barber_id = $id; }

    #[On('service-created')]
    public function refreshServices($id) { $this->service_id = $id; }

    public function updatedBarberId() { $this->selectedTime = ''; }
    public function updatedServiceId() { $this->selectedTime = ''; }
    public function updatedSelectedDate() { $this->selectedTime = ''; }

    public function getAvailableSlotsProperty()
    {
        if (!$this->selectedDate || !$this->service_id || !$this->barber_id) return [];

        $service = Service::find($this->service_id);
        $barber = Barber::find($this->barber_id);

        if (!$service || !$barber) return [];

        $availabilityService = app(AvailabilityService::class);
        return $availabilityService->getAvailableSlots($barber, Carbon::parse($this->selectedDate), $service->duration_minutes ?: 30);
    }

    protected function getcustomersList() {
        return \App\Models\BerberApp\Customer::pluck('name', 'id')->toArray();
    }

    protected function getbarbersList() {
        return Barber::pluck('name', 'id')->toArray();
    }

    protected function getservicesList() {
        return Service::pluck('name', 'id')->toArray();
    }

    public function render() {
        return view('livewire.admin.berber-app.bookings.quick-create', [
            'customers' => $this->getcustomersList(),
            'barbers' => $this->getbarbersList(),
            'services' => $this->getservicesList(),
            'availableSlots' => $this->availableSlots,
        ]);
    }

    public function store(CreateBookingAction $action)
    {
        $this->validate([
            'customer_id' => 'required',
            'barber_id' => 'required',
            'service_id' => 'required',
            'selectedDate' => 'required|date',
            'selectedTime' => 'required',
        ]);

        $dto = BookingDTO::fromArray([
            'customer_id' => $this->customer_id,
            'barber_id' => $this->barber_id,
            'service_id' => $this->service_id,
            'appointment_datetime' => Carbon::parse($this->selectedDate . ' ' . $this->selectedTime)->toDateTimeString(),
            'status' => 'confirmed',
            'locale' => app()->getLocale(),
            'reminder_enabled' => true,
        ]);

        $item = $action->execute($dto);
        $this->dispatch('booking-created', id: $item->id);
        $this->js("Livewire.dispatch('booking-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('bookings.created'), type: 'success');

        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) $item->id;
        $this->reset(['customer_id', 'barber_id', 'service_id', 'selectedTime']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }
}
