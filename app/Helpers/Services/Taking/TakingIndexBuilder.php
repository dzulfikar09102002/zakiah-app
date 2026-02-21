<?php

namespace App\Helpers\Services\Taking;

use App\Models\Device;
use App\Models\Employee;
use App\Models\Entity;
use App\Models\Location;
use App\Models\PaymentMethod;
use App\Models\SaleRefund;
use App\Models\SaleTransaction;

class TakingIndexBuilder
{
    private Device $device;
    private Location $location;
    private Entity $entity;
    private Employee $employee;

    private array $saleSummaries, $paymentSummaries, $saleTransactionIds;
    private array $saleRefundSummaries, $saleRefundIds;
    
    /**
     * Create a new class instance.
     */
    public function __construct(Device $device, Entity $entity, Location $location, Employee $employee)
    {
        //
        $this->device = $device;
        $this->entity = $entity;
        $this->location = $location;
        $this->employee = $employee;

        $this->saleTransactionIds = array();
        $this->saleRefundIds = array();

        $this->initSaleSummaries();
        $this->initPaymentSummaries();
        $this->initSaleRefundSummaries();
    }

    public function build()
    {
        # find sale_transaction where taking = null
        # sum all payment
        # find sale_refund where taking = null
        # find all payment method
        $deviceId = $this->device->id;
        $employeeId = $this->employee->id;

        $datas = SaleTransaction::with([
                'saleTransactionDetails',
                // 'saleTransactionDetails.product:id,name,sku',
                // 'saleTransactionDetails.productUnit:id,name',
                // 'saleTransactionDetails.tax:id,name',
                // 'saleTransactionDetails.productCategory:id,name',
                'saleTransactionPayments',
                // 'saleTransactionPayments.paymentMethod:id,name',
                'saleTransactionPromos',
                // 'saleTransactionPromos.promo:id,name',
                'saleRefunds',
            ])
            ->where('entity_id', $this->entity->id)
            ->where('location_id', $this->location->id)
            ->where('cashier_id', $employeeId)
            // ->where(function (Builder $builder) use($deviceId) {
            //     // $builder->where('device_id', $deviceId)->orWhere('checkpoint_device_id', $deviceId);
            // })
            ->where('void_at', null)
            ->where('taking_id', null)
            ->get();

        foreach($datas as $saleTransaction)
        {
            array_push($this->saleTransactionIds, $saleTransaction->id);
            $this->appendSalesSummaries($saleTransaction);

            foreach($saleTransaction['saleTransactionPayments'] as $saleTransactionPayment)
            {
                $paymentMethodId = $saleTransactionPayment['payment_method_id'];
                $amountReceive = $saleTransactionPayment['amount_receive'];
                $change = $saleTransactionPayment['change'];

                $this->appendPaymentSummaries($paymentMethodId, $amountReceive, $change);
                $this->appendPaymentSummariesSales($paymentMethodId, $amountReceive, $change);
            }

            foreach($saleTransaction['saleRefunds'] as $saleRefund)
            {
                array_push($this->saleRefundIds, $saleRefund->id);

                $this->appendSalesRefundSummaries($saleRefund);
            }
        }

        return $this;
    }

    public function response(): array
    {
        return [
            'saleTransactionIds' => $this->saleTransactionIds,
            'saleSummaries' => $this->saleSummaries,
            'paymentSummaries' => $this->mappedObject($this->paymentSummaries),
            'saleRefundIds' => $this->saleRefundIds,
            'saleRefundSummaries' => $this->saleRefundSummaries,
        ];
    }

    /**
     * Get the value of saleSummaries
     */ 
    public function getSaleSummaries()
    {
        return $this->saleSummaries;
    }

    private function initSaleSummaries()
    {
        $this->saleSummaries = [
            'grossSales' => 0,
            'discountBeforeTax' => 0,
            'promoBeforeTax' => 0,
            'surchargeBeforeTax' => 0,
            'netSales' => 0,
            'serviceCharge' => 0,
            'taxAmount' => 0,
            'paymentPlatformFee' => 0,
            'netSalesAfterTax' => 0,
        ];
    }

