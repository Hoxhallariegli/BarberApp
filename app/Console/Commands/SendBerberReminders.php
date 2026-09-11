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
        $now = Carbon::now();

        // 1. Pastrojmë/Anullojmë rikujtesat që kanë kaluar kohën e tyre me më shumë se 2 orë
        // Kjo parandalon dërgimin e mesazheve të djeshme sot.
        Reminder::where('status', 'pending')
            ->where('send_at', '<', $now->copy()->subHours(2))
            ->update(['status' => 'expired']);

        // 2. Marrim vetëm rikujtesat "e freskëta" që duhen dërguar TANI
        $reminders = Reminder::where('status', 'pending')
            ->where('send_at', '<=', $now)
            ->where('send_at', '>=', $now->copy()->subHours(2))
            ->with(['booking'])
            ->get();

        foreach ($reminders as $reminder) {
            $booking = $reminder->booking;
            if (!$booking || $booking->status === 'cancelled') {
                $reminder->update(['status' => 'cancelled']);
                continue;
            }

            $customerName = $booking->customer_name ?: 'Klient';
            $phone = $this->formatPhone($booking->customer_phone ?: $booking->customer?->phone);

            if (!$phone) {
                $reminder->update(['status' => 'failed']);
                continue;
            }

            $time = Carbon::parse($booking->appointment_datetime)->format('H:i');
            $date = Carbon::parse($booking->appointment_datetime)->format('d/m');
            $confirmUrl = str_replace(['https://', 'http://'], '', rtrim(config('app.url'), '/') . "/confirm/{$booking->token}");

            $template = \App\Models\SmsTemplate::getTemplate('reminder', $booking->locale);
            $body = $template
                ? str_replace(['{name}', '{time}', '{date}', '{link_confirm}'], [$customerName, $time, $date, $confirmUrl], $template)
                : "STATION: Takim ne {$time} - {$date}. Konfirmo: {$confirmUrl}";

            $extraData = [
                'show_notification' => 'true',
                'notification_title' => "Rikujtese: Takimi {$time}",
                'notification_body' => "SMS per {$customerName}"
            ];

            if ($this->smsService->send($phone, $body, 'reminder', null, $extraData)) {
                $reminder->update(['status' => 'sent', 'sent_at' => now()]);
            }
        }
    }

    private function formatPhone($phone) {
        if (!$phone) return null;
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '355')) $phone = substr($phone, 3);
        return '+355' . substr(ltrim($phone, '0'), 0, 9);
    }
}
