<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\BerberApp\Booking;
use App\Models\BerberApp\Barber;
use App\Models\BerberApp\Service;
use App\Services\AvailabilityService;
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
            ->whereYear('appointment_datetime', $year)
            ->where('status', '!=', 'cancelled');

        if ($barberId && $barberId !== 'null' && $barberId !== '') {
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
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_datetime');

        if ($barberId && $barberId !== 'null' && $barberId !== '') {
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

        // Nese kemi zgjedhur nje berber, tregojme timeline-in e plote me orare te lira
        if ($barberId && $barberId !== 'null' && $barberId !== '') {
            $barber = Barber::find($barberId);
            if (!$barber) return response()->json(['data' => [], 'mode' => 'list']);

            $slots = [];
            $schedule = $barber->schedules()->where('day_of_week', Carbon::parse($date)->dayOfWeek)->first();

            if (!$schedule || !$schedule->is_working) {
                return response()->json(['data' => [], 'mode' => 'closed', 'message' => 'Berberi nuk punon këtë ditë.']);
            }

            $start = Carbon::parse($date . ' ' . $schedule->start_time);
            $end = Carbon::parse($date . ' ' . $schedule->end_time);

            while ($start < $end) {
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

        // Perndryshe kthejme listen e rezervimeve per gjithe salonin
        return response()->json([
            'data' => $bookings->map(fn($b) => [
                'time' => Carbon::parse($b->appointment_datetime)->format('H:i'),
                'booking' => $b,
                'is_free' => false
            ]),
            'mode' => 'list'
        ]);
    }

    public function availableSlots(Request $request)
    {
        $request->validate([
            'barber_id' => 'required|exists:ba_barbers,id',
            'service_id' => 'required|exists:ba_services,id',
            'date' => 'required|date',
        ]);

        $barber = Barber::findOrFail($request->barber_id);
        $service = Service::findOrFail($request->service_id);
        $date = Carbon::parse($request->date);

        $availabilityService = app(AvailabilityService::class);
        $slots = $availabilityService->getAvailableSlots($barber, $date, $service->duration_minutes ?: 30);

        return response()->json(['data' => $slots]);
    }
}
