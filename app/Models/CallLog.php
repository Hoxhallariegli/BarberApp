<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    use HasFactory;
    protected $fillable = ['phone_number', 'caller_name', 'type', 'call_time', 'is_client'];
    protected function casts(): array { return [
            'call_time' => 'datetime',
            'is_client' => 'boolean',
        ]; }
    public static function rules($id = null): array { return [
            'phone_number' => ['required', 'string', 'max:255'],
            'caller_name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', \Illuminate\Validation\Rule::in(['incoming', 'missed', 'outgoing'])],
            'call_time' => ['required', 'date'],
            'is_client' => ['required', 'boolean'],
        ]; }
    public static function sortable(): array { return ['id', 'phone_number', 'caller_name', 'type', 'call_time', 'is_client']; }

}