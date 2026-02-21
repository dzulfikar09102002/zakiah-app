<?php

namespace App\Models;

use App\Observers\ProductStockMovementObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

# TODO Fill
#[ObservedBy([ProductStockMovementObserver::class])]
class ProductStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'location_id',
        'product_unit_id',
    ];

    /**
     * Get the parent imageable model (user or post).
     */
    public function resource(): MorphTo
    {
        return $this->morphTo();
    }

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
