<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BerberApp\Reminder;
use App\Services\FirebaseService;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendBerberReminders extends Command
{
    protected $signature = 'berber:send-reminders';
    protected $description = 'Dërgon njoftimet SMS për rezervimet 30 min para';

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
            ->with(['booking.barber', 'booking.customer'])
            ->get();

        if ($reminders->isEmpty()) return;

        foreach ($reminders as $reminder) {
            $booking = $reminder->booking;
            if (!$booking || $booking->status === 'cancelled') {
                $reminder->update(['status' => 'cancelled']);
                continue;
            }

            $reminder->update(['status' => 'processing']);

            $customerName = $booking->customer_name ?: ($booking->customer ? $booking->customer->name : 'Klient');
            $customerPhone = $booking->customer_phone ?: ($booking->customer ? $booking->customer->phone : null);
            $time = Carbon::parse($booking->appointment_datetime)->format('H:i');
            $confirmUrl = rtrim(config('app.url'), '/') . "/confirm/{$booking->token}";

            $body = "Pershendetje {$customerName}, keni lene takim sot ne oren {$time}. Ju lutem konfirmoni ne link: {$confirmUrl}";

            if ($customerPhone) {
                $extraData = [
                    'show_notification' => 'true',
                    'notification_title' => "Kujtesë: Klienti po vjen",
                    'notification_body' => "Klienti {$customerName} ka takimin në orën {$time}"
                ];

                if ($this->smsService->send($customerPhone, $body, 'reminder', null, $extraData)) {
                    $reminder->update(['status' => 'sent', 'sent_at' => now()]);
                } else {
                    $reminder->update(['status' => 'pending']);
                }
            }
        }
        $this->info("Procesi perfundoi.");
    }
}
