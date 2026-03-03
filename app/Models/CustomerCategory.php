<?php

namespace App\Models;

use App\Enums\StatusEnum;
use App\Enums\CustomerCategoryResetEveryEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerCategory extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'status',
        'required',
        'reset_every',
        'created_by',
        'updated_by',
        'entity_id'

    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'required' => 'bool',
            'reset_every' => CustomerCategoryResetEveryEnum::class,
            'status' => StatusEnum::class,
        ];
    }

    public function scopeRequired(Builder $query): void
    {
        $query->where('required', true);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('status', 'active');
    }

    public function customerCategoryRule(): HasOne {
        return $this->HasOne(CustomerCategoryRule::class);
    }

    public function customers(): HasMany {
        return $this->hasMany(Customer::class);
    }
}
