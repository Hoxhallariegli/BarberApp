<?php

namespace App\Domain\BerberApp\Booking\Actions;

use App\Models\BerberApp\Booking;
use App\Models\BerberApp\Service;
use App\Models\BerberApp\Barber;
use App\Domain\BerberApp\Booking\DTOs\BookingDTO;
use App\Models\AuditTrail;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class CreateBookingAction
{
    public function execute(BookingDTO $dto): Booking
    {
        $data = $dto->toArray();

        // Nese nuk kemi ID klienti por kemi telefon, krijojme klientin
        if (empty($data['customer_id']) && !empty($data['customer_phone'])) {
            $customer = \App\Models\BerberApp\Customer::firstOrCreate(
                ['phone' => $this->formatPhone($data['customer_phone'])],
                ['name' => $data['customer_name'] ?? 'Klient']
            );
            $data['customer_id'] = $customer->id;
        }

        $data['reminder_enabled'] = filter_var($data['reminder_enabled'], FILTER_VALIDATE_BOOLEAN);
        $serviceIds = array_map('intval', (array) ($data['service_ids'] ?? []));
        unset($data['service_ids']);

        if (!empty($serviceIds)) {
            $data['service_id'] = $serviceIds[0];
        }

        // VALIDIMI I DISPONUESHMERISE (MANDATORY)
        $barber = Barber::findOrFail($data['barber_id']);
        $start = Carbon::parse($data['appointment_datetime']);

        $totalDuration = 0;
        if (!empty($serviceIds)) {
            $totalDuration = Service::whereIn('id', $serviceIds)->sum('duration_minutes');
        } else if (isset($data['service_id'])) {
            $s = Service::find($data['service_id']);
            $totalDuration = $s?->duration_minutes ?: 30;
        }

        $availabilityService = app(AvailabilityService::class);
        if (!$availabilityService->isSlotAvailable($barber, $start, $totalDuration ?: 30)) {
            throw ValidationException::withMessages([
                'appointment_datetime' => ['Ky orar është i zënë ose nuk mjafton koha për shërbimet e zgjedhura.'],
            ]);
        }

        $item = Booking::create($data);

        if (!empty($serviceIds)) {
            $item->services()->sync($serviceIds);
        }

        $item->load(['customer', 'barber', 'service', 'services']);
        AuditTrail::log($item, 'create', 'Bookings');

        $phone = $item->customer_phone ?: ($item->customer ? $item->customer->phone : null);

        if ($phone && $item->reminder_enabled) {
            $phone = $this->formatPhone($phone);
            $smsService = app(\App\Services\SmsService::class);
            $time = Carbon::parse($item->appointment_datetime)->format('H:i');
            $date = Carbon::parse($item->appointment_datetime)->format('d/m');

            $template = \App\Models\SmsTemplate::getTemplate('booking_confirmation', $item->locale);
            if ($template) {
                $message = str_replace(
                    ['{name}', '{time}', '{date}'],
                    [$item->customer_name ?: ($item->customer ? $item->customer->name : 'Klient'), $time, $date],
                    $template
                );
            } else {
                $message = "STATION: Rezervimi u krye per oren {$time} - {$date}.";
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
                'send_at' => Carbon::parse($item->appointment_datetime)->subMinutes((int)$item->reminder_minutes),
                'status' => 'pending',
            ]);
        }

        if ($item->status === 'completed') {
            \App\Models\BerberApp\Payment::create([
                'booking_id' => $item->id,
                'amount' => $item->total_price ?: ($item->service?->price ?? 0),
                'status' => 'paid',
            ]);
        }

        return $item;
    }

    private function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '355' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '355')) {
            $phone = '355' . $phone;
        }
        return '+' . $phone;
    }
}
