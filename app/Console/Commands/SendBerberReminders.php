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
    protected $description = 'Dërgon njoftimet SMS dhe Push për rezervimet 30 min para';

    public function __construct(
        protected FirebaseService $firebaseService,
        protected SmsService $smsService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now();
        // Marrim rikujtesat që duhen dërguar dhe janë ende në pritje
        $reminders = Reminder::where('status', 'pending')
            ->where('send_at', '<=', $now)
            ->with(['booking.barber', 'booking.service'])
            ->get();

        if ($reminders->isEmpty()) {
            return;
        }

        foreach ($reminders as $reminder) {
            $booking = $reminder->booking;

            if (!$booking || $booking->status === 'cancelled') {
                $reminder->update(['status' => 'cancelled']);
                continue;
            }

            // Shënoje si 'processing' MENJËHERË që cikli tjetër i minutës mos ta kapë përsëri
            $reminder->update(['status' => 'processing']);

            $customerName = $booking->customer_name ?: ($booking->customer ? $booking->customer->name : 'Klient');
            $customerPhone = $booking->customer_phone ?: ($booking->customer ? $booking->customer->phone : null);
            $time = Carbon::parse($booking->appointment_datetime)->format('H:i');

            // Use config APP_URL to ensure links are correct even in CLI
            $baseUrl = config('app.url');
            $confirmUrl = rtrim($baseUrl, '/') . "/confirm/{$booking->token}";

            $body = "Përshëndetje {$customerName}, keni një rezervim në oren {$time}. Konfirmoni: {$confirmUrl}";

            $sent = false;

            // 1. Dërgo SMS-in
            if ($customerPhone) {
                try {
                    // Kjo dërgon sinjalin te celulari
                    if ($this->smsService->send($customerPhone, $body, 'reminder')) {
                        $sent = true;
                    }
                } catch (\Exception $e) {
                    Log::error("Reminder SMS failed: " . $e->getMessage());
                }
            }

            // 2. Nëse u dërgua sinjali, shënoje rikujtesën si të përfunduar
            if ($sent) {
                $reminder->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);

                // 3. Opsionale: Njofto berberin (në një bllok veçmas që mos të bllokojë SMS-in)
                try {
                    $this->notifyBarberAboutReminder($booking);
                } catch (\Exception $e) {
                    Log::error("Barber notification failed: " . $e->getMessage());
                }
            } else {
                // Nëse dështoi sinjali i Firebase, ktheje në pending për t'u riprovuar
                $reminder->update(['status' => 'pending']);
            }
        }

        $this->info("Procesi përfundoi për " . $reminders->count() . " rikujtesa.");
    }

    protected function notifyBarberAboutReminder($booking)
    {
        if (!$booking->barber) return;

        // Dërgojmë njoftim te pajisja aktive e adminit për t'i thënë që klienti po vjen
        $title = "Kujtesë: Klienti po vjen";
        $body = "Klienti " . ($booking->customer_name ?: 'i panjohur') . " ka takimin në orën " . Carbon::parse($booking->appointment_datetime)->format('H:i');

        $device = \App\Models\SmsDevice::where('is_active', true)->first();
        if ($device) {
            $this->firebaseService->sendNotification($title, $body, $device->fcm_token);
        }
    }
}
