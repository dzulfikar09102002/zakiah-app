<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerPoint extends Model
{
    use HasFactory;

    public function customerPointMovements(): HasMany {
        return $this->hasMany(CustomerPointMovement::class);
    }
}
