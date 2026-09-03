<?php

namespace App\Models\BerberApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarberSchedule extends Model
{
    use HasFactory;

    protected $table = 'ba_barber_schedules';

    protected $fillable = [
        'id',
        'barber_id',
        'day_of_week',
        'start_time',
        'end_time',
        'break_start_time',
        'break_end_time',
        'is_working',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'is_working' => 'boolean',
    ];

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barber_id');
    }
}
