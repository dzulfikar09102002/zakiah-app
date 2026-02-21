<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleTransactionPayment extends Model
{
    use HasFactory;

    public function saleTransaction(): BelongsTo {
        return $this->belongsTo(SaleTransaction::class);
    }

    public function paymentMethod(): BelongsTo {
        return $this->belongsTo(PaymentMethod::class);
    }
}
