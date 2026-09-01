<?php

namespace App\Domain\BerberApp\Booking\Actions;

use App\Models\BerberApp\Booking;
use App\Models\AuditTrail;

class DeleteBookingAction
{
    public function execute(Booking $model): bool 
    {
        AuditTrail::log($model, 'delete', 'Bookings');
        return $model->delete(); 
    }
}