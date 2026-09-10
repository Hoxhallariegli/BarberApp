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

class Create extends Component
{
    use WithPagination;

    public $customer_id = '';
    public $barber_id = '';
    public $service_id = ''; // Temp for dropdown
    public $service_ids = [];
    public $selectedDate = '';
    public $selectedTime = '';

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
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
        return $availabilityService->getAvailableSlots($barber, Carbon::parse($this->selectedDate), (int)$totalDuration ?: 30);
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
        abort_if_cannot('add_bookings');
        return view('livewire.admin.berber-app.bookings.create', [
            'customers' => $this->getcustomersList(),
            'barbers' => $this->getbarbersList(),
            'services' => $this->getservicesList(),
            'availableSlots' => $this->availableSlots,
        ])->layout('components.layouts.app')->title(__('bookings.Add Booking'));
    }

    public function store(CreateBookingAction $action) {
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
            'status' => 'confirmed',
            'locale' => app()->getLocale(),
            'reminder_enabled' => true,
        ]);

        $action->execute($dto);
        session()->flash('success', __('bookings.created'));
        return to_route('admin.bookings.index');
    }
}
