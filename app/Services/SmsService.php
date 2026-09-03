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

        \Illuminate\Support\Facades\Log::info("SMS GATEWAY: Sending signal to Firebase for SMS ID {$smsLog->id} to {$smsLog->phone_number}");

        // Sigurohemi që të gjitha të dhënat të shkojnë në 'data' payload
        $payload = array_merge([
            'action' => 'SEND_SMS',
            'phone' => $smsLog->phone_number,
            'body' => $smsLog->body,
            'sms_id' => (string)$smsLog->id,
        ], $extraData);

        // Shfaqim çfarë po dërgojmë për debug
        \Illuminate\Support\Facades\Log::debug("SMS GATEWAY PAYLOAD:", $payload);

        $messageId = $this->firebase->sendData(
            $device->fcm_token,
            $payload
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
