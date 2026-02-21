<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleRefundDetail extends Model
{
    use HasFactory;

    public function saleRefund(): BelongsTo {
        return $this->belongsTo(SaleRefund::class);
    }

    public function saleTransaction(): BelongsTo {
        return $this->belongsTo(SaleTransaction::class);
    }

    public function saleTransactionDetail(): BelongsTo {
        return $this->belongsTo(SaleTransactionDetail::class);
    }
}
