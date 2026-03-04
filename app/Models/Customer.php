<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'location_id',
        'first_name',
        'last_name',
        'phone_number',
        'phone_number_country_code',
        'customer_category_id',
        'email',
        'entity_id',
        'user_id',
        'created_by',
        'updated_by'
    ];

    public function customerPoint(): HasOne {
        return $this->HasOne(CustomerPoint::class);
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
    }

    public function customerCategory(): BelongsTo {
        return $this->belongsTo(CustomerCategory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
