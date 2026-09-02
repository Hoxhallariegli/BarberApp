<?php

namespace App\Domain\BerberApp\Booking\Actions;

use App\Models\BerberApp\Booking;
use App\Domain\BerberApp\Booking\DTOs\BookingDTO;
use App\Models\AuditTrail;
use Carbon\Carbon;

class CreateBookingAction
{
    public function execute(BookingDTO $dto): Booking
    {
        $data = $dto->toArray();

        // Handle customer by phone if customer_id is missing
        if (empty($data['customer_id']) && !empty($data['customer_phone'])) {
            $customer = \App\Models\BerberApp\Customer::firstOrCreate(
                ['phone' => $data['customer_phone']],
                ['name' => $data['customer_name'] ?? 'Klient']
            );
            $data['customer_id'] = $customer->id;
        }

        $item = Booking::create($data);
        AuditTrail::log($item, 'create', 'Bookings');

        // SMS Notification: "Ju bëtë aplikim në websitin tonë në këtë orar"
        $phone = $item->customer_phone ?: ($item->customer ? $item->customer->phone : null);
        if ($phone) {
            $smsService = app(\App\Services\SmsService::class);
            $time = Carbon::parse($item->appointment_datetime)->format('H:i d/m/Y');
            $baseUrl = config('app.url');
            $confirmUrl = rtrim($baseUrl, '/') . "/confirm/{$item->token}";

            $message = "Ju bëtë aplikim në websitin tonë në këtë orar: {$time}. Mund ta menaxhoni këtu: {$confirmUrl}";

            // Shtojmë njoftim për Adminin/APK-në
            $extraData = [
                'show_notification' => 'true',
                'notification_title' => "Rezervim i Ri!",
                'notification_body' => "Klienti {$item->customer_name} rezervoi në orën {$time}"
            ];

            $smsService->send($phone, $message, 'booking_confirmation', null, $extraData);
        }

        // Schedule Reminder (30 minutes before)
        if ($item->reminder_enabled) {
            $sendAt = Carbon::parse($item->appointment_datetime)->subMinutes($item->reminder_minutes ?? 30);
            \App\Models\BerberApp\Reminder::create([
                'booking_id' => $item->id,
                'reminder_type' => 'sms_and_push',
                'send_at' => $sendAt,
                'status' => 'pending',
            ]);
        }

        return $item;
    }
}
