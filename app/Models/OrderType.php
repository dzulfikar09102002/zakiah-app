<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderType extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'entity_id',
        'payment_method_id',
        'name',
        'search_name',
        'fixed_fee',
        'variable_fee',
        'require_customer_data',
        'status',
        'created_by',
        'updated_by',
    ];
    
}
