<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TakingPaymentDetail extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
        'taking_id',
    ];

    public function paymentMethod(): BelongsTo {
        return $this->belongsTo(PaymentMethod::class);
    }
}
