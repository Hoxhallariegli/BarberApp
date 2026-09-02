<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BerberApp\Reminder;
use App\Services\SmsService;
use Carbon\Carbon;

class SendBerberReminders extends Command
{
    protected $signature = 'berber:send-reminders';

    public function __construct(protected SmsService $smsService) {
        parent::__construct();
    }

    public function handle()
    {
        $reminders = Reminder::where('status', 'pending')
            ->where('send_at', '<=', Carbon::now())
            ->get();

        foreach ($reminders as $reminder) {
            $booking = $reminder->booking;
            if (!$booking || $booking->status === 'cancelled') {
                $reminder->update(['status' => 'cancelled']);
                continue;
            }

            $customerName = $booking->customer_name ?: 'Klient';
            $customerPhone = $booking->customer_phone;
            $time = Carbon::parse($booking->appointment_datetime)->format('H:i');
            $confirmUrl = rtrim(config('app.url'), '/') . "/confirm/{$booking->token}";

            // Mesazhi i plote me link
            $body = "Pershendetje {$customerName}, keni lene takim sot ne oren {$time}. Konfirmoni ketu: {$confirmUrl}";

            if ($customerPhone) {
                $extraData = [
                    'show_notification' => 'true',
                    'notification_title' => "Rikujtesë: Klienti po vjen",
                    'notification_body' => "Klienti {$customerName} ka takimin në {$time}"
                ];

                if ($this->smsService->send($customerPhone, $body, 'reminder', null, $extraData)) {
                    $reminder->update(['status' => 'sent', 'sent_at' => now()]);
                }
            }
        }
    }
}
