<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoReward extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
        'promo_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'promo_id',
        'deleted_at',
        'created_at',
        'updated_at',
        'updated_by',
        'created_by',
    ];

    public function promoRewardProducts(): HasMany {
        return $this->hasMany(PromoRewardProduct::class);
    }

    protected function casts(): array
    {
        return [
            'percentage' => 'boolean',
        ];
    }
}
