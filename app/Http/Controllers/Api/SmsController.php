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
        $request->validate([
            'fcm_message_id' => 'required|string',
            'status' => 'required|in:sent,failed',
            'error_message' => 'nullable|string',
        ]);

        $smsLog = SmsLog::where('fcm_message_id', $request->fcm_message_id)->first();

        if (!$smsLog) {
            return response()->json([
                'success' => false,
                'message' => 'SMS log not found',
            ], 404);
        }

        $smsLog->update([
            'status' => $request->status,
            'sent_at' => $request->status === 'sent' ? now() : null,
            'error_message' => $request->error_message,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
        ]);
    }
}
