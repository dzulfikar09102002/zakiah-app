<?php

namespace App\Models;

use App\Observers\ProductLocationStockObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ProductLocationStockObserver::class])]
class ProductLocationStock extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'location_id',
        'product_unit_id',
        'stock',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'checksum',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'stock' => 'integer',
        'last_in_stock' => 'integer',
        'last_out_stock' => 'integer',
        'last_buy_price' => 'integer',
        'buying_price' => 'integer',
    ];

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo {
        return $this->belongsTo(ProductUnit::class);
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
    }
}
