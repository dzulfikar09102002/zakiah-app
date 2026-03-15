<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class Helper
{
    public static function getPaginatedData(EloquentBuilder|QueryBuilder $builder)
    {
        return $builder->paginate(request('per_page', 10))->withQueryString();
    }
}
