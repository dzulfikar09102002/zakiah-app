<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Hak Akses backoffice per entity. Terpisah dari roles (jabatan di toko untuk POS).
 */
class AccessRole extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
            'default_for_role_id' => 'integer',
        ];
    }

    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(MenuAction::class, 'access_role_menu_actions')
            ->withPivot('created_at', 'created_by');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
