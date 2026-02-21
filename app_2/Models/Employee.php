<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'select_all_location' => 'bool',
            'entity_permission' => 'array',
            'location_permission' => 'array',
        ];
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

    public function entity(): BelongsTo {
        return $this->belongsTo(Entity::class);
    }

    public function role(): BelongsTo {
        return $this->belongsTo(Role::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function employeeLocations(): HasMany {
        return $this->hasMany(EmployeeLocation::class);
    }

    public function devices(): HasMany {
        return $this->hasMany(Device::class);
    }
}
