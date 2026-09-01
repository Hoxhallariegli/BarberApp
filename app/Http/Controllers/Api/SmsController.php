<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsDevice;
use App\Models\SmsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SmsController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
            'device_name' => 'nullable|string',
            'api_key' => 'nullable|string',
        ]);

        $apiKey = $request->api_key ?? Str::random(32);

        $device = SmsDevice::updateOrCreate(
            ['api_key' => $apiKey],
            [
                'fcm_token' => $request->fcm_token,
                'device_name' => $request->device_name,
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully',
            'api_key' => $device->api_key,
        ]);
    }

    public function statusUpdate(Request $request)
    {
        \Illuminate\Support\Facades\Log::info("SMS GATEWAY FEEDBACK:", $request->all());

        if ($request->status === 'debug_log') {
            return response()->json(['success' => true]);
        }

        $request->validate([
            'sms_id' => 'required_without:fcm_message_id',
            'fcm_message_id' => 'required_without:sms_id',
            'status' => 'required|string', // sent, failed, processing
            'error_message' => 'nullable|string',
        ]);

        $smsLog = null;
        if ($request->sms_id) {
            $smsLog = SmsLog::find($request->sms_id);
        } elseif ($request->fcm_message_id) {
            $smsLog = SmsLog::where('fcm_message_id', $request->fcm_message_id)->first();
        }

        if (!$smsLog) {
            return response()->json(['success' => false, 'message' => 'SMS log not found'], 404);
        }

        $updateData = [
            'status' => $request->status,
            'error_message' => $request->error_message,
        ];

        if ($request->status === 'sent') {
            $updateData['sent_at'] = now();
        }

        $smsLog->update($updateData);

        return response()->json(['success' => true, 'message' => 'Status updated: ' . $request->status]);
    }
}
