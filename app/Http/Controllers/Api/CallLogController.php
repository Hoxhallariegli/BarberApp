<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CallLog;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CallLogController extends Controller
{
    public function index()
    {
        return CallLog::latest()->paginate();
    }

    public function log(Request $request)
    {
        Log::info('--- SMS GATEWAY: CALL ATTEMPT ---', $request->all());

        $phoneNumber = $request->phone_number ?? 'Unknown';

        if (!$phoneNumber || $phoneNumber === 'Unknown') {
            Log::warning('SMS GATEWAY: Skipping call with Unknown number');
            return response()->json(['success' => false, 'message' => 'Skipping unknown number']);
        }

        $phoneNumber = preg_replace('/[^0-9+]/', '', (string)$phoneNumber);

        // Kontrollojmë duplikatin e saktë (numër dhe tip brenda 10 sekondave)
        $recentCall = CallLog::where('phone_number', $phoneNumber)
            ->where('type', $request->type ?? 'incoming')
            ->where('created_at', '>=', now()->subSeconds(10))
            ->first();

        if ($recentCall) {
            Log::info('SMS GATEWAY: Duplicate call ignored for ' . $phoneNumber);
            return response()->json(['success' => true, 'message' => 'Duplicate call ignored']);
        }

        $client = Client::where('phone', 'like', '%' . substr($phoneNumber, -8) . '%')->first();
        $callerName = $request->caller_name ?? ($client ? $client->name : 'Unknown');

        try {
            CallLog::create([
                'phone_number' => $phoneNumber,
                'caller_name' => $callerName,
                'type' => $request->type ?? 'incoming',
                'call_time' => now(), // SHTOJME KOHEN KETU
                'is_client' => (bool)$client,
            ]);
            Log::info('--- SMS GATEWAY: Success! Call logged for ' . $callerName);
        } catch (\Exception $e) {
            Log::error('SMS GATEWAY: Database Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Database error'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Call logged']);
    }
}
