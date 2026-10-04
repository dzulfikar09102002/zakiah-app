<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalEntity extends Model
{
    use SoftDeletes;

    protected $table = 'legal_entities';

    protected $fillable = [
        'entity_id',
        'code',
        'initial',
        'name',
        'legal_type',
        'npwp',
        'is_pkp',
        'full_address',
        'postal_code',
        'city',
        'province',
        'country',
        'phone_number',
        'phone_number_country_code',
        'email',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
        'invoice_prefix',
        'image_url',
        'icon_image_url',
        'status',
    ];

    protected $casts = [
        'is_pkp' => 'boolean',
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

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function legalEntityLocations(): HasMany
    {
        return $this->hasMany(LegalEntityLocation::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'legal_entity_locations')
            ->withPivot(['id'])
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // ---- Scopes ----------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePkp(Builder $query): Builder
    {
        return $query->where('is_pkp', true);
    }

    public function scopeForEntity(Builder $query, int $entityId): Builder
    {
        return $query->where('entity_id', $entityId);
    }

    public function scopeForLocation(Builder $query, int $locationId): Builder
    {
        return $query->whereHas('legalEntityLocations', fn (Builder $q) => $q->where('location_id', $locationId));
    }
}
