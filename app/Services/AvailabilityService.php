<?php

namespace App\Services;

use App\Models\BerberApp\Barber;
use App\Models\BerberApp\Booking;
use Carbon\Carbon;
use Carbon\CarbonInterval;

class AvailabilityService
{
    /**
     * Get available slots for a barber on a specific date for a specific service duration.
     */
    public function getAvailableSlots(Barber $barber, Carbon $date, int $durationMinutes = 30)
    {
        $dayOfWeek = $date->dayOfWeek;
        $schedule = $barber->schedules()->where('day_of_week', $dayOfWeek)->where('is_working', true)->first();

        if (!$schedule) {
            return [];
        }

        $start = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->start_time);
        $end = Carbon::parse($date->format('Y-m-d') . ' ' . $schedule->end_time);

        // If the date is today, ensure we only start from the current time (+ a small buffer if needed)
        if ($date->isToday()) {
            $now = Carbon::now();
            if ($start->lt($now)) {
                $start = $now->copy();

                // Round to the next 15-minute interval for a cleaner start
                $minutes = ceil($start->minute / 15) * 15;
                if ($minutes == 60) {
                    $start->addHour()->startOfHour();
                } else {
                    $start->minute($minutes)->second(0);
                }
            }
        }

        $slots = [];
        $interval = CarbonInterval::minutes(15); // Check every 15 minutes for a starting point

        // Get absences for this day
        $absences = $barber->absences()->whereDate('date', $date->toDateString())->get();

        // Get existing bookings for this day
        $bookings = Booking::where('barber_id', $barber->id)
            ->whereDate('appointment_datetime', $date->toDateString())
            ->where('status', '!=', 'cancelled')
            ->with('service')
            ->get();

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
        // 1. Check Lunch Break
        if ($schedule->break_start_time && $schedule->break_end_time) {
            $breakStart = Carbon::parse($slotStart->format('Y-m-d') . ' ' . $schedule->break_start_time);
            $breakEnd = Carbon::parse($slotStart->format('Y-m-d') . ' ' . $schedule->break_end_time);

            if ($slotStart->lt($breakEnd) && $slotEnd->gt($breakStart)) {
                return false;
            }
        }

        // 2. Check Absences
        foreach ($absences as $absence) {
            $absStart = $absence->start_time ? Carbon::parse($slotStart->format('Y-m-d') . ' ' . $absence->start_time) : null;
            $absEnd = $absence->end_time ? Carbon::parse($slotStart->format('Y-m-d') . ' ' . $absence->end_time) : null;

            if (!$absStart || !$absEnd) {
                // Whole day absence
                return false;
            }

            if ($slotStart->lt($absEnd) && $slotEnd->gt($absStart)) {
                return false;
            }
        }

        // 3. Check Existing Bookings
        foreach ($bookings as $booking) {
            $bStart = $booking->appointment_datetime;
            $bDuration = $booking->service ? $booking->service->duration_minutes : 30;
            $bEnd = $bStart->copy()->addMinutes($bDuration);

            if ($slotStart->lt($bEnd) && $slotEnd->gt($bStart)) {
                return false;
            }
        }

        return true;
    }
}
