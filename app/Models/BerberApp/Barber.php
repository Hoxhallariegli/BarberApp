<?php

namespace App\Models\BerberApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    use HasFactory;
    protected $table = 'ba_barbers';
    protected $fillable = ['name', 'specialization', 'phone', 'commission_rate', 'photo'];

    public function schedules()
    {
        return $this->hasMany(BarberSchedule::class, 'barber_id');
    }

    public function absences()
    {
        return $this->hasMany(BarberAbsence::class, 'barber_id');
    }

    protected function casts(): array { return [
            'commission_rate' => 'decimal:2',
        ]; }
    public static function rules($id = null): array { return [
            'name' => ['required', 'string', 'max:255'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'commission_rate' => ['nullable', 'numeric'],
            'photo' => ['nullable', 'max:255'],
        ]; }
    public static function sortable(): array { return ['id', 'name', 'specialization', 'phone', 'commission_rate', 'photo']; }

}
