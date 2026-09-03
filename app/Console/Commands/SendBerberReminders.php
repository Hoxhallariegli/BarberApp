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
            ->with(['booking']) // Shtojmë këtë që të lejojë leximin e rezervimit
            ->get();

        foreach ($reminders as $reminder) {
            $booking = $reminder->booking;
            if (!$booking || $booking->status === 'cancelled') {
                $reminder->update(['status' => 'cancelled']);
                continue;
            }

            $customerName = $booking->customer_name ?: 'Klient';

            // PASTRIMI I NUMRIT (Saktësisht si te Landing)
            $phone = preg_replace('/[^0-9]/', '', $booking->customer_phone);
            if (str_starts_with($phone, '355')) { $phone = substr($phone, 3); }
            $phone = '+355' . substr(ltrim($phone, '0'), 0, 9);

            $time = Carbon::parse($booking->appointment_datetime)->format('H:i');
            $confirmUrl = rtrim(config('app.url'), '/') . "/confirm/{$booking->token}";

            // Use SMS Template
            $template = \App\Models\SmsTemplate::getTemplate('reminder');
            if ($template) {
                $body = str_replace(
                    ['{name}', '{time}', '{link_confirm}'],
                    [$customerName, $time, $confirmUrl],
                    $template
                );
            } else {
                $body = "STATION: Pershendetje {$customerName}, keni takim ne oren {$time}. Konfirmoni ketu: {$confirmUrl}";
            }

            $extraData = [
                'show_notification' => 'true',
                'notification_title' => "Rikujtesë: Takimi i orës {$time}",
                'notification_body' => "Po i dërgohet SMS klientit {$customerName}"
            ];

            if ($this->smsService->send($phone, $body, 'reminder', null, $extraData)) {
                $reminder->update(['status' => 'sent', 'sent_at' => now()]);
            }
        }
    }
}