    private function appendSalesSummaries(SaleTransaction $saleTransaction)
    {
        $this->saleSummaries['grossSales'] += $saleTransaction->gross_sales;
        $this->saleSummaries['discountBeforeTax'] += $saleTransaction->discount_amount_before_tax;
        $this->saleSummaries['promoBeforeTax'] += $saleTransaction->promo_amount_before_tax;
        $this->saleSummaries['surchargeBeforeTax'] += $saleTransaction->surcharge_amount_before_tax;
        $this->saleSummaries['netSales'] += $saleTransaction->net_sales;
        $this->saleSummaries['serviceCharge'] += $saleTransaction->service_charge;
        $this->saleSummaries['taxAmount'] += $saleTransaction->tax_amount;
        $this->saleSummaries['paymentPlatformFee'] += $saleTransaction->payment_platform_fee;
        $this->saleSummaries['netSalesAfterTax'] += $saleTransaction->net_sales_after_tax;
    }

    private function initPaymentSummaries()
    {
        $this->paymentSummaries = array();
        $paymentMethods = PaymentMethod::where('entity_id', $this->entity->id)->get();

        foreach ($paymentMethods as $paymentMethod)
        {
            $this->paymentSummaries[$paymentMethod->id] = [
                'id' => $paymentMethod->id,
                'name' => $paymentMethod->name,
                'recorded_amount' => 0,
                'counted_amount' => 0,
                'difference_amount' => 0,
                'sales_amount' => 0,
                'sales_count' => 0,
                'refund_amount' => 0,
                'refund_count' => 0,
                'money_movement_in_amount' => 0,
                'money_movement_in_count' => 0,
                'money_movement_out_amount' => 0,
                'money_movement_out_count' => 0,
                'customer_deposit_amount' => 0,
                'customer_deposit_count' => 0,
                'product_sold_count' => 0,
                'product_category_sold_count' => 0,
                'product_return_count' => 0,
                'product_category_return_count' => 0,
            ];
        }
    }

    private function appendPaymentSummaries(int $paymentMethodId, int $amount, int $change)
    {
        if (!array_key_exists($paymentMethodId, $this->paymentSummaries)) {
            return;
        }

        $this->paymentSummaries[$paymentMethodId]['recorded_amount'] += $amount - $change;

        # TODO:
        # if pos_setting.auto_fill_clsoing = true
        $this->paymentSummaries[$paymentMethodId]['counted_amount'] += $amount - $change;
    }

    private function appendPaymentSummariesSales(int $paymentMethodId, int $amount, int $change)
    {
        if (!array_key_exists($paymentMethodId, $this->paymentSummaries)) {
            return;
        }

        $this->paymentSummaries[$paymentMethodId]['sales_amount'] += $amount - $change;
        $this->paymentSummaries[$paymentMethodId]['sales_count'] += 1;
    }

    private function initSaleRefundSummaries()
    {
        $this->saleRefundSummaries = [
            'grossSales' => 0,
            'discountBeforeTax' => 0,
            'promoBeforeTax' => 0,
            'surchargeBeforeTax' => 0,
            'netSales' => 0,
            'serviceCharge' => 0,
            'taxAmount' => 0,
            'netSalesAfterTax' => 0,
        ];
    }

    private function appendSalesRefundSummaries(SaleRefund $saleRefund)
    {
        $this->saleRefundSummaries['grossSales'] += $saleRefund->gross_sales;
        $this->saleRefundSummaries['discountBeforeTax'] += $saleRefund->discount_amount_before_tax;
        $this->saleRefundSummaries['promoBeforeTax'] += $saleRefund->promo_amount_before_tax;
        $this->saleRefundSummaries['surchargeBeforeTax'] += $saleRefund->surcharge_amount_before_tax;
        $this->saleRefundSummaries['netSales'] += $saleRefund->net_sales;
        $this->saleRefundSummaries['serviceCharge'] += $saleRefund->service_charge;
        $this->saleRefundSummaries['taxAmount'] += $saleRefund->tax_amount;
        $this->saleRefundSummaries['netSalesAfterTax'] += $saleRefund->net_sales_after_tax;
    }

    private function mappedObject(array $summaries)
    {
        $paymentMethods = array();

        foreach($summaries as $id => $paymentMethodSummary)
        {
            array_push($paymentMethods, $paymentMethodSummary);
        }

        return $paymentMethods;
    }
}
