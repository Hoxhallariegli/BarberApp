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

        $query = Booking::with(['customer', 'barber', 'service', 'services'])
            ->whereDate('appointment_datetime', $dateStr)
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_datetime');

        if ($barberId && $barberId !== 'null' && $barberId !== '') {
            $query->where('barber_id', $barberId);
        }

        $bookings = $query->get();

        $bookings->each(function($b) {
            $b->local_time = substr($b->getRawOriginal('appointment_datetime'), 11, 5);
        });

        if ($barberId && $barberId !== 'null' && $barberId !== '') {
            $barber = Barber::find($barberId);
            if (!$barber) return response()->json(['data' => [], 'mode' => 'list']);

            $schedule = $barber->schedules()->where('day_of_week', $selectedDate->dayOfWeek)->first();

            if (!$schedule || !$schedule->is_working) {
                return response()->json(['data' => [], 'mode' => 'timeline', 'message' => 'Berberi është pushim sot.']);
            }

            $start = Carbon::parse($dateStr . ' ' . ($schedule->start_time ?: '09:00'));
            $end = Carbon::parse($dateStr . ' ' . ($schedule->end_time ?: '21:00'));

            $timeline = [];
            $currentTime = $start->copy();

            // RREGULLI I RI: Eci vetem me hapa 15 minutash (Standard)
            while ($currentTime < $end) {
                $timeStr = $currentTime->format('H:i');

                // Kontrollojme nese ka rezervim ne kete moment
                $booking = $bookings->first(function($b) use ($timeStr) {
                   return $b->local_time === $timeStr;
                });

                if ($booking) {
                    $timeline[] = ['time' => $timeStr, 'booking' => $booking, 'is_free' => false];
                    $duration = $booking->services->isNotEmpty() ? $booking->services->sum('duration_minutes') : ($booking->service ? $booking->service->duration_minutes : 30);

                    // Avancojme kohen pas rezervimit te rrumbullakosur ne 15 minutat me te aferta
                    $rawEnd = (clone $currentTime)->addMinutes((int)$duration);
                    $currentTime->addMinutes(ceil($duration / 15) * 15);
                } else {
                    // Shto sllot te lire vetem ne minutat 00, 15, 30, 45
                    if ($currentTime->minute % 15 == 0) {
                        $timeline[] = ['time' => $timeStr, 'booking' => null, 'is_free' => true];
                    }
                    $currentTime->addMinutes(15);
                }
            }

            return response()->json(['data' => $timeline, 'mode' => 'timeline']);
        }

        return response()->json([
            'data' => $bookings->map(fn($b) => ['time' => $b->local_time, 'booking' => $b, 'is_free' => false]),
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
        $includePast = filter_var($request->get('include_past'), FILTER_VALIDATE_BOOLEAN);

        $duration = (int) $request->get('duration_minutes');
        if (!$duration) $duration = 15;

        $availabilityService = app(AvailabilityService::class);
        $slots = $availabilityService->getAvailableSlots($barber, $date, max($duration, 15), $excludeId, $includePast);

        // FILTRIMI I SLLOTEVE: Vetem hapa 15 minutash (00, 15, 30, 45)
        $slots = array_values(array_filter($slots, function($s) {
            $min = (int)substr($s, 3, 2);
            return $min % 15 === 0;
        }));

        return response()->json(['data' => $slots]);
    }
}
