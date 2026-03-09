<?php

namespace App\Http\Requests;

use App\Enums\LocationKindEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatelocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'backoffice_phone_number_country_code' => $this->backoffice_phone_number_country_code
                ? ltrim($this->backoffice_phone_number_country_code, '+')
                : null,

            'contact_phone_number_country_code' => $this->contact_phone_number_country_code
                ? ltrim($this->contact_phone_number_country_code, '+')
                : null,
        ]);
    }
    public function rules(): array
    {
        return [
            'name' => [
            'required',
            'string',
            'max:100',
            Rule::unique('locations', 'name')
    ->where('entity_id', auth()->user()->entity_id)
    ->ignore($this->route('location'))
        ],
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
            "footer" => 'nullable|string|max:255',
        ];
    }
}
