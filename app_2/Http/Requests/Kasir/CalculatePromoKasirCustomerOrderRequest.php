<?php

namespace App\Http\Requests\Kasir;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class CalculatePromoKasirCustomerOrderRequest extends BaseRequest
{
    protected $page = PageNameConstants::CustomerOrderMenu;
    protected $action = ActionConstants::ApplyPromoAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            "location_id" => 'required',
            "order_type_id" => 'required',
            "customer_order_id" => 'nullable',
            "customer" => 'nullable',
            "customer.email" => 'nullable|email',
            "customer.first_name" => 'nullable',
            "customer.last_name" => 'nullable',
            "customer.phone_number" => 'nullable|integer',
            "customer.phone_number_country_code" => 'nullable|integer',
            "promo_ids" => 'nullable|array|exists:promos,id',
            "adjustment" => 'nullable',
            "adjustment.quantity" => 'nullable|integer',
            "adjustment.amount" => 'nullable|integer',
            "adjustment.total_amount" => 'nullable|integer',
            "adjustment.is_percentage" => 'boolean',
            "adjustment.free_of_charge" => 'boolean',
            "products" => 'required|array',
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
            "products.*.adjustment" => 'nullable|array',
            "products.*.adjustment.quantity" => 'nullable|integer',
            "products.*.adjustment.amount" => 'nullable|integer',
            "products.*.adjustment.total_amount" => 'nullable|integer',
            "products.*.adjustment.is_percentage" => 'boolean',
            "products.*.adjustment.free_of_charge" => 'boolean',
            "products.*.loyalty_id" => 'nullable|exists:loyalties,id',
            "products.*.loyalty_reward_product_id" => 'nullable|exists:loyalty_reward_products,id',
            "products.*.loyalty_point" => 'nullable|integer|min:1',
            "payments" => 'nullable|array',
            "payments.*.payment_method_id" => 'required|integer',
        ];
    }
}
