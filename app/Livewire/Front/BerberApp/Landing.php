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

        // Pastrim rrënjësor i numrit
        $phone = preg_replace('/[^0-9]/', '', $this->customerPhone);

        // Nëse fillon me 0, e heqim (psh 067 -> 67)
        $phone = ltrim($phone, '0');

        // Nëse nuk ka 355 përpara, e shtojmë
        if (!str_starts_with($phone, '355')) {
            $phone = '355' . $phone;
        }
        $phone = '+' . $phone;

        // LIMITOJME GJATESINE (Sigurohemi qe nuk kemi shifra te teperta)
        // +355 (4) + 67/68/69 (2) + 7 shifra = 12 karaktere ne total
        if (strlen($phone) > 13) {
             // Heqim shifrat e teperta nese u shkruan gabim
             $phone = substr($phone, 0, 13);
        }

        $dto = \App\Domain\BerberApp\Booking\DTOs\BookingDTO::fromArray([
            'barber_id' => $barberId,
            'service_id' => $this->selectedServiceId,
            'customer_name' => $this->customerName,
            'customer_phone' => $phone,
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
