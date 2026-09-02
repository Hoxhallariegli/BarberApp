<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CallLogController;
use App\Http\Controllers\Api\SmsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/calls/log', [CallLogController::class, 'log']);
Route::post('/sms/register', [SmsController::class, 'register']);
Route::post('/sms/status', [SmsController::class, 'statusUpdate']);
Route::post('/sms/debug', function(Request $request) {
    Log::warning("APK DEBUG: " . $request->message, $request->all());
    return response()->json(['success' => true]);
});

// Call Job Status update (referenced in mobile app main.dart)
Route::post('/call-jobs/{id}/status', function($id, \Illuminate\Http\Request $request) {
    $job = \App\Models\CallJob::find($id);
    if ($job) {
        $job->update([
            'status' => $request->status,
            'error_message' => $request->error_message,
            'updated_at' => $request->occurred_at ?? now(),
        ]);
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false, 'message' => 'Job not found'], 404);
});
