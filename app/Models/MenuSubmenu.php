<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Sub menu = satu halaman aplikasi, beserta aksi-aksinya.
 */
class MenuSubmenu extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function actions(): HasMany
    {
        return $this->hasMany(MenuAction::class)->orderBy('seq');
    }

    public function details(): HasMany
    {
        return $this->hasMany(MenuHeaderDetail::class);
    }
}
