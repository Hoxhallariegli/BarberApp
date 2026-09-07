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

        $bookings->transform(function($b) {
            $b->ticks = DB::table('ba_reminders')
                ->where('booking_id', $b->id)
                ->whereNotNull('sent_at')
                ->count();
            return $b;
        });

        if ($barberId && $barberId !== 'null' && $barberId !== '') {
            $barber = Barber::find($barberId);
            $schedule = $barber->schedules()->where('day_of_week', Carbon::parse($date)->dayOfWeek)->first();

            if (!$schedule || !$schedule->is_working) {
                return response()->json(['data' => [], 'mode' => 'closed', 'message' => 'Berberi nuk punon.']);
            }

            $slots = [];
            $start = Carbon::parse($date . ' ' . $schedule->start_time);
            $end = Carbon::parse($date . ' ' . $schedule->end_time);

            // Shkojme me hapa 15 minuta per precizion maksimal
            while ($start < $end) {
                $currentTime = $start->format('H:i');

                // 1. A ka nje rezervim qe nis fiks ne kete minute?
                $exactBooking = $bookings->first(fn($b) => Carbon::parse($b->appointment_datetime)->format('H:i') === $currentTime);

                // 2. A eshte kjo minute brenda kohezgjatjes se nje rezervimi tjeter?
                $isOccupied = $bookings->contains(function($b) use ($start) {
                    $bStart = Carbon::parse($b->appointment_datetime);
                    $duration = $b->service ? ($b->service->duration_minutes ?: 30) : 30;
                    $bEnd = (clone $bStart)->addMinutes($duration);
                    return $start >= $bStart && $start < $bEnd;
                });

                if ($exactBooking) {
                    $slots[] = [
                        'time' => $currentTime,
                        'booking' => $exactBooking,
                        'is_free' => false
                    ];
                    // Kapercejme kohen e sherbimit
                    $duration = $exactBooking->service ? ($exactBooking->service->duration_minutes ?: 30) : 30;
                    $start->addMinutes($duration);
                    continue;
                }

                if (!$isOccupied) {
                    // Shfaqim slot te lire vetem ne minutat 00 dhe 30 qe te mos behet lista shume e gjate
                    if ($start->minute == 0 || $start->minute == 30) {
                        $slots[] = [
                            'time' => $currentTime,
                            'booking' => null,
                            'is_free' => true
                        ];
                    }
                }

                $start->addMinutes(15);
            }
            return response()->json(['data' => $slots, 'mode' => 'timeline']);
        }

        // Per "Te Gjithe" kthejme vetem rezervimet aktive
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
        $barber = Barber::findOrFail($request->barber_id);
        $service = Service::findOrFail($request->service_id);
        $date = Carbon::parse($request->date);
        $availabilityService = app(AvailabilityService::class);
        $slots = $availabilityService->getAvailableSlots($barber, $date, $service->duration_minutes ?: 30);
        return response()->json(['data' => $slots]);
    }
}
