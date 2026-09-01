<?php

namespace App\Models\BerberApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;
    protected $table = 'ba_reminders';
    protected $fillable = ['booking_id', 'reminder_type', 'sent_at', 'status', 'send_at'];
    protected function casts(): array { return [
            'sent_at' => 'datetime',
            'send_at' => 'datetime',
        ]; }
    public static function rules($id = null): array { return [
            'booking_id' => ['required', 'integer'],
            'reminder_type' => ['required', 'string', 'max:255'],
            'sent_at' => ['nullable', 'date'],
        ]; }
    public static function sortable(): array { return ['id', 'booking_id', 'reminder_type', 'sent_at']; }

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BerberApp\Booking::class, 'booking_id'); }

}
