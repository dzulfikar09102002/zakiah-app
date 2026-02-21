<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoRewardProduct extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
        'promo_id',
        'promo_reward_id',
    ];
}
