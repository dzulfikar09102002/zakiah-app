<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class StoreBrandRequest extends BaseRequest
{
    protected $page = PageNameConstants::BrandMenu;
    protected $action = ActionConstants::StoreAction;

    public function rules(): array
    {
        return [
            "name" => 'required',
            "locations" => 'nullable',
            "locations.*.location_id" => 'required|exists:locations,id',
            "image_url" => 'nullable|image',
            "icon_image_url" => 'nullable|image',
        ];
    }
}
