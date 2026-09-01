<?php

namespace App\Models\BerberApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $table = 'ba_payments';
    protected $fillable = ['booking_id', 'amount', 'status'];
    protected function casts(): array { return [
            'amount' => 'decimal:2',
        ]; }
    public static function rules($id = null): array { return [
            'booking_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric'],
            'status' => ['required', 'string', 'max:255'],
        ]; }
    public static function sortable(): array { return ['id', 'booking_id', 'amount', 'status']; }

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\BerberApp\Booking::class, 'booking_id'); }

}