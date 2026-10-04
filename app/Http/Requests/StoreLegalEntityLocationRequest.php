<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLegalEntityLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $entityId = $this->user()?->entity?->id;

        return [
            'location_id' => [
                'required',
                Rule::exists('locations', 'id')->where('entity_id', $entityId)->whereNull('deleted_at'),
            ],
            'legal_entity_ids' => 'required|array|min:1',
            'legal_entity_ids.*' => [
                'required',
                'distinct',
                Rule::exists('legal_entities', 'id')->where('entity_id', $entityId)->whereNull('deleted_at'),
                Rule::unique('legal_entity_locations', 'legal_entity_id')
                    ->where('location_id', $this->input('location_id'))
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'legal_entity_ids.required' => 'Pilih minimal satu CV.',
            'legal_entity_ids.min' => 'Pilih minimal satu CV.',
            'legal_entity_ids.*.unique' => 'Salah satu CV sudah terdaftar di lokasi tersebut.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'location_id' => 'lokasi',
            'legal_entity_ids' => 'CV',
            'legal_entity_ids.*' => 'CV',
        ];
    }
}
