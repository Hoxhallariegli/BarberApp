<?php

namespace App\Services;

use App\Models\BerberApp\Barber;
use App\Models\BerberApp\Booking;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class AvailabilityService
{
    public function getAvailableSlots(Barber $barber, Carbon $date, int $durationMinutes = 30, $excludeId = null, bool $includePast = false)
    {
        $dayOfWeek = $date->dayOfWeek;
        $schedule = $barber->schedules()->where('day_of_week', $dayOfWeek)->where('is_working', true)->first();

        if (!$schedule) return [];

        $start = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->start_time);
        $end = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->end_time);

        // If not forced to include past, filter by "now" for today
        if (!$includePast && $date->isToday()) {
            $now = Carbon::now();
            if ($start->lt($now)) {
                $start = $now->copy();
                $minutes = ceil($start->minute / 15) * 15;
                if ($minutes == 60) $start->addHour()->startOfHour();
                else $start->minute($minutes)->second(0);
            }
        }

        $slots = [];
        $interval = CarbonInterval::minutes(15);

        $absences = $barber->absences()->whereDate('date', $date->toDateString())->get();
        $query = Booking::where('barber_id', $barber->id)
            ->whereDate('appointment_datetime', $date->toDateString())
            ->where('status', '!=', 'cancelled')
            ->with(['service', 'services']);

        if ($excludeId) $query->where('id', '!=', $excludeId);
        $bookings = $query->get();

        for ($time = $start->copy(); $time->copy()->addMinutes($durationMinutes)->lte($end); $time->add($interval)) {
            $slotStart = $time->copy();
            $slotEnd = $time->copy()->addMinutes($durationMinutes);

            if ($this->isAvailable($slotStart, $slotEnd, $schedule, $absences, $bookings)) {
                $slots[] = $slotStart->format('H:i');
            }
        }
        return $slots;
    }

    private function isAvailable($slotStart, $slotEnd, $schedule, $absences, $bookings)
    {
        if ($schedule->break_start_time && $schedule->break_end_time) {
            $breakStart = Carbon::parse($slotStart->format('Y-m-d') . ' ' . $schedule->break_start_time);
            $breakEnd = Carbon::parse($slotStart->format('Y-m-d') . ' ' . $schedule->break_end_time);
            if ($slotStart->lt($breakEnd) && $slotEnd->gt($breakStart)) return false;
        }

        foreach ($absences as $absence) {
            $absStart = $absence->start_time ? Carbon::parse($slotStart->format('Y-m-d') . ' ' . $absence->start_time) : null;
            $absEnd = $absence->end_time ? Carbon::parse($slotStart->format('Y-m-d') . ' ' . $absence->end_time) : null;
            if (!$absStart || !$absEnd) return false;
            if ($slotStart->lt($absEnd) && $slotEnd->gt($absStart)) return false;
        }

        foreach ($bookings as $booking) {
            $bStart = $booking->appointment_datetime;
            $bDuration = $booking->services->sum('duration_minutes') ?: ($booking->service ? $booking->service->duration_minutes : 30);
            $bEnd = (clone $bStart)->addMinutes($bDuration);
            if ($slotStart->lt($bEnd) && $slotEnd->gt($bStart)) return false;
        }
        return true;
    }
}
