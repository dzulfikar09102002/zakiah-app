<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Aksi per sub menu (Lihat, Tambah, ...). Route-nya ada di menu_action_routes.
 */
class MenuAction extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    public function submenu(): BelongsTo
    {
        return $this->belongsTo(MenuSubmenu::class, 'menu_submenu_id');
    }

    public function routes(): HasMany
    {
        return $this->hasMany(MenuActionRoute::class);
    }
}
