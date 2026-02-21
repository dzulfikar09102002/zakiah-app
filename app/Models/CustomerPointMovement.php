<?php

namespace App\Models;

use App\Observers\CustomerPointMovementObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([CustomerPointMovementObserver::class])]
class CustomerPointMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_id',
        'sale_transaction_id',
        'sale_transaction_detail_id',
        'customer_id',
        'customer_order_id',
        'customer_order_detail_id',
        'loyalty_id',
        'loyalty_reward_product_id',
        'transaction_value',
        'miniminal_transaction_value',
        'code',
        'point',
        'type',
    ];

    public function customerPoint(): BelongsTo {
        return $this->belongsTo(CustomerPoint::class);
    }
}
