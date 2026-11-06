<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Group sub menu di dalam header (mis. Laporan > Penjualan).
 */
class MenuGroup extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function details(): HasMany
    {
        return $this->hasMany(MenuHeaderDetail::class);
    }
}
