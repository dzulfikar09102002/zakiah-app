<?php

namespace App\Http\Requests;

use App\Models\Entity;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEntityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => 'nullable|max:255',
            "email" => [
                'email',
                Rule::unique('entities', 'email')->ignore($this->entity->id)
            ],
            "website" => 'nullable|url',
            "phone_number" => 'nullable|numeric|min_digits:5|max_digits:15',
            "phone_number_country_code" => 'nullable|numeric|min_digits:1|max_digits:3',
            "image_url" => 'nullable|image',
            "icon_image_url" => 'nullable|image',
            "postal_code" => 'nullable|min:5|max:10',
            "timezone" => 'nullable|timezone',
        ];
    }
}
