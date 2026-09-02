<?php

namespace App\Observers;

use App\Models\BerberApp\Booking;

class BookingObserver
{
    // Pastruar për të shmangur dublikimin e SMS-ve.
    // Njoftimet menaxhohen nga CreateBookingAction.
    public function created(Booking $booking)
    {
        // Do nothing
    }
}
