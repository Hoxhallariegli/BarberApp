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
        $dateStr = $request->get('date', Carbon::today()->toDateString());
        $selectedDate = Carbon::parse($dateStr);
        $barberId = $request->get('barber_id');
        $now = Carbon::now();

        // 1. Marrim te gjitha rezervimet per kete date dhe filter (nese ka)
        $query = Booking::with(['customer', 'barber', 'service'])
            ->whereDate('appointment_datetime', $dateStr)
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

        // 2. Nese eshte perzgjedhur nje berber, krijojme timeline me orare pune
        if ($barberId && $barberId !== 'null' && $barberId !== '') {
            $barber = Barber::find($barberId);
            if (!$barber) return response()->json(['data' => [], 'mode' => 'list']);

            $schedule = $barber->schedules()->where('day_of_week', $selectedDate->dayOfWeek)->first();

            // Edhe nese nuk ka orar pune, tregojme rezervimet qe ka
            $startStr = $schedule?->start_time ?: '08:00';
            $endStr = $schedule?->end_time ?: '21:00';
            $isWorking = $schedule?->is_working ?? true;

            $slots = [];
            $start = Carbon::parse($dateStr . ' ' . $startStr);
            $end = Carbon::parse($dateStr . ' ' . $endStr);

            // Shkojme me hapa 15 minuta per precizion maksimal
            while ($start < $end) {
                $currentTime = $start->format('H:i');

                // A ka rezervim qe nis fiks ketu?
                $exactBooking = $bookings->first(fn($b) => Carbon::parse($b->appointment_datetime)->format('H:i') === $currentTime);

                // A eshte kjo minute e zene nga nje rezervim qe ka nisur me pare?
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
                    $duration = $exactBooking->service ? ($exactBooking->service->duration_minutes ?: 30) : 30;
                    $start->addMinutes($duration);
                    continue;
                }

                // Shfaqim slot te lire vetem nese berberi punon dhe koha nuk ka kaluar
                if (!$isOccupied && $isWorking) {
                    $isPast = $selectedDate->isToday() ? $start->lt($now) : $selectedDate->isPast();

                    if (!$isPast && ($start->minute == 0 || $start->minute == 30)) {
                        $slots[] = [
                            'time' => $currentTime,
                            'booking' => null,
                            'is_free' => true
                        ];
                    }
                }

                $start->addMinutes(15);
            }

            // Nese kemi rezervime jashte orarit zyrtar, i shtojme ne fund te listes
            foreach ($bookings as $b) {
                $bTime = Carbon::parse($b->appointment_datetime)->format('H:i');
                if (!collect($slots)->contains('time', $bTime)) {
                    $slots[] = ['time' => $bTime, 'booking' => $b, 'is_free' => false];
                }
            }

            // Ri-renditim slotet sipas kohes
            usort($slots, fn($a, $b) => strcmp($a['time'], $b['time']));

            return response()->json(['data' => $slots, 'mode' => 'timeline']);
        }

        // 3. Per "Te Gjithe", kthejme listen e thjeshte te rezervimeve
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
