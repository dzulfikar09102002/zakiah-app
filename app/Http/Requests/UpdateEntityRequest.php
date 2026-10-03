<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * Aturan mengikuti backend lama; image_url/icon_image_url tidak diterima karena
     * logo diambil dari public/assets/images (lihat EntityBrandingService).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('entities', 'email')->ignore($this->user()?->entity?->id),
            ],
            'website' => 'nullable|url',
            'phone_number' => 'nullable|numeric|min_digits:5|max_digits:15',
            'phone_number_country_code' => 'nullable|numeric|min_digits:1|max_digits:3',
            'full_address' => 'nullable|max:255',
            'city' => 'nullable|max:100',
            'province' => 'nullable|max:100',
            'postal_code' => 'nullable|min:5|max:10',
            'timezone' => 'nullable|timezone',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama entity',
            'phone_number' => 'nomor telepon',
            'phone_number_country_code' => 'kode negara',
            'full_address' => 'alamat',
            'city' => 'kota',
            'province' => 'provinsi',
            'postal_code' => 'kode pos',
            'timezone' => 'zona waktu',
        ];
    }
}
