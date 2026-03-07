<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, Employee::class, 'entity_id', 'id', 'id', 'user_id');
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function customerCategories()
    {
        return $this->hasMany(CustomerCategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function taxes()
    {
        return $this->hasMany(Tax::class);
    }

    public function orderTypes()
    {
        return $this->hasMany(OrderType::class);
    }

    public function productCategories()
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function paymentMethods()
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
