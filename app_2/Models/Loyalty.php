<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loyalty extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
        'entity_id',
        'code',
    ];
    
    protected function casts(): array
    {
        return [
            'allow_multiple' => 'bool',
            'include_discount_and_promo' => 'bool',
            'include_surcharge' => 'bool',
            'include_free_of_charge' => 'bool',
            'include_tax' => 'bool',
            'include_service_charge' => 'bool',
            'select_all_location' => 'bool',
            'allow_convert_point_as_amount' => 'bool',
        ];
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function rewardProducts(): HasMany {
        return $this->hasMany(LoyaltyRewardProduct::class);
    }
}
