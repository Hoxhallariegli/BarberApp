<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
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
        $template = self::where('type', $type)->where('is_active', true)->first();
        if (!$template) return null;

        // Try provided locale, then default to 'sq', then whatever is available
        $locale = $locale ?: 'sq';

        if (isset($template->body[$locale])) {
            return $template->body[$locale];
        }

        return $template->body['sq'] ?? ($template->body[array_key_first($template->body)] ?? null);
    }
}
