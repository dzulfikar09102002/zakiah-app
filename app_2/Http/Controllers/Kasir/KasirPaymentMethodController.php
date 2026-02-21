<?php

namespace App\Http\Controllers\Kasir;

use App\Enums\PaymentMethodKindEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\IndexKasirPaymentMethodRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\PaymentMethod;

class KasirPaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexKasirPaymentMethodRequest $request)
    {
        //
        $paymentMethods = PaymentMethod::where('entity_id', $request->entity->id)
            ->select(['id', 'name', 'icon_image_url', 'kind'])
            ->get();

        $paymentMethodCashes = array();
        $paymentMethodDebits = [];
        $paymentMethodCreditCards = [];
        $paymentMethodQris = [];
        $paymentMethodOnlinePayments = [];
        $paymentMethodVas = [];
        foreach ($paymentMethods as $paymentMethod)
        {
            $this->appendResult(PaymentMethodKindEnum::Cash, $paymentMethodCashes, $paymentMethod);
            $this->appendResult(PaymentMethodKindEnum::Debit, $paymentMethodDebits, $paymentMethod);
            $this->appendResult(PaymentMethodKindEnum::CreditCard, $paymentMethodCreditCards, $paymentMethod);
            $this->appendResult(PaymentMethodKindEnum::Qris, $paymentMethodQris, $paymentMethod);
            $this->appendResult(PaymentMethodKindEnum::OnlinePayment, $paymentMethodOnlinePayments, $paymentMethod);
            $this->appendResult(PaymentMethodKindEnum::VA, $paymentMethodVas, $paymentMethod);
        }

        $result = [];
        $this->setResult(__('payment_method.cash'), $paymentMethodCashes, $result);
        $this->setResult(__('payment_method.debit'), $paymentMethodDebits, $result);
        $this->setResult(__('payment_method.credit_card'), $paymentMethodCreditCards, $result);
        $this->setResult(__('payment_method.qris'), $paymentMethodQris, $result);
        $this->setResult(__('payment_method.online_payment'), $paymentMethodOnlinePayments, $result);
        $this->setResult(__('payment_method.va'), $paymentMethodVas, $result);
        
        return (new BaseJsonResponse($result))->response();
    }

    private function setResult(string $label, array $data, array &$result)
    {
        if (count($data) == 0) {
            return;
        }

        array_push($result, [
            "label" => $label,
            "paymentMethods" => $data,
        ]);
    }

    private function appendResult(PaymentMethodKindEnum $kind, array &$data, PaymentMethod $paymentMethod)
    {
        if ($paymentMethod->kind->value != $kind->value) {
            return;
        }

        array_push($data, $paymentMethod);
    }
}
