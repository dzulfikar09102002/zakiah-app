<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerCategoryRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'minimal_spend',
        'include_tax',
        'include_service_charge',
        'include_promo',
        'include_surcharge',
        'include_free_of_charge',
    ];
    
    protected function casts(): array
    {
        return [
            'include_tax' => 'bool',
            'include_service_charge' => 'bool',
            'include_promo' => 'bool',
            'include_surcharge' => 'bool',
            'include_free_of_charge' => 'bool',
        ];
    }

    public function validateRule(int $minimalSpend): bool
    {
        return $minimalSpend > $this->minimal_spend;
    }
}
