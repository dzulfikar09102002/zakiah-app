<?php

namespace App\Http\Requests;

use App\Enums\LegalEntityTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLegalEntityRequest extends FormRequest
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
            'phone_number_country_code' => $this->phone_number_country_code
                ? ltrim($this->phone_number_country_code, '+')
                : null,
            'initial' => $this->initial ? strtoupper($this->initial) : null,
        ]);
    }

    /**
     * Unique per entity juga mencakup data terhapus (soft delete),
     * karena index unique di database tidak mengabaikan deleted_at.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $entityId = $this->user()?->entity?->id;
        $ignoreId = $this->route('legal_entity')?->id;

        return [
            'name' => 'required|max:255',
            'initial' => [
                'nullable',
                'max:20',
                Rule::unique('legal_entities', 'initial')->where('entity_id', $entityId)->ignore($ignoreId),
            ],
            'legal_type' => ['required', Rule::enum(LegalEntityTypeEnum::class)],
            'npwp' => [
                'nullable',
                'max:30',
                Rule::unique('legal_entities', 'npwp')->where('entity_id', $entityId)->ignore($ignoreId),
            ],
            'is_pkp' => 'boolean',
            'full_address' => 'nullable|max:255',
            'postal_code' => 'nullable|min:5|max:10',
            'city' => 'nullable|max:255',
            'province' => 'nullable|max:255',
            'country' => 'nullable|max:255',
            'phone_number' => 'nullable|numeric|min_digits:5|max_digits:15',
            'phone_number_country_code' => 'nullable|numeric|min_digits:1|max_digits:3',
            'email' => 'nullable|email|max:255',
            'bank_name' => 'nullable|max:255',
            'bank_account_no' => 'nullable|max:50',
            'bank_account_name' => 'nullable|max:255',
            'invoice_prefix' => 'nullable|max:50',
            'status' => 'nullable|in:active,archived',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'initial' => 'inisial',
            'legal_type' => 'jenis badan usaha',
            'npwp' => 'NPWP',
            'full_address' => 'alamat',
            'postal_code' => 'kode pos',
            'city' => 'kota',
            'province' => 'provinsi',
            'country' => 'negara',
            'phone_number' => 'nomor telepon',
            'bank_name' => 'nama bank',
            'bank_account_no' => 'nomor rekening',
            'bank_account_name' => 'nama pemilik rekening',
            'invoice_prefix' => 'prefix invoice',
        ];
    }
}
