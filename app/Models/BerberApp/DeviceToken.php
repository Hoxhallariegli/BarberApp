<?php

namespace App\Models\BerberApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory;
    protected $table = 'ba_device_tokens';
    protected $fillable = ['user_id', 'fcm_token', 'device_type'];
    protected function casts(): array { return [
        ]; }
    public static function rules($id = null): array { return [
            'user_id' => ['nullable', 'integer'],
            'fcm_token' => ['required', 'string', 'max:255'],
            'device_type' => ['nullable', 'string', 'max:255'],
        ]; }
    public static function sortable(): array { return ['id', 'user_id', 'fcm_token', 'device_type']; }

}