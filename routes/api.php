<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CallLogController;
use App\Http\Controllers\Api\SmsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public/System Routes
Route::post('/calls/log', [CallLogController::class, 'log']);
Route::post('/sms/register', [SmsController::class, 'register']);
Route::post('/sms/status', [SmsController::class, 'statusUpdate']);
Route::post('/sms/debug', function(\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::warning("APK DEBUG: " . $request->input('message'), $request->all());
    return response()->json(['success' => true]);
});

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

// Mobile Auth
Route::post('/mobile/login', [\App\Http\Controllers\Api\Mobile\AuthController::class, 'login']);

// Mobile PRO Dashboard & Resources
Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {

    // RRUGËT SPECIFIKE DUHET TË JENË TË PARAT
    Route::get('/bookings/calendar', [\App\Http\Controllers\Api\Mobile\MobileBookingController::class, 'calendarStats']);
    Route::get('/bookings/day-schedule', [\App\Http\Controllers\Api\Mobile\MobileBookingController::class, 'daySchedule']);
    Route::get('/bookings/available-slots', [\App\Http\Controllers\Api\Mobile\MobileBookingController::class, 'availableSlots']);

    // RESOURCES
    Route::apiResource('sms-templates', \App\Http\Controllers\Api\Mobile\SmsTemplateController::class);
    Route::apiResource('barbers', \App\Http\Controllers\Api\Mobile\BarberController::class);
    Route::apiResource('customers', \App\Http\Controllers\Api\Mobile\CustomerController::class);
    Route::apiResource('services', \App\Http\Controllers\Api\Mobile\ServiceController::class);
    Route::apiResource('bookings', \App\Http\Controllers\Api\Mobile\BookingController::class);
    Route::apiResource('payments', \App\Http\Controllers\Api\Mobile\PaymentController::class);
    Route::apiResource('reminders', \App\Http\Controllers\Api\Mobile\ReminderController::class);

    // DASHBOARD
    Route::post('/logout', [\App\Http\Controllers\Api\Mobile\AuthController::class, 'logout']);
    Route::get('/dashboard', [\App\Http\Controllers\Api\Mobile\MobileDashboardController::class, 'dashboard']);
    Route::get('/sms-settings', [\App\Http\Controllers\Api\Mobile\MobileDashboardController::class, 'smsSettings']);
});
