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

        $query = DB::table('ba_bookings')
            ->select(DB::raw('DATE(appointment_datetime) as booking_date'), DB::raw('count(*) as total'))
            ->whereMonth('appointment_datetime', $month)
            ->whereYear('appointment_datetime', $year)
            ->where('status', '!=', 'cancelled');

        if ($barberId && $barberId !== 'null' && $barberId !== '') {
            $query->where('barber_id', $barberId);
        }

        $stats = $query->groupBy(DB::raw('DATE(appointment_datetime)'))
            ->get()
            ->pluck('total', 'booking_date');

        return response()->json(['data' => $stats]);
    }

    public function daySchedule(Request $request)
    {
        $dateStr = $request->get('date', Carbon::today()->toDateString());
        $selectedDate = Carbon::parse($dateStr);
        $barberId = $request->get('barber_id');
        $now = Carbon::now();

        $query = Booking::with(['customer', 'barber', 'service', 'services'])
            ->whereDate('appointment_datetime', $dateStr)
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_datetime');

        if ($barberId && $barberId !== 'null' && $barberId !== '') {
            $query->where('barber_id', $barberId);
        }

        $bookings = $query->get();

        // Shto oren lokale si string ne cdo rezervim per te shmangur gabimet e zonave kohore ne Flutter
        $bookings->each(function($b) {
            $b->local_time = Carbon::parse($b->appointment_datetime)->format('H:i');
        });

        if ($barberId && $barberId !== 'null' && $barberId !== '') {
            $barber = Barber::find($barberId);
            if (!$barber) return response()->json(['data' => [], 'mode' => 'list']);

            $schedule = $barber->schedules()->where('day_of_week', $selectedDate->dayOfWeek)->first();

            if (!$schedule || !$schedule->is_working) {
                return response()->json(['data' => [], 'mode' => 'timeline', 'message' => 'Berberi është pushim sot.']);
            }

            $startStr = $schedule->start_time ?: '09:00';
            $endStr = $schedule->end_time ?: '21:00';

            $slots = [];
            $start = Carbon::parse($dateStr . ' ' . $startStr);
            $end = Carbon::parse($dateStr . ' ' . $endStr);

            while ($start < $end) {
                $currentTime = $start->format('H:i');

                $bookingAtThisTime = $bookings->first(function($b) use ($currentTime) {
                   return $b->local_time === $currentTime;
                });

                if ($bookingAtThisTime) {
                    $slots[] = ['time' => $currentTime, 'booking' => $bookingAtThisTime, 'is_free' => false];
                    $duration = $bookingAtThisTime->services->isNotEmpty()
                        ? $bookingAtThisTime->services->sum('duration_minutes')
                        : ($bookingAtThisTime->service ? ($bookingAtThisTime->service->duration_minutes ?: 30) : 30);
                    $start->addMinutes($duration);
                } else {
                    $slots[] = ['time' => $currentTime, 'booking' => null, 'is_free' => true];
                    $start->addMinutes(15);
                }
            }
            return response()->json(['data' => $slots, 'mode' => 'timeline']);
        }

        return response()->json([
            'data' => $bookings->map(fn($b) => [
                'time' => $b->local_time,
                'booking' => $b,
                'is_free' => false
            ]),
            'mode' => 'list'
        ]);
    }

    public function availableSlots(Request $request)
    {
        $barberId = $request->barber_id;
        if (!$barberId) return response()->json(['data' => []]);

        $barber = Barber::findOrFail($barberId);
        $date = Carbon::parse($request->date);
        $excludeId = $request->get('exclude_booking_id');
        if ($excludeId === 'null' || $excludeId === '') $excludeId = null;

        $duration = (int) $request->get('duration_minutes');
        if (!$duration && $request->service_id) {
            $service = Service::find($request->service_id);
            $duration = $service?->duration_minutes ?: 30;
        }

        $availabilityService = app(AvailabilityService::class);
        $slots = $availabilityService->getAvailableSlots($barber, $date, $duration ?: 30, $excludeId);
        return response()->json(['data' => $slots]);
    }
}
