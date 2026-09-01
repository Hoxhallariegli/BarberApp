<?php

namespace App\Observers;

use App\Models\BerberApp\Booking;
use App\Services\SmsService;

class BookingObserver
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function created(Booking $booking)
    {
        $name = $booking->customer_name ?: ($booking->customer ? $booking->customer->name : 'Klient');
        $phone = $booking->customer_phone ?: ($booking->customer ? $booking->customer->phone : null);

        if (!$phone) return;

        $date = $booking->appointment_datetime->format('d/m/Y');
        $time = $booking->appointment_datetime->format('H:i');

        $body = "Pershendetje {$name}, rezervimi juaj per ne date {$date} ne ore {$time} u krye me sukses.";

        $this->smsService->send($phone, $body, 'booking_confirmation');
    }
}
