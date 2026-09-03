<?php

namespace App\Livewire\Admin\BerberApp\Barbers\Schedule;

use App\Models\BerberApp\Barber;
use App\Models\BerberApp\BarberSchedule;
use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Configure Hours')]
class BarberScheduleConfig extends Component
{
    public Barber $barber;
    public $schedules = [];

    protected $rules = [
        'schedules.*.start_time' => 'nullable',
        'schedules.*.end_time' => 'nullable',
        'schedules.*.break_start_time' => 'nullable',
        'schedules.*.break_end_time' => 'nullable',
        'schedules.*.is_working' => 'boolean',
    ];

    public function mount(Barber $barber)
    {
        $this->barber = $barber;
        $existingSchedules = $barber->schedules->keyBy('day_of_week');

        $days = [
            1 => __('barbers.Monday'),
            2 => __('barbers.Tuesday'),
            3 => __('barbers.Wednesday'),
            4 => __('barbers.Thursday'),
            5 => __('barbers.Friday'),
            6 => __('barbers.Saturday'),
            0 => __('barbers.Sunday'),
        ];

        foreach ($days as $dayNum => $dayName) {
            $schedule = $existingSchedules->get($dayNum);

            $this->schedules[$dayNum] = [
                'day_name' => $dayName,
                'is_working' => $schedule ? (bool)$schedule->is_working : ($dayNum != 0),
                'start_time' => $schedule ? ($schedule->start_time ?? '') : '09:00',
                'end_time' => $schedule ? ($schedule->end_time ?? '') : '18:00',
                'break_start_time' => $schedule ? ($schedule->break_start_time ?? '') : '13:00',
                'break_end_time' => $schedule ? ($schedule->break_end_time ?? '') : '14:00',
            ];
        }
    }

    public function save()
    {
        foreach ($this->schedules as $dayNum => $data) {
            BarberSchedule::updateOrCreate(
                ['barber_id' => $this->barber->id, 'day_of_week' => $dayNum],
                [
                    'start_time' => $data['start_time'] ?: null,
                    'end_time' => $data['end_time'] ?: null,
                    'break_start_time' => $data['break_start_time'] ?: null,
                    'break_end_time' => $data['break_end_time'] ?: null,
                    'is_working' => $data['is_working'],
                ]
            );
        }

        session()->flash('success', __('barbers.Hours saved successfully!'));
    }

    public function render()
    {
        return view('livewire.admin.berber-app.barbers.schedule.barber-schedule-config')
            ->layout('components.layouts.app');
    }
}
