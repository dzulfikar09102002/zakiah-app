<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class CustomerOrder extends Model
{
    use HasFactory;

    public function customerOrderDetails(): HasMany {
        return $this->hasMany(CustomerOrderDetail::class);
    }

    public function customerOrderPromos(): HasMany {
        return $this->hasMany(CustomerOrderPromo::class);
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
    }

    public function orderType(): BelongsTo {
        return $this->belongsTo(OrderType::class);
    }

    public function customer(): BelongsTo {
        return $this->belongsTo(Customer::class);
    }

    protected $fillable = [
        'code',
    ];

    public function scopeInprogress(Builder $query): void
    {
        $query->where('status', '!=', 'paid');
    }
    
    protected function casts(): array
    {
        return [
            'adjustment' => 'array',
            'product_ids' => 'array',
            'product_category_ids' => 'array',
            'modifier_ids' => 'array',
            'modifier_option_ids' => 'array',
        ];
    }
}
