<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
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
}
