<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoRule extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    public function promoRuleCustomerCategories(): HasMany {
        return $this->hasMany(PromoRuleCustomerCategory::class);
    }

    public function promoRuleOrderTypes(): HasMany {
        return $this->hasMany(PromoRuleOrderType::class);
    }

    public function promoRuleProducts(): HasMany {
        return $this->hasMany(PromoRuleProduct::class);
    }
}
