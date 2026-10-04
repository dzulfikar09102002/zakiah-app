<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalEntityLocation extends Model
{
    use SoftDeletes;

    protected $table = 'legal_entity_locations';

    protected $fillable = [
        'legal_entity_id',
        'location_id',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->created_by ??= auth()->id();
            $model->updated_by ??= auth()->id();
        });

        static::updating(function (self $model) {
            $model->updated_by = auth()->id();
        });
    }

    // ---- Relations -------------------------------------------------

    public function legalEntity(): BelongsTo
    {
        return $this->belongsTo(LegalEntity::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
