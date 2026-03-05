<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
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
        'first_name' => strtoupper(trim($this->first_name)),
        'last_name'  => strtoupper(trim($this->last_name)),
        'phone_number_country_code' => ltrim($this->phone_number_country_code, '+'),
    ]);
    }
    public function rules(): array
    {
       $customer = $this->route('customer');
        return [
            'first_name' => ['required'],
            'last_name' => [
                'required',
                Rule::unique('customers')
                    ->where(fn ($q) =>
                        $q->where('first_name', $this->first_name)
                        ->where('entity_id', $this->user()->entity->id)
                    )
                    ->ignore($customer),
            ],
            'phone_number'=> 'required',
            'phone_number_country_code' => 'required',
            'location_id' => 'required',
            'customer_category_id' => 'required',
        ];
    }
}
