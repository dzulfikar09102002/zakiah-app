<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImportServiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_import_service_id',
        'kode',
        'nama',
        'deskripsi',
        'satuan',
        'berat',
        'harga_pokok',
        'harga_jual_ecer',
        'harga_jual_grosir',
        'kategori',
        'stok_minimum',
        'barcode',
        'nama_lokasi',
        'stok',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'imported_line' => 'array',
        ];
    }

    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
    }

    public function productUnit(): BelongsTo {
        return $this->belongsTo(ProductUnit::class);
    }

    public function productCategory(): BelongsTo {
        return $this->belongsTo(ProductCategory::class);
    }

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }
}
