<?php

namespace App\Models\BerberApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarberAbsence extends Model
{
    use HasFactory;

    protected $table = 'ba_barber_absences';

    protected $fillable = [
        'barber_id',
        'date',
        'start_time',
        'end_time',
        'reason'
    ];

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barber_id');
    }
}
