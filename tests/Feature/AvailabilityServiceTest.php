<?php

use App\Models\BerberApp\Barber;
use App\Models\BerberApp\BarberSchedule;
use App\Models\BerberApp\BarberAbsence;
use App\Models\BerberApp\Booking;
use App\Models\BerberApp\Service;
use App\Services\AvailabilityService;
use Carbon\Carbon;

test('it returns available slots based on barber schedule', function () {
    $barber = Barber::create(['name' => 'Test Barber', 'commission_rate' => 0.1]);

    // Monday schedule: 09:00 - 12:00
    BarberSchedule::create([
        'barber_id' => $barber->id,
        'day_of_week' => 1, // Monday
        'start_time' => '09:00',
        'end_time' => '12:00',
        'is_working' => true
    ]);

    $service = new AvailabilityService();
    $date = Carbon::parse('2026-09-07'); // A Monday

    $slots = $service->getAvailableSlots($barber, $date, 30);

    // Should have slots: 09:00, 09:15, 09:30, 09:45, 10:00, 10:15, 10:30, 10:45, 11:00, 11:15, 11:30
    // Total 11 slots
    expect($slots)->toHaveCount(11);
    // loop: 09:00 + 30 = 09:30 <= 12:00 (Y)
    // 09:15 + 30 = 09:45 <= 12:00 (Y)
    // ...
    // 11:30 + 30 = 12:00 <= 12:00 (Y)
    // 11:45 + 30 = 12:15 <= 12:00 (N)
    // 09:00, 09:15, 09:30, 09:45, 10:00, 10:15, 10:30, 10:45, 11:00, 11:15, 11:30 -> 11 slots.
    expect($slots)->toContain('09:00', '11:30');
});

test('it blocks lunch break slots', function () {
    $barber = Barber::create(['name' => 'Test Barber 2', 'commission_rate' => 0.1]);

    BarberSchedule::create([
        'barber_id' => $barber->id,
        'day_of_week' => 1,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'break_start_time' => '12:00',
        'break_end_time' => '13:00',
        'is_working' => true
    ]);

    $service = new AvailabilityService();
    $date = Carbon::parse('2026-09-07');

    $slots = $service->getAvailableSlots($barber, $date, 60);

    // 12:00 slot should be blocked (it overlaps with 12:00-13:00 break)
    // 11:15 slot (lasts till 12:15) should be blocked
    // 11:00 slot (lasts till 12:00) should be OK
    // 13:00 slot (starts at 13:00) should be OK
    expect($slots)->not->toContain('11:15', '11:30', '11:45', '12:00', '12:15', '12:30', '12:45');
    expect($slots)->toContain('11:00', '13:00');
});

test('it blocks slots during absences', function () {
    $barber = Barber::create(['name' => 'Test Barber 3', 'commission_rate' => 0.1]);

    BarberSchedule::create([
        'barber_id' => $barber->id,
        'day_of_week' => 1,
        'start_time' => '09:00',
        'end_time' => '17:00',
        'is_working' => true
    ]);

    BarberAbsence::create([
        'barber_id' => $barber->id,
        'date' => '2026-09-07',
        'start_time' => '10:00',
        'end_time' => '11:00'
    ]);

    $service = new AvailabilityService();
    $date = Carbon::parse('2026-09-07');

    $slots = $service->getAvailableSlots($barber, $date, 30);

    expect($slots)->not->toContain('09:45', '10:00', '10:15', '10:30', '10:45');
    expect($slots)->toContain('09:30', '11:00');
});
