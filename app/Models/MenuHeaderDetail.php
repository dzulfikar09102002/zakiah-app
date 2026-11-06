<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penempatan sub menu di header, opsional di dalam group.
 */
class MenuHeaderDetail extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'seq' => 'integer',
        ];
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(MenuHeader::class, 'menu_header_id');
    }

    public function submenu(): BelongsTo
    {
        return $this->belongsTo(MenuSubmenu::class, 'menu_submenu_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(MenuGroup::class, 'menu_group_id');
    }
}
