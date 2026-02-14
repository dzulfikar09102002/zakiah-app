<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductCategoryRequest extends FormRequest
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
        $item = $this->route('productCategory');
        // Jika $item adalah objek (Model Binding), ambil id. Jika string, gunakan langsung.
        $id = is_object($item) ? $item->id : $item;

        return [
            'name' => [
                'required',
                Rule::unique('product_categories')
                    ->where(fn ($query) => $query->where('entity_id', $this->entity_id))
                    ->ignore($id), // Abaikan data ini sendiri
            ],
            'entity_id' => ['required'],
        ];
    }
}
