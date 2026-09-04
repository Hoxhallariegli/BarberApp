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
Route::post('/sms/debug', function(\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::warning("APK DEBUG: " . $request->input('message'), $request->all());
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

// Mobile Pro Dashboard Routes
Route::post('/mobile/login', [\App\Http\Controllers\Api\Mobile\AuthController::class, 'login']);

Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {
    Route::apiResource('barbers', \App\Http\Controllers\Api\Mobile\BarberController::class);
    Route::apiResource('customers', \App\Http\Controllers\Api\Mobile\CustomerController::class);
    Route::apiResource('services', \App\Http\Controllers\Api\Mobile\ServiceController::class);
    Route::apiResource('bookings', \App\Http\Controllers\Api\Mobile\BookingController::class);
    Route::apiResource('payments', \App\Http\Controllers\Api\Mobile\PaymentController::class);
    Route::apiResource('reminders', \App\Http\Controllers\Api\Mobile\ReminderController::class);

    Route::post('/logout', [\App\Http\Controllers\Api\Mobile\AuthController::class, 'logout']);
    Route::get('/dashboard', [\App\Http\Controllers\Api\Mobile\MobileDashboardController::class, 'dashboard']);
    Route::get('/bookings/slots', [\App\Http\Controllers\Api\Mobile\MobileDashboardController::class, 'getAvailableSlots']);
    Route::post('/bookings/{id}/payment', [\App\Http\Controllers\Api\Mobile\MobileDashboardController::class, 'completePayment']);
    Route::get('/sms-templates', [\App\Http\Controllers\Api\Mobile\MobileDashboardController::class, 'smsTemplates']);
    Route::get('/sms-settings', [\App\Http\Controllers\Api\Mobile\MobileDashboardController::class, 'smsSettings']);
});
