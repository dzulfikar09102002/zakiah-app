<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'entity_id',
        'name',
        'search_name',
        'status',
        'created_by',
        'updated_by',
    ];
}
