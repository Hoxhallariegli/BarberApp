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
    public $service_id = ''; // Temp for dropdown
    public $service_ids = [];
    public $selectedDate = '';
    public $selectedTime = '';

    public function mount(Booking $booking)
    {
        $this->item = $booking;
        $this->customer_id = $booking->customer_id;
        $this->barber_id = $booking->barber_id;
        $this->service_ids = $booking->services()->pluck('ba_services.id')->map(fn($id) => (string)$id)->toArray();
        if (empty($this->service_ids) && $booking->service_id) {
            $this->service_ids = [(string)$booking->service_id];
        }
        $this->selectedDate = $booking->appointment_datetime?->format('Y-m-d');
        $this->selectedTime = $booking->appointment_datetime?->format('H:i');
    }

    #[On('customer-created')]
    public function refreshCustomers($id) { $this->customer_id = $id; }

    #[On('barber-created')]
    public function refreshBarbers($id) { $this->barber_id = $id; }

    #[On('service-created')]
    public function refreshServices($id) { $this->service_ids[] = (string)$id; }

    public function updatedBarberId() { $this->selectedTime = ''; }
    public function updatedSelectedDate() { $this->selectedTime = ''; }

    public function addService()
    {
        if ($this->service_id && !in_array($this->service_id, $this->service_ids)) {
            $this->service_ids[] = (string)$this->service_id;
            $this->service_id = '';
            $this->selectedTime = '';
        }
    }

    public function removeService($index)
    {
        unset($this->service_ids[$index]);
        $this->service_ids = array_values($this->service_ids);
        $this->selectedTime = '';
    }

    public function getAvailableSlotsProperty()
    {
        if (!$this->selectedDate || empty($this->service_ids) || !$this->barber_id) return [];

        $barber = Barber::find($this->barber_id);
        if (!$barber) return [];

        $totalDuration = Service::whereIn('id', $this->service_ids)->sum('duration_minutes');

        $availabilityService = app(AvailabilityService::class);
        $slots = $availabilityService->getAvailableSlots($barber, Carbon::parse($this->selectedDate), (int)$totalDuration ?: 30);

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
        return Service::get()->pluck('translated_name', 'id')->toArray();
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
            'service_ids' => 'required|array|min:1',
            'selectedDate' => 'required|date',
            'selectedTime' => 'required',
        ], [
            'selectedTime.required' => 'Ju lutem zgjidhni orarin e rezervimit.',
            'service_ids.required' => 'Ju lutem shtoni të paktën një shërbim në listë.'
        ]);

        $dto = BookingDTO::fromArray([
            'customer_id' => $this->customer_id,
            'barber_id' => $this->barber_id,
            'service_id' => $this->service_ids[0],
            'service_ids' => $this->service_ids,
            'appointment_datetime' => Carbon::parse($this->selectedDate . ' ' . $this->selectedTime)->toDateTimeString(),
            'locale' => $this->item->locale,
        ]);

        $action->execute($this->item, $dto);
        session()->flash('success', __('bookings.updated'));
        return to_route('admin.bookings.index');
    }
}
