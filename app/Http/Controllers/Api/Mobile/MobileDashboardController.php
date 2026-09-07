<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Booking;
use App\Models\BerberApp\Customer;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MobileDashboardController extends Controller
{
    public function dashboard()
    {
        // Statistikat per sot
        $bookingsToday = Booking::whereDate('appointment_datetime', Carbon::today())->count();
        $totalCustomers = Customer::count();

        return response()->json([
            'success' => true,
            'stats' => [
                'bookings_today' => $bookingsToday,
                'total_customers' => $totalCustomers,
            ]
        ]);
    }

    public function getAvailableSlots(Request $request)
    {
        return response()->json(['data' => []]);
    }

    public function completePayment(Request $request, $id)
    {
        return response()->json(['success' => true]);
    }

    public function smsTemplates()
    {
        return response()->json(['data' => []]);
    }

    public function smsSettings()
    {
        return response()->json(['data' => []]);
    }
}
