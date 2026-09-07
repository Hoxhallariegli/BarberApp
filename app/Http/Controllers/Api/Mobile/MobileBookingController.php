<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Booking;
use App\Models\BerberApp\Barber;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MobileBookingController extends Controller
{
    public function calendarStats(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);

        $stats = Booking::select(DB::raw('DATE(appointment_datetime) as date'), DB::raw('count(*) as count'))
            ->whereMonth('appointment_datetime', $month)
            ->whereYear('appointment_datetime', $year)
            ->groupBy('date')
            ->get()
            ->pluck('count', 'date');

        return response()->json(['data' => $stats]);
    }

    public function daySchedule(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());

        $bookings = Booking::with(['customer', 'barber', 'service'])
            ->whereDate('appointment_datetime', $date)
            ->orderBy('appointment_datetime')
            ->get();

        // Shtojme numrin e "ticks" per cdo booking bazuar te Reminders
        $bookings->transform(function($b) {
            $ticks = DB::table('ba_reminders')
                ->where('booking_id', $b->id)
                ->whereNotNull('sent_at')
                ->count();
            $b->ticks = $ticks;
            return $b;
        });

        // Gjenerojme slotet e mundshme (psh 08:00 - 21:00 cdo 30 min)
        $slots = [];
        $start = Carbon::parse($date)->setTime(8, 0);
        $end = Carbon::parse($date)->setTime(21, 0);

        while ($start <= $end) {
            $time = $start->format('H:i');
            $bookingAtSlot = $bookings->first(fn($b) => Carbon::parse($b->appointment_datetime)->format('H:i') === $time);

            $slots[] = [
                'time' => $time,
                'booking' => $bookingAtSlot,
                'is_free' => !$bookingAtSlot
            ];
            $start->addMinutes(30);
        }

        return response()->json(['data' => $slots]);
    }
}
