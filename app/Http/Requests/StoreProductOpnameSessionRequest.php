<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductOpnameSessionRequest extends FormRequest
{
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
            'location_id' => ['required', 'integer', Rule::exists('locations', 'id')->where('entity_id', $entityId)],
            'opname_date' => ['required', 'date'],
            'pic_employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->where('entity_id', $entityId)],
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'distinct', Rule::exists('employees', 'id')->where('entity_id', $entityId)],
            'start_now' => ['boolean'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'location_id' => 'toko',
            'opname_date' => 'tanggal SO',
            'pic_employee_id' => 'PJ SO',
            'employee_ids' => 'karyawan',
            'note' => 'catatan',
        ];
    }
}
