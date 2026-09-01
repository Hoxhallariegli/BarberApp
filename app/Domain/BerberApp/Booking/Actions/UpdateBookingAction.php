<?php

namespace App\Domain\BerberApp\Booking\Actions;

use App\Models\BerberApp\Booking;
use App\Domain\BerberApp\Booking\DTOs\BookingDTO;
use App\Models\AuditTrail;

class UpdateBookingAction
{
    public function execute(Booking $model, BookingDTO $dto): Booking
    {
        $model->fill($dto->toArray());
        AuditTrail::log($model, 'update', 'Bookings');
        $model->save();
        return $model->fresh();
    }
}