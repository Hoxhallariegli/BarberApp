<?php

namespace App\Domain\BerberApp\Booking\Actions;

use App\Models\BerberApp\Booking;
use App\Domain\BerberApp\Booking\DTOs\BookingDTO;
use App\Models\AuditTrail;

class UpdateBookingAction
{
    public function execute(Booking $model, BookingDTO $dto): Booking
    {
        $oldStatus = $model->status;
        $data = $dto->toArray();

        // Convert service_ids to clean integer array
        $serviceIds = isset($data['service_ids']) ? array_values(array_map('intval', (array)$data['service_ids'])) : [];
        unset($data['service_ids']);

        if (!empty($serviceIds)) {
            $data['service_id'] = $serviceIds[0];
        }

        $model->fill($data);
        $model->save();

        // Sync services to pivot table
        $model->services()->sync($serviceIds);

        // Generate payment if status changed to completed
        if ($oldStatus !== 'completed' && $model->status === 'completed') {
            if (!$model->payments()->exists()) {
                \App\Models\BerberApp\Payment::create([
                    'booking_id' => $model->id,
                    'amount' => $model->total_price ?: ($model->service?->price ?? 0),
                    'status' => 'paid',
                ]);
            }
        }

        AuditTrail::log($model, 'update', 'Bookings');
        return $model->fresh(['customer', 'barber', 'service', 'services']);
    }
}
