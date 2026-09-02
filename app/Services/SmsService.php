<?php

namespace App\Services;

use App\Models\SmsDevice;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function send(string $phone, string $body, string $type = 'promotional', ?int $jobId = null, array $extraData = [])
    {
        $smsLog = SmsLog::create([
            'phone_number' => $phone,
            'body' => $body,
            'status' => 'pending',
            'template_type' => $type,
            'job_id' => $jobId,
        ]);

        return $this->dispatchToGateway($smsLog, $extraData);
    }

    public function dispatchToGateway(SmsLog $smsLog, array $extraData = [])
    {
        $device = SmsDevice::where('is_active', true)->first();

        if (!$device) {
            Log::warning('No active SMS Gateway device found.');
            return false;
        }

        $data = array_merge([
            'action' => 'SEND_SMS',
            'phone' => $smsLog->phone_number,
            'body' => $smsLog->body,
            'sms_id' => $smsLog->id,
        ], $extraData);

        \Illuminate\Support\Facades\Log::info("SMS GATEWAY: Sending signal to Firebase for SMS ID {$smsLog->id} to {$smsLog->phone_number}");

        // Përdorim sendData me HIGH PRIORITY që APK ta kapë menjëherë në background
        $messageId = $this->firebase->sendData(
            $device->fcm_token,
            array_merge($data, [
                'notification_title' => $extraData['notification_title'] ?? "SMS Gateway: Dërgim...",
                'notification_body' => $extraData['notification_body'] ?? "Po dërgohet te {$smsLog->phone_number}",
                'show_notification' => 'true'
            ])
        );

        if ($messageId) {
            $smsLog->update([
                'status' => 'queued',
                'fcm_message_id' => $messageId
            ]);
        }

        return (bool) $messageId;
    }
}
