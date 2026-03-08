<?php

namespace App\Models;

use App\Enums\LocationKindEnum;
use App\Enums\StatusEnum;
use App\Observers\LocationObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([LocationObserver::class])]
class Location extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'entity_id',
        'code',
        'initial',
        'name',
        'search_name',
        'image_url',
        'icon_image_url',
        'backoffice_phone_number',
        'backoffice_phone_number_country',
        'backoffice_email',
        'contact_phone_number',
        'contact_phone_number_country_code',
        'contact_email',
        'kind',
        'warehouse',
        'full_address',
        'postal_code',
        'district',
        'city',
        'province',
        'country',
        'timezone',
        'footer',
        'allow_transfer_stock',
        'allow_external_supplier',
        'franchise',
        'status',
        'created_by',
        'updated_by',
    ];
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'allow_transfer_stock' => 'boolean',
            'allow_external_supplier' => 'boolean',
            'franchise' => 'boolean',
            'kind' => LocationKindEnum::class,
            'status' => StatusEnum::class,
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
        'checksum',
        'search_name',
    ];
}
