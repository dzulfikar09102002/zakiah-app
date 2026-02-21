<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Foundation\Http\FormRequest;

class SearchKasirCatalogueRequest extends FormRequest
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
            //
            'location' => 'required',
            'keyword' => 'nullable',
            'product_category' => 'nullable',
            'order_type' => 'nullable',
            'filter_stock' => 'nullable|in:true,false',
            'page' => 'nullable|numeric|min:1',
            'limit' => 'required|integer|min:12',
        ];
    }
}
