<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Database\Factories\AuditTrailsFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditTrail extends Model
{
    /** @use HasFactory<AuditTrailsFactory> */
    use HasFactory;

    use HasUuid;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'link',
        'reference_id',
        'section',
        'type',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * @return BelongsTo<User, AuditTrail>
     */
    public function user(): BelongsTo
    {
        /** @var BelongsTo<User, AuditTrail> */
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public static function log($model, string $type, string $section): void
    {
        // Përdorim array_map për të siguruar që vlerat decimal të serializohen saktë
        $oldValues = $type === 'update' ? array_intersect_key($model->getOriginal(), $model->getDirty()) : null;
        $newValues = $type === 'update' ? $model->getDirty() : ($type === 'create' ? $model->toArray() : null);

        // Sigurohemi që çdo vlerë në array të jetë e tipit primitiv (string, int, float) për JSON
        if ($oldValues) $oldValues = json_decode(json_encode($oldValues), true);
        if ($newValues) $newValues = json_decode(json_encode($newValues), true);

        self::create([
            'user_id' => auth()->id(),
            'title' => ucfirst($type) . " " . class_basename($model),
            'reference_id' => (string)$model->id,
            'section' => $section,
            'type' => $type,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }

    protected static function newFactory(): AuditTrailsFactory
    {
        return AuditTrailsFactory::new();
    }
}
