<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Brand extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [
        'id',
        'entity_id',
        'code',
        'initial',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
        'updated_by',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusEnum::class,
        ];
    }

    public function brandLocations(): HasMany {
        return $this->hasMany(BrandLocation::class);
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'brand_locations');
    }
}
