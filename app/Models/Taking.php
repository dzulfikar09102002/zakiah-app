<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Taking extends Model
{
    use HasFactory;
    
    protected function casts(): array
    {
        return [
            'sale_transaction_ids' => 'array',
            'sale_refund_ids' => 'array',
            'is_shift' => 'bool',
        ];
    }

    public function takingPaymentDetails(): HasMany {
        return $this->hasMany(TakingPaymentDetail::class);
    }

    public function takingTaxDetails(): HasMany {
        return $this->hasMany(TakingTaxDetail::class);
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
    }
}
