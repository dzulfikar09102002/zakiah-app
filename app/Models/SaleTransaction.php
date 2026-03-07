<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleTransaction extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
        'entity_id',
        'code',
    ];

    protected $casts = [
        'net_sales_after_tax' => 'integer',
        'payment_platform_fee' => 'integer',
        'discount_amount' => 'integer',
        'promo_amount' => 'integer',
        'product_ids' => 'array',
        'product_category_ids' => 'array',
        'modifier_ids' => 'array',
        'modifier_option_ids' => 'array',
    ];
    public function saleTransactionDetails(): HasMany
    {
        return $this->hasMany(SaleTransactionDetail::class);
    }

    public function saleTransactionDetailLoyalties(): HasMany
    {
        return $this->saleTransactionDetails()->whereNotNull('loyalty_id');
    }

    public function saleTransactionPayments(): HasMany
    {
        return $this->hasMany(SaleTransactionPayment::class);
    }

    public function saleTransactionPromos(): HasMany
    {
        return $this->hasMany(SaleTransactionPromo::class);
    }

    public function saleRefunds(): HasMany
    {
        return $this->hasMany(SaleRefund::class);
    }

    public function orderType(): BelongsTo
    {
        return $this->belongsTo(OrderType::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function employeeSales(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_sales_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'cashier_id');
    }

    public function voidBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'void_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'paid_by');
    }


    public function getTotalItemAttribute(): int
    {
        return (int) $this->saleTransactionDetails()->sum('quantity');
    }

    public function allowRefund(): bool
    {
        return true;
    }
}