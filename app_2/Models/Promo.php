<?php

namespace App\Models;

use App\Enums\PromoStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [
        'id',
        'entity_id',
        'code',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
        'updated_by',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PromoStatusEnum::class,
            'auto_apply' => 'boolean',
            'combine_promo' => 'boolean',
            'free_of_charge' => 'boolean',
            'select_all_location' => 'boolean',
        ];
    }

    public function promoRule(): HasOne {
        return $this->hasOne(PromoRule::class);
    }

    public function promoReward(): HasOne {
        return $this->hasOne(PromoReward::class);
    }

    public function ownerLocation(): BelongsTo {
        return $this->belongsTo(Location::class);
    }
}
