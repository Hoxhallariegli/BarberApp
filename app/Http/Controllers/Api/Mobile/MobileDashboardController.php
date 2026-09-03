<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Barber;
use App\Models\BerberApp\Booking;
use App\Models\BerberApp\Customer;
use App\Models\BerberApp\Payment;
use App\Models\BerberApp\Reminder;
use App\Models\BerberApp\Service;
use App\Models\SmsTemplate;
use App\Models\SmsLog;
use App\Models\SmsDevice;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MobileDashboardController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();

        return response()->json([
            'stats' => [
                'bookings_today' => Booking::whereDate('appointment_datetime', $today)->count(),
                'total_customers' => Customer::count(),
                'total_revenue' => (float) Payment::sum('amount'),
                'pending_reminders' => Reminder::where('status', 'pending')->count(),
            ],
            'recent_bookings' => Booking::with(['customer', 'barber', 'service'])
                ->latest()
                ->take(5)
                ->get()
        ]);
    }

    public function barbers()
    {
        return response()->json(Barber::all());
    }

    public function bookings()
    {
        return response()->json(
            Booking::with(['customer', 'barber', 'service'])
                ->latest()
                ->paginate(20)
        );
    }

    public function customers()
    {
        return response()->json(Customer::latest()->paginate(20));
    }

    public function services()
    {
        return response()->json(Service::all());
    }

    public function payments()
    {
        return response()->json(
            Payment::with(['booking.customer'])
                ->latest()
                ->paginate(20)
        );
    }

    public function reminders()
    {
        return response()->json(
            Reminder::with(['booking.customer'])
                ->latest()
                ->paginate(20)
        );
    }

    public function smsTemplates()
    {
        return response()->json(SmsTemplate::all());
    }

    public function smsSettings()
    {
        // Kthejmë pajisjen aktive dhe disa statistika të SMS
        return response()->json([
            'device' => SmsDevice::where('is_active', true)->first(),
            'logs_count' => SmsLog::count(),
            'failed_count' => SmsLog::where('status', 'failed')->count(),
        ]);
    }
}
