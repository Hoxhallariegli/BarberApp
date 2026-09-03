<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'body',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'body' => 'array',
    ];

    public static function getTemplate(string $type, ?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $template = self::where('type', $type)->where('is_active', true)->first();

        if (!$template || !isset($template->body[$locale])) {
            // Fallback to first available language if current locale not found
            return $template->body[array_key_first($template->body)] ?? null;
        }

        return $template->body[$locale];
    }
}
