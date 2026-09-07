<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MobileBookingController extends Controller
{
    public function calendarStats(Request $request)
    {
        $month = $request->get('month', Carbon::now()->month);
        $year = $request->get('year', Carbon::now()->year);
        $barberId = $request->get('barber_id');

        $query = Booking::select(DB::raw('DATE(appointment_datetime) as date'), DB::raw('count(*) as count'))
            ->whereMonth('appointment_datetime', $month)
            ->whereYear('appointment_datetime', $year);

        if ($barberId) {
            $query->where('barber_id', $barberId);
        }

        $stats = $query->groupBy('date')
            ->get()
            ->pluck('count', 'date');

        return response()->json(['data' => $stats]);
    }

    public function daySchedule(Request $request)
    {
        $date = $request->get('date', Carbon::today()->toDateString());
        $barberId = $request->get('barber_id');

        $query = Booking::with(['customer', 'barber', 'service'])
            ->whereDate('appointment_datetime', $date)
            ->orderBy('appointment_datetime');

        if ($barberId) {
            $query->where('barber_id', $barberId);
        }

        $bookings = $query->get();

        // Shtojme ticks (reminders)
        $bookings->transform(function($b) {
            $b->ticks = DB::table('ba_reminders')
                ->where('booking_id', $b->id)
                ->whereNotNull('sent_at')
                ->count();
            return $b;
        });

        // Timeline logic: Vetem nese kemi zgjedhur nje berber specifik shfaqim slote "Te Lira"
        if ($barberId) {
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
            return response()->json(['data' => $slots, 'mode' => 'timeline']);
        }

        // Perndryshe kthejme vetem listen e rezervimeve aktive per te gjithe
        return response()->json([
            'data' => $bookings->map(fn($b) => [
                'time' => Carbon::parse($b->appointment_datetime)->format('H:i'),
                'booking' => $b,
                'is_free' => false
            ]),
            'mode' => 'list'
        ]);
    }
}
