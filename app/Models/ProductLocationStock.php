<?php

namespace App\Models;

use App\Observers\ProductLocationStockObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([ProductLocationStockObserver::class])]
class ProductLocationStock extends Model
{
    protected $fillable = [
        'product_id',
        'location_id',
        'product_unit_id',
        'stock',
        'checksum', // 1. Tambahkan checksum ke fillable
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'checksum',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'stock' => 'integer',
        'last_in_stock' => 'integer',
        'last_out_stock' => 'integer',
        'last_buy_price' => 'integer',
        'buying_price' => 'integer',
    ];

    /**
     * 2. Auto-generate Checksum jika belum terisi saat model dibuat
     */
    protected static function booted(): void
    {
        static::creating(function (ProductLocationStock $model) {
            if (empty($model->checksum)) {
                // Generate hash unik berdasarkan atribut stok agar MySQL tidak error 1364
                $model->checksum = hash('sha256', implode('-', [
                    $model->product_id,
                    $model->location_id,
                    $model->product_unit_id ?? 0,
                    $model->stock ?? 0,
                    microtime(true)
                ]));
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}