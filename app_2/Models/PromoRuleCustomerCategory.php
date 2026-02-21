<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoRuleCustomerCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'promo_id',
        'promo_rule_id',
        'deleted_at',
        'created_at',
        'updated_at',
        'updated_by',
        'created_by',
    ];

    protected $guarded = [
        'id',
        'promo_id',
        'promo_rule_id',
    ];
}
