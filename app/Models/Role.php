<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'entity_permission',
        'tier',
        'level',
        'allow_pos',
        'allow_backoffice',
        'location_permission',
        'created_by',
        'updated_by'
    ];

    // cast JSON columns supaya array otomatis
    protected $casts = [
        'entity_permission' => 'array',
        'location_permission' => 'array',
        'allow_pos' => 'boolean',
        'allow_backoffice' => 'boolean',
        'tier' => 'integer',
        'level' => 'integer',
    ];
}