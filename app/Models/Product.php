<?php

namespace App\Models;

use App\Enums\TaxSettingEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $casts = [
        'total_stock' => 'integer',
    ];
    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'description',
        'product_category_id',
        'child_product_category_id',
        'product_unit_id',
        'product_sell_unit_id',
        'location_id',
        'image_url',
        'sell_to_customer',
        'service',
        'modifier',
        'allow_custom_price',
        'select_all_location',
        'location_ids',
        'exclude_location_ids',
        'tax_id',
        'tax_setting',
        'sell_price',
        'last_buying_price',
        'supplier_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'deleted_at',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'select_all_location' => 'bool',
            'sell_to_customer' => 'bool',
            'service' => 'bool',
            'modifier' => 'bool',
            'has_variance' => 'bool',
            'allow_custom_price' => 'bool',
            'location_ids' => 'array',
            'exclude_location_ids' => 'array',
            'last_buying_price' => 'integer',
            'tax_setting' => TaxSettingEnum::class,
        ];
    }

    public function productLocations(): HasMany {
        return $this->hasMany(ProductLocation::class);
    }

    public function productSellPrices(): HasMany {
        return $this->hasMany(ProductSellPrice::class);
    }

    public function productSellPrice(): HasOne {
        return $this->hasOne(ProductSellPrice::class);
    }

    public function productLocationStocks(): HasMany {
        return $this->hasMany(ProductLocationStock::class);
    }

    public function productLocationStock(): HasOne {
        return $this->hasOne(ProductLocationStock::class);
    }

    public function productUnit(): BelongsTo {
        return $this->belongsTo(ProductUnit::class);
    }

    public function productSellUnit(): BelongsTo {
        return $this->belongsTo(ProductUnit::class, 'product_sell_unit_id');
    }

    public function productCategory(): BelongsTo {
        return $this->belongsTo(ProductCategory::class);
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
    }
    public function supplier(): BelongsTo {
        return $this->belongsTo(Supplier::class);
    }
    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }
}
