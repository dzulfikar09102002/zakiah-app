<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Foundation\Http\FormRequest;

class StoreKasirTakingRequest extends FormRequest
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
            "locationId" => "required|exists:locations,id",
            "isShift" => "required|boolean",
            "saleReferenceId" => "nullable|integer",
            "saleTransactionIds" => "nullable|array",
            "saleRefundIds" => "nullable|array",
            "moneyMovementIds" => "nullable|array",
            "customerDepositIds" => "nullable|array",
            "paymentSummaries" => "required|array",
            "paymentSummaries.*.payment_method_id" => "required|integer",
            "paymentSummaries.*.recorded_amount" => "required|integer",
            "paymentSummaries.*.counted_amount" => "required|integer",
            "paymentSummaries.*.difference_amount" => "required|integer",
            "paymentSummaries.*.sales_amount" => "required|integer",
            "paymentSummaries.*.sales_count" => "required|integer",
            "paymentSummaries.*.refund_amount" => "required|integer",
            "paymentSummaries.*.refund_count" => "required|integer",
            "paymentSummaries.*.money_movement_in_amount" => "required|integer",
            "paymentSummaries.*.money_movement_in_count" => "required|integer",
            "paymentSummaries.*.money_movement_out_amount" => "required|integer",
            "paymentSummaries.*.money_movement_out_count" => "required|integer",
            "paymentSummaries.*.customer_deposit_amount" => "required|integer",
            "paymentSummaries.*.customer_deposit_count" => "required|integer",
            "paymentSummaries.*.product_sold_count" => "required|integer",
            "paymentSummaries.*.product_category_sold_count" => "required|integer",
            "paymentSummaries.*.product_return_count" => "required|integer",
            "paymentSummaries.*.product_category_return_count" => "required|integer",
        ];
    }
}
