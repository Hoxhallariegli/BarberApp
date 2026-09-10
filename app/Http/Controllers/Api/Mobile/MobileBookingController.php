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

        if ($barberId && $barberId !== 'null' && $barberId !== '') {
            $barber = Barber::find($barberId);
            if (!$barber) return response()->json(['data' => [], 'mode' => 'list']);

            $schedule = $barber->schedules()->where('day_of_week', $selectedDate->dayOfWeek)->first();
            $startStr = $schedule?->start_time ?: '08:00';
            $endStr = $schedule?->end_time ?: '21:00';
            $isWorking = $schedule?->is_working ?? true;

            $slots = [];
            $start = Carbon::parse($dateStr . ' ' . $startStr);
            $end = Carbon::parse($dateStr . ' ' . $endStr);

            if (!$isWorking) return response()->json(['data' => [], 'mode' => 'timeline', 'message' => 'Pushim']);

            while ($start < $end) {
                $currentTime = $start->format('H:i');

                $bookingAtThisTime = $bookings->first(function($b) use ($start) {
                    $bStart = Carbon::parse($b->appointment_datetime);
                    $duration = $b->services->isNotEmpty() ? $b->services->sum('duration_minutes') : ($b->service ? ($b->service->duration_minutes ?: 30) : 30);
                    $bEnd = (clone $bStart)->addMinutes($duration);
                    return $start >= $bStart && $start < $bEnd;
                });

                if ($bookingAtThisTime) {
                    if (Carbon::parse($bookingAtThisTime->appointment_datetime)->format('H:i') === $currentTime) {
                        $slots[] = ['time' => $currentTime, 'booking' => $bookingAtThisTime, 'is_free' => false];
                    }
                } else {
                    $slots[] = ['time' => $currentTime, 'booking' => null, 'is_free' => true];
                }
                $start->addMinutes(15);
            }
            return response()->json(['data' => $slots, 'mode' => 'timeline']);
        }

        return response()->json(['data' => $bookings->map(fn($b) => ['time' => Carbon::parse($b->appointment_datetime)->format('H:i'), 'booking' => $b, 'is_free' => false]), 'mode' => 'list']);
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
