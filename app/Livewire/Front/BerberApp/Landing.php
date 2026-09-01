<?php

namespace App\Livewire\Front\BerberApp;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\BerberApp\Barber;
use App\Models\BerberApp\Service;
use App\Models\BerberApp\Booking;
use App\Models\BerberApp\BarberAbsence;
use App\Models\BerberApp\BarberSchedule;
use App\Services\AvailabilityService;
use Carbon\Carbon;

#[Title('The Station Barbers')]
class Landing extends Component
{
    // Booking Flow State
    public $showBookingModal = false;
    public $step = 1; // 1: Service/Barber, 2: Date/Time, 3: Info

    public $selectedServiceId;
    public $selectedBarberId;
    public $selectedDate;
    public $selectedTime;

    public $customerName;
    public $customerPhone;
    public $allowNotifications = true;

    protected $listeners = ['fcm-token-received' => 'setFcmToken'];
    public $fcmToken;

    public function setFcmToken($token)
    {
        $this->fcmToken = $token;
    }

    public function mount()
    {
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function selectService($id)
    {
        $this->selectedServiceId = $id;
        $this->step = 2;

        // Only open modal AFTER data is potentially ready (Livewire will handle the state)
        $this->showBookingModal = true;
    }

    public function updatedSelectedDate()
    {
        $this->selectedTime = null;
    }

    public function getAvailableSlotsProperty()
    {
        if (!$this->selectedDate || !$this->selectedServiceId) return [];

        $selectedService = Service::find($this->selectedServiceId);
        if (!$selectedService) return [];

        $barber = $this->selectedBarberId ? Barber::find($this->selectedBarberId) : Barber::first();
        if (!$barber) return [];

        $availabilityService = app(AvailabilityService::class);
        $date = Carbon::parse($this->selectedDate);

        return $availabilityService->getAvailableSlots($barber, $date, $selectedService->duration_minutes ?: 30);
    }

    public function confirmTime($time)
    {
        $this->selectedTime = $time;
        $this->step = 3;
    }

    public function submitBooking(\App\Domain\BerberApp\Booking\Actions\CreateBookingAction $action)
    {
        $this->validate([
            'customerName' => 'required|string|min:3',
            'customerPhone' => 'required|string|min:8',
            'selectedServiceId' => 'required',
            'selectedDate' => 'required',
            'selectedTime' => 'required',
        ]);

        $barberId = $this->selectedBarberId;
        if (!$barberId) {
            $barberId = Barber::where('active', true)->first()->id;
        }

        $dto = \App\Domain\BerberApp\Booking\DTOs\BookingDTO::fromArray([
            'barber_id' => $barberId,
            'service_id' => $this->selectedServiceId,
            'customer_name' => $this->customerName,
            'customer_phone' => $this->customerPhone,
            'appointment_datetime' => Carbon::parse($this->selectedDate . ' ' . $this->selectedTime)->toDateTimeString(),
            'status' => 'pending',
            'reminder_enabled' => $this->allowNotifications,
            'reminder_minutes' => 30,
            'fcm_token' => $this->fcmToken,
        ]);

        $action->execute($dto);

        $this->step = 4; // Success
    }

    public function resetBooking()
    {
        $this->reset(['showBookingModal', 'step', 'selectedServiceId', 'selectedBarberId', 'selectedTime', 'customerName', 'customerPhone']);
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.front.berber-app.landing', [
            'barbers' => Barber::all(), // Removed active filter for initial display
            'services' => Service::all(), // Removed active filter for initial display
            'selectedService' => $this->selectedServiceId ? Service::find($this->selectedServiceId) : null,
            'selectedBarber' => $this->selectedBarberId ? Barber::find($this->selectedBarberId) : null,
        ])->layout('components.layouts.front');
    }
}
