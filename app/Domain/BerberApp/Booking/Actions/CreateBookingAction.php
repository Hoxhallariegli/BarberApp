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

        if (empty($data['customer_id']) && !empty($data['customer_phone'])) {
            $customer = \App\Models\BerberApp\Customer::firstOrCreate(
                ['phone' => $data['customer_phone']],
                ['name' => $data['customer_name'] ?? 'Klient']
            );
            $data['customer_id'] = $customer->id;
        }

        $item = Booking::create($data);
        AuditTrail::log($item, 'create', 'Bookings');

        $phone = $item->customer_phone ?: ($item->customer ? $item->customer->phone : null);
        if ($phone) {
            $smsService = app(\App\Services\SmsService::class);
            $time = Carbon::parse($item->appointment_datetime)->format('H:i');

            // Use SMS Template
            $template = \App\Models\SmsTemplate::getTemplate('booking_confirmation');
            if ($template) {
                $message = str_replace(
                    ['{name}', '{time}'],
                    [$item->customer_name ?: 'Klient', $time],
                    $template
                );
            } else {
                $message = "STATION: Rezervimi juaj u krye me sukses per oren {$time}.";
            }

            $extraData = [
                'show_notification' => 'true',
                'notification_title' => "Rezervim i Ri! 🆕",
                'notification_body' => "Klienti {$item->customer_name} - Ora {$time}"
            ];

            $smsService->send($phone, $message, 'booking_confirmation', null, $extraData);
        }

        if ($item->reminder_enabled) {
            \App\Models\BerberApp\Reminder::create([
                'booking_id' => $item->id,
                'reminder_type' => 'sms_and_push',
                'send_at' => Carbon::parse($item->appointment_datetime)->subMinutes(30),
                'status' => 'pending',
            ]);
        }

        return $item;
    }
}
