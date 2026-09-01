<?php

namespace App\Http\Controllers\BerberApp;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Booking;
use Illuminate\Http\Request;

class ConfirmBookingController extends Controller
{
    public function show($token)
    {
        $booking = Booking::where('token', $token)->firstOrFail();

        return view('front.confirm-booking', compact('booking'));
    }

    public function confirm($token)
    {
        $booking = Booking::where('token', $token)->firstOrFail();
        $booking->update(['status' => 'confirmed']);

        return back()->with('success', 'Rezervimi u konfirmua me sukses! Ju presim.');
    }

    public function cancel($token)
    {
        $booking = Booking::where('token', $token)->firstOrFail();
        $booking->update(['status' => 'cancelled']);

        return back()->with('info', 'Rezervimi u anullua. Faleminderit që na njoftuat.');
    }
}
