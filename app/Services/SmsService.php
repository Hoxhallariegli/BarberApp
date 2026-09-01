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

    public function send(string $phone, string $body, string $type = 'promotional', ?int $jobId = null)
    {
        $smsLog = SmsLog::create([
            'phone_number' => $phone,
            'body' => $body,
            'status' => 'pending',
            'template_type' => $type,
            'job_id' => $jobId,
        ]);

        return $this->dispatchToGateway($smsLog);
    }

    public function dispatchToGateway(SmsLog $smsLog)
    {
        $device = SmsDevice::where('is_active', true)->first();

        if (!$device) {
            Log::warning('No active SMS Gateway device found.');
            return false;
        }

        $data = [
            'action' => 'SEND_SMS',
            'phone' => $smsLog->phone_number,
            'body' => $smsLog->body,
            'sms_id' => $smsLog->id,
        ];

        // Dërgojmë një njoftim vizual që përmban edhe të dhënat e SMS-it (Data)
        // Kjo bën që Android ta shfaqë njoftimin vizual DHE të nisë SMS-in me të njëjtin sinjal
        $messageId = $this->firebase->sendNotification(
            "SMS Gateway: Dërgim...",
            "Po dërgohet te {$smsLog->phone_number}: " . \Illuminate\Support\Str::limit($smsLog->body, 40),
            $device->fcm_token,
            $data
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
