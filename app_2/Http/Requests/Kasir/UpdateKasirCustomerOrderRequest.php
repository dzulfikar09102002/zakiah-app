<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKasirCustomerOrderRequest extends FormRequest
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
            "location_id" => 'nullable',
            "order_type_id" => 'nullable',
            "customer" => 'nullable',
            "customer.email" => 'nullable|email',
            "customer.first_name" => 'nullable',
            "customer.last_name" => 'nullable',
            "customer.phone_number" => 'nullable|integer',
            "customer.phone_number_country_code" => 'nullable|integer',
            "adjustment" => 'nullable',
            "adjustment.quantity" => 'nullable|integer',
            "adjustment.amount" => 'nullable|integer',
            "adjustment.total_amount" => 'nullable|integer',
            "adjustment.is_percentage" => 'boolean',
            "adjustment.free_of_charge" => 'boolean',
            "products" => 'nullable|array',
            "products.*.id" => 'nullable|integer',
            "products.*.customer_order_detail_id" => 'nullable|integer',
            "products.*.product_id" => 'required|integer',
            "products.*.brand_id" => 'nullable|integer',
            "products.*.order_type_id" => 'required|integer',
            "products.*.product_unit_id" => 'required|integer',
            "products.*.product_category_id" => 'nullable|integer',
            "products.*.catalogue_detail_id" => 'nullable|integer',
            "products.*.quantity" => 'required|integer|min:-1',
            "products.*.sell_price" => 'required|integer|min:0',
            "products.*.custom_price" => 'boolean',
        ];
    }
}
