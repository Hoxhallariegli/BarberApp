<?php

namespace App\Models\BerberApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $table = 'ba_bookings';
    protected $fillable = ['token', 'customer_id', 'barber_id', 'service_id', 'appointment_datetime', 'customer_name', 'customer_phone', 'status', 'locale', 'reminder_enabled', 'reminder_minutes', 'fcm_token'];

    protected function casts(): array { return [
            'appointment_datetime' => 'datetime',
        ]; }
    public static function rules($id = null): array { return [
            'customer_id' => ['required', 'integer'],
            'barber_id' => ['required', 'integer'],
            'service_id' => ['required', 'integer'],
            'appointment_datetime' => ['required', 'date'],
        ]; }
    public static function sortable(): array { return ['id', 'customer_id', 'barber_id', 'service_id', 'appointment_datetime']; }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BerberApp\Customer::class, 'customer_id'); }

    public function barber(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BerberApp\Barber::class, 'barber_id'); }

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BerberApp\Service::class, 'service_id'); }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany { return $this->hasMany(\App\Models\BerberApp\Payment::class, 'booking_id'); }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($booking) {
            $booking->token = \Illuminate\Support\Str::random(32);
        });
    }


}
