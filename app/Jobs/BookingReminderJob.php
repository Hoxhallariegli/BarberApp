<?php

namespace App\Jobs;

use App\Models\BerberApp\Booking;
use App\Models\BerberApp\Reminder;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BookingReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(SmsService $smsService)
    {
        $now = Carbon::now();
        $startTime = $now->copy()->addMinutes(25);
        $endTime = $now->copy()->addMinutes(35);

        $bookings = Booking::whereBetween('appointment_datetime', [$startTime, $endTime])
            ->where('status', 'pending')
            ->where('reminder_enabled', true)
            ->get();

        foreach ($bookings as $booking) {
            // Check if reminder already sent
            $exists = Reminder::where('booking_id', $booking->id)
                ->where('reminder_type', '30_min_sms')
                ->exists();

            if ($exists) continue;

            $name = $booking->customer_name ?: ($booking->customer ? $booking->customer->name : 'Klient');
            $phone = $booking->customer_phone ?: ($booking->customer ? $booking->customer->phone : null);

            if (!$phone) continue;

            $time = $booking->appointment_datetime->format('H:i');
            $link = url('/confirm/' . $booking->token);

            $body = "Pershendetje {$name}, ju keni nje rezervim ne oren {$time}. Konfirmoni pjesmarrjen tuaj: {$link}";

            if ($smsService->send($phone, $body, 'booking_reminder')) {
                Reminder::create([
                    'booking_id' => $booking->id,
                    'reminder_type' => '30_min_sms',
                    'sent_at' => Carbon::now(),
                    'status' => 'sent'
                ]);
            }
        }
    }
}
