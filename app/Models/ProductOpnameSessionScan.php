<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

/**
 * Setiap scan satu baris (insert-only) dan selalu bernilai satu barang. Koreksi PJ dicatat
 * sebagai baris qty -1 yang menunjuk scan yang dibatalkan. Jumlah per produk dihitung
 * dengan SUM(qty).
 */
class ProductOpnameSessionScan extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'qty' => 'integer',
            'recorded_stock' => 'integer',
            'ins_date' => 'datetime',
        ];
    }

    /**
     * Kolom koreksi datang dari SQL terpisah yang dijalankan manual. Selama belum dijalankan,
     * scan dan review tetap harus jalan; hanya fitur koreksi PJ yang tidak tersedia.
     */
    public static function supportsCorrections(): bool
    {
        return once(fn () => Schema::hasColumn('product_opname_session_scans', 'cancelled_scan_id'));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
