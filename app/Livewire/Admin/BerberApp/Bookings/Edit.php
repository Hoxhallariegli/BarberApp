<?php

namespace App\Livewire\Admin\BerberApp\Bookings;

use App\Models\BerberApp\Booking;
use App\Models\BerberApp\Barber;
use App\Models\BerberApp\Service;
use App\Domain\BerberApp\Booking\DTOs\BookingDTO;
use App\Domain\BerberApp\Booking\Actions\UpdateBookingAction;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

class Edit extends Component
{
    use WithPagination;

    public Booking $item;
    public $customer_id = '';
    public $barber_id = '';
    public $service_id = '';
    public $selectedDate = '';
    public $selectedTime = '';

    public function mount(Booking $booking)
    {
        $this->item = $booking;
        $this->customer_id = $booking->customer_id;
        $this->barber_id = $booking->barber_id;
        $this->service_id = $booking->service_id;
        $this->selectedDate = $booking->appointment_datetime?->format('Y-m-d');
        $this->selectedTime = $booking->appointment_datetime?->format('H:i');
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
        $slots = $availabilityService->getAvailableSlots($barber, Carbon::parse($this->selectedDate), $service->duration_minutes ?: 30);

        // Add the current booking's time back to the available slots if it's the same day
        if ($this->item->appointment_datetime && $this->item->appointment_datetime->format('Y-m-d') === $this->selectedDate) {
            $currentTime = $this->item->appointment_datetime->format('H:i');
            if (!in_array($currentTime, $slots)) {
                $slots[] = $currentTime;
                sort($slots);
            }
        }

        return $slots;
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
        abort_if_cannot('edit_bookings');
        return view('livewire.admin.berber-app.bookings.edit', [
            'customers' => $this->getcustomersList(),
            'barbers' => $this->getbarbersList(),
            'services' => $this->getservicesList(),
            'availableSlots' => $this->availableSlots,
        ])->layout('components.layouts.app')->title(__('bookings.Edit Booking'));
    }

    public function update(UpdateBookingAction $action)
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
            'locale' => $this->item->locale, // Keep original locale or update to current? Let's keep original
        ]);

        $action->execute($this->item, $dto);
        session()->flash('success', __('bookings.updated'));
        return to_route('admin.bookings.index');
    }
}
