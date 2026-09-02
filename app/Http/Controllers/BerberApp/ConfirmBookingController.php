<?php

namespace App\Http\Controllers\BerberApp;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Booking;
use App\Models\SmsDevice;
use App\Services\FirebaseService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConfirmBookingController extends Controller
{
    public function __construct(
        protected FirebaseService $firebase,
        protected SmsService $sms
    ) {}

    public function show($token)
    {
        $booking = Booking::where('token', $token)->with(['barber', 'service'])->firstOrFail();

        return view('front.confirm-booking', compact('booking'));
    }

    public function confirm($token)
    {
        $booking = Booking::where('token', $token)->with(['barber'])->firstOrFail();
        $booking->update(['status' => 'confirmed']);

        $this->notifyInterests($booking, 'konfirmuar');

        return back()->with('success', 'Rezervimi u konfirmua me sukses! Ju presim.');
    }

    public function cancel($token)
    {
        $booking = Booking::where('token', $token)->with(['barber'])->firstOrFail();
        $booking->update(['status' => 'cancelled']);

        $this->notifyInterests($booking, 'anulluar');

        return back()->with('info', 'Rezervimi u anullua. Faleminderit që na njoftuat.');
    }

    protected function notifyInterests(Booking $booking, string $statusLabel)
    {
        $customerName = $booking->customer_name ?: ($booking->customer ? $booking->customer->name : 'Klienti');
        $time = $booking->appointment_datetime->format('H:i');

        $title = "Përditësim Rezervimi";
        $message = "Klienti {$customerName} e ka {$statusLabel} takimin e orës {$time}.";

        // 1. Njofto APK-në (Adminin) via Push
        $device = SmsDevice::where('is_active', true)->first();
        if ($device) {
            $this->firebase->sendNotification($title, $message, $device->fcm_token);
        }

        // 2. Njofto Berberin via SMS (nëse ka numër)
        if ($booking->barber && $booking->barber->phone) {
            $this->sms->send($booking->barber->phone, "NJOFTIM: {$message}", 'alert');
        }
    }
}
