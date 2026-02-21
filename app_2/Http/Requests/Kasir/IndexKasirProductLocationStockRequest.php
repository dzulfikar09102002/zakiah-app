<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexKasirProductLocationStockRequest extends FormRequest
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
            'prod_id' => [
                'required',
                Rule::exists('products', 'id')->where(function (Builder $query) {
                    return $this->validate($query);
                }),
            ],
            'limit' => 'required|integer|min:1'
        ];
    }

    private function validate(Builder $query)
    {
        return $query->where('entity_id', $this->entity->id);
    }
}
