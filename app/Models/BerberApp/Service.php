<?php

namespace App\Models\BerberApp;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $table = 'ba_services';
    protected $fillable = ['name', 'price', 'duration_minutes'];
    protected function casts(): array { return [
            'price' => 'decimal:2',
            'name' => 'array',
        ]; }
    public static function rules($id = null): array { return [
            'name' => ['required', 'array'],
            'name.*' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric'],
            'duration_minutes' => ['nullable', 'integer'],
        ]; }
    public static function sortable(): array { return ['id', 'name', 'price', 'duration_minutes']; }

    public function getNameAttribute($value)
    {
        $names = json_decode($value, true);
        if (!is_array($names)) return $value;

        $locale = app()->getLocale();
        return $names[$locale] ?? $names['en'] ?? array_values($names)[0] ?? '';
    }

    public function getTranslation($field, $lang)
    {
        $translations = $this->getAttributes()[$field] ?? null;
        if (!$translations) return '';

        $data = json_decode($translations, true);
        return $data[$lang] ?? '';
    }


}
