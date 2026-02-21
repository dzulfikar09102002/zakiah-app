<?php

namespace App\Http\Requests;

use App\Enums\StatusEnum;
use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends BaseRequest
{
    protected $page = PageNameConstants::BrandMenu;
    protected $action = ActionConstants::UpdateAction;
    
    public function rules(): array
    {
        return [
            "name" => 'nullable',
            "locations" => 'nullable',
            "locations.*.id" => 'nullable|exists:brand_locations,id',
            "locations.*.location_id" => 'required|exists:locations,id',
            "locations.*.deleted" => 'nullable|boolean',
            "image_url" => 'nullable|image',
            "icon_image_url" => 'nullable|image',
            "status" => ['nullable', Rule::enum(StatusEnum::class)],
        ];
    }
}
