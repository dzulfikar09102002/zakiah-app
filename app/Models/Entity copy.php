<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EntityCopy extends Base
{
    use HasFactory;
    use SoftDeletes;

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function customerCategories(): HasMany
    {
        return $this->hasMany(CustomerCategory::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function taxes(): HasMany
    {
        return $this->hasMany(Tax::class);
    }

    public function orderTypes(): HasMany
    {
        return $this->hasMany(OrderType::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    protected $fillable = [
        'name',
        'email',
        'website',
        'image_url',
        'icon_image_url',
        'phone_number',
        'phone_number_country_code',
        'full_address',
        'postal_code',
        'city',
        'province',
        'country',
        'timezone',
    ];
}
