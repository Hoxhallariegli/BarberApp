<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CallJob extends Model
{
    protected $fillable = ['phone_number', 'status', 'device_id', 'error_message'];
}
