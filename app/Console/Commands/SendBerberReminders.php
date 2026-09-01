<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BerberApp\Reminder;
use App\Services\FirebaseService;
use App\Services\SmsService;
use Carbon\Carbon;

class SendBerberReminders extends Command
{
    protected $signature = 'berber:send-reminders';
    protected $description = 'Dërgon njoftimet push dhe SMS për rezervimet 30 min para me linqe interaktive';

    public function __construct(
        protected FirebaseService $firebaseService,
        protected SmsService $smsService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now();
        $reminders = Reminder::where('status', 'pending')
            ->where('send_at', '<=', $now)
            ->with(['booking.barber', 'booking.service'])
            ->get();

        foreach ($reminders as $reminder) {
            // Mark as processing immediately to avoid duplicates in next minute cycle
            $reminder->update(['status' => 'processing']);

            $booking = $reminder->booking;

            if (!$booking || $booking->status === 'cancelled') {
                $reminder->update(['status' => $booking ? 'cancelled' : 'failed']);
                continue;
            }

            $customerName = $booking->customer_name ?: ($booking->customer ? $booking->customer->name : 'Klient');
            $customerPhone = $booking->customer_phone ?: ($booking->customer ? $booking->customer->phone : null);
            $time = Carbon::parse($booking->appointment_datetime)->format('H:i');

            $title = "Kujtesë për Takimin";
            $body = "Përshëndetje {$customerName}! Mos harroni takimin tuaj në {$time}.";

            // Interactive link
            $confirmUrl = url("/confirm/{$booking->token}");
            $bodyWithLinks = $body . " Konfirmoni pjesmarrjen tuaj: {$confirmUrl}";

            $sent = false;

            // 1. Send SMS
            if ($customerPhone) {
                if ($this->smsService->send($customerPhone, $bodyWithLinks)) {
                    $sent = true;
                }
            }

            // 2. Send Push Notification
            $tokens = \App\Models\BerberApp\DeviceToken::where('booking_id', $booking->id)->pluck('fcm_token')->toArray();
            if (!empty($tokens)) {
                foreach ($tokens as $token) {
                    $this->firebaseService->sendNotification($title, $body, $token, [
                        'booking_id' => $booking->id,
                        'action' => 'REMINDER',
                        'confirm_url' => $confirmUrl,
                        'cancel_url' => $cancelUrl
                    ]);
                }
            }

            if ($sent) {
                $reminder->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
                $this->notifyBarberAboutReminder($booking);
            } else {
                $reminder->update(['status' => 'failed']);
            }
        }

        $this->info("U dërguan " . $reminders->count() . " njoftime.");
    }

    protected function notifyBarberAboutReminder($booking)
    {
        if (!$booking->barber || !$booking->barber->user_id) return;

        $title = "Kujtesë: Klienti po vjen";
        $body = "Klienti " . ($booking->customer_name ?: 'i panjohur') . " ka takimin në orën " . Carbon::parse($booking->appointment_datetime)->format('H:i');

        $this->firebaseService->sendNotification($title, $body, "user_{$booking->barber->user_id}");
    }
}
