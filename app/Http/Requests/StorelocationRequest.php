<?php

namespace App\Http\Requests;

use App\Enums\LocationKindEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorelocationRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name" => 'required',
            "backoffice_email" => 'nullable|email',
            "backoffice_phone_number" => 'nullable|numeric|min_digits:5|max_digits:15',
            "backoffice_phone_number_country_code" => 'nullable|numeric|min_digits:1|max_digits:3',
            "contact_email" => 'nullable|email',
            "contact_phone_number" => 'nullable|numeric|min_digits:5|max_digits:15',
            "contact_phone_number_country_code" => 'nullable|numeric|min_digits:1|max_digits:3',
            "image_url" => 'nullable|image',
            "icon_image_url" => 'nullable|image',
            "kind" => ['required', Rule::enum(LocationKindEnum::class)],
            "full_address" => 'nullable|max:255',
            "postal_code" => 'nullable|min:5|max:8',
            "district" => 'nullable|max:255',
            "city" => 'nullable|max:255',
            "province" => 'nullable|max:255',
            "country" => 'nullable|max:255',
            "timezone" => 'nullable|timezone',
            "location_hours" => 'nullable|array',
        ];
    }
}
