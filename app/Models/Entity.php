<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entity extends Model
{
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, Employee::class, 'entity_id', 'id', 'id', 'user_id');
    }
}
