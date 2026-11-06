<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Header menu sidebar (Laporan, Stok, ...). Tabel dibuat lewat
 * database/sql/2026_11_06_create_menu_access_tables.sql.
 */
class MenuHeader extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'seq' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(MenuHeaderDetail::class)->orderBy('seq');
    }
}
