<?php

namespace App\Observers;

use App\Models\BerberApp\Booking;
use App\Models\SmsDevice;
use App\Services\SmsService;
use App\Services\FirebaseService;

class BookingObserver
{
    protected $smsService;
    protected $firebaseService;

    public function __construct(SmsService $smsService, FirebaseService $firebaseService)
    {
        $this->smsService = $smsService;
        $this->firebaseService = $firebaseService;
    }

    public function created(Booking $booking)
    {
        $name = $booking->customer_name ?: ($booking->customer ? $booking->customer->name : 'Klient');
        $phone = $booking->customer_phone ?: ($booking->customer ? $booking->customer->phone : null);
        $time = $booking->appointment_datetime->format('H:i');
        $date = $booking->appointment_datetime->format('d/m/Y');

        // 1. Dërgo SMS konfirmimi te klienti (përmes Gateway)
        if ($phone) {
            $body = "Pershendetje {$name}, rezervimi juaj per ne date {$date} ne ore {$time} u krye me sukses.";
            $this->smsService->send($phone, $body, 'booking_confirmation');
        }

        // 2. Njofto Berberin/Adminin te App-i Mobile menjëherë
        $device = SmsDevice::where('is_active', true)->first();
        if ($device) {
            $this->firebaseService->sendNotification(
                "Rezervim i Ri! 🆕",
                "Klienti {$name} rezervoi per ne oren {$time} ({$date}).",
                $device->fcm_token
            );
        }
    }
}
