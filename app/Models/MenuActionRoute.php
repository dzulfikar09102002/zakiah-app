<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuActionRoute extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];

    public function action(): BelongsTo
    {
        return $this->belongsTo(MenuAction::class, 'menu_action_id');
    }
}
