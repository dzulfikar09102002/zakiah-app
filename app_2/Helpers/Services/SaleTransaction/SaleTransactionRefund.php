<?php

namespace App\Helpers\Services\SaleTransaction;

use App\Helpers\Data\SaleTransaction\SaleTransactionDetailData;
use App\Helpers\Services\Taxes\TaxCalculator;
use App\Helpers\UniqueCodeGenerator;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Location;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductStockMovement;
use App\Models\ProductUnit;
use App\Models\SaleRefund;
use App\Models\SaleRefundDetail;
use App\Models\SaleRefundPayment;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionDetail;
use DateTime;
use DateTimeZone;

class SaleTransactionRefund
{
    private SaleTransaction $saleTransaction;
    private Employee $employee;
    private Device $device;
    private array $params;

    /**
     * Create a new class instance.
     */
    public function __construct(SaleTransaction $saleTransaction, Device $device, Employee $employee, array $params)
    {
        //
        $this->saleTransaction = $saleTransaction;
        $this->employee = $employee;
        $this->device = $device;
        $this->params = $params;
    }

    public function refund(): SaleRefund
    {
        $saleDetailTransactionData = $this->buildDetails();

        $saleRefund = new SaleRefund();

        $saleRefund->sale_transaction_id = $this->saleTransaction->id;
        $saleRefund->device_id = $this->device->id;

        $saleRefund->code = UniqueCodeGenerator::generateCode();

        $saleRefund->employee_id = $this->employee->id;
        $saleRefund->employee_first_name = $this->employee->first_name ?? '';
        $saleRefund->employee_last_name = $this->employee->last_name ?? '';

        $saleRefund->location_id = $this->saleTransaction->location_id;
        $saleRefund->location_name = $this->saleTransaction->location_name;
        $saleRefund->location_timezone = $this->saleTransaction->location_timezone;

        $saleRefund->order_type_id = $this->saleTransaction->order_type_id;
        $saleRefund->order_type_name = $this->saleTransaction->order_type_name;

        $saleRefund->reason = $this->params['reason'];
        $saleRefund->notes = $this->params['notes'];
        $saleRefund->sales_reference_id = $this->params['saleReferenceId'];

        $saleRefund->sales_at = $this->saleTransaction->sales_at;
        $saleRefund->local_sales_at = $this->saleTransaction->local_sales_at;

        $timeNow = new DateTime();
        $timezone = new DateTimeZone($this->saleTransaction->location_timezone);

        $saleRefund->refund_at = $timeNow;
        $saleRefund->local_refund_at = $timeNow->setTimezone($timezone);

        $saleRefund->gross_sales = $saleDetailTransactionData->getGrossSales();
        $saleRefund->discount_amount_before_tax = $saleDetailTransactionData->getDiscountAmountBeforeTax();
        $saleRefund->surcharge_amount_before_tax = $saleDetailTransactionData->getSurchargeAmountBeforeTax();
        $saleRefund->promo_amount_before_tax = $saleDetailTransactionData->getPromoAmountBeforeTax();
        $saleRefund->free_of_charge_amount_before_tax = $saleDetailTransactionData->getFreeOfChargeAmountBeforeTax();
        $saleRefund->net_sales = $saleDetailTransactionData->getNetSales();
        $saleRefund->service_charge_before_tax = $saleDetailTransactionData->getServiceChargeBeforeTax();
        $saleRefund->tax_amount = $saleDetailTransactionData->getTaxAmount();
        $saleRefund->rounding_amount = $saleDetailTransactionData->getRoundingAmount();
        $saleRefund->rounding_tax_amount = $saleDetailTransactionData->getRoundingTaxAmount();
        $saleRefund->rounding_service_charge_amount = $saleDetailTransactionData->getRoundingServiceChargeAmount();
        $saleRefund->net_sales_after_tax = $saleDetailTransactionData->getNetSalesAfterTax();
        $saleRefund->discount_amount = $saleDetailTransactionData->getDiscountAmount();
        $saleRefund->surcharge_amount = $saleDetailTransactionData->getSurchargeAmount();
        $saleRefund->promo_amount = $saleDetailTransactionData->getPromoAmount();
        $saleRefund->subtotal = $saleDetailTransactionData->getSubtotal();
        $saleRefund->platform_fee = 0;
        $saleRefund->total_processing_fee = 0;
        $saleRefund->total_subsidize = 0;

        $saleRefund->product_ids = $saleDetailTransactionData->getProductIds();
        $saleRefund->product_category_ids = $saleDetailTransactionData->getProductCategoryIds();
        $saleRefund->modifier_ids = array();
        $saleRefund->modifier_option_ids = array();

        $saleRefund->save();

        $this->createRefundDetails($this->saleTransaction, $saleRefund, $saleDetailTransactionData->getSaleDetailTransactions());
        $this->createPaymentDetails($saleRefund, $this->params['payments']);

        $this->saleTransaction->refunded_amount = $this->saleTransaction->refunded_amount + $saleRefund->net_sales_after_tax;
        $this->saleTransaction->save();

        return $saleRefund;
    }

    private function buildDetails(): SaleTransactionDetailData
    {
        $saleDetailTransactionData = new SaleTransactionDetailData();

        foreach ($this->params['sale_transaction_details'] as $param)
        {
            $saleTransactionDetail = $this->saleTransaction->saleTransactionDetails()->where('id', $param['id'])->first();
            $this->validateTransactionDetail($saleTransactionDetail);

            $saleTransactionDetail->cancelled_quantity = $param['quantity'];
            $saleTransactionDetail->save();

            $saleDetailTransactionData
                ->addSaleDetailTransactions($saleTransactionDetail)
                ->addProductIds($saleTransactionDetail->product_id)
                ->addProductCategoryIds($saleTransactionDetail->product_category_id)
                ->addGrossSales($saleTransactionDetail, $param['quantity'])
                ->addSubtotal($saleTransactionDetail, $param['quantity'])
                ->addDiscountAmountBeforeTax($saleTransactionDetail, $param['quantity'])
                ->addDiscountAmount($saleTransactionDetail)
                ->addSurchargeAmountBeforeTax($saleTransactionDetail, $param['quantity'])
                ->addSurchargeAmount($saleTransactionDetail)
                ->addPromoAmountBeforeTax($saleTransactionDetail, $param['quantity'])
                ->addPromoAmount($saleTransactionDetail)
                ->addFreeOfChargeAmountBeforeTax($saleTransactionDetail, $param['quantity'])
                ->addServiceChargeBeforeTax($saleTransactionDetail, $param['quantity'])
                ->addTaxAmount($saleTransactionDetail, $param['quantity']);
        }

        return $saleDetailTransactionData;
    }

    private function validateTransactionDetail(SaleTransactionDetail $saleTransactionDetail)
    {
        # if error throw error        
    }

    private function createRefundDetails(SaleTransaction $saleTransaction, SaleRefund $saleRefund, array $saleTransactionDetails)
    {
        foreach ($saleTransactionDetails as $saleTransactionDetail)
        {
            $saleRefundDetail = new SaleRefundDetail();

            $saleRefundDetail->sale_transaction_id = $saleTransaction->id;
            $saleRefundDetail->sale_transaction_detail_id = $saleTransactionDetail->id;
            $saleRefundDetail->sale_refund_id = $saleRefund->id;

            $saleRefundDetail->brand_id = $saleTransaction->brand_id;
            $saleRefundDetail->location_id = $saleTransaction->location_id;
            $saleRefundDetail->employee_id = $this->employee->id;
            $saleRefundDetail->order_type_id = $saleTransactionDetail->order_type_id;
            $saleRefundDetail->product_id = $saleTransactionDetail->product_id;
            $saleRefundDetail->product_category_id = $saleTransactionDetail->product_category_id;
            $saleRefundDetail->product_unit_id = $saleTransactionDetail->product_unit_id;
            $saleRefundDetail->tax_id = $saleTransactionDetail->tax_id;

            $saleRefundDetail->brand_name = $saleTransactionDetail->brand_name;
            $saleRefundDetail->location_name = $saleTransactionDetail->location_name;
            $saleRefundDetail->employee_first_name = $this->employee->first_name ?? '';
            $saleRefundDetail->employee_last_name = $this->employee->last_name ?? '';
            $saleRefundDetail->order_type_name = $saleTransactionDetail->order_type_name;

            $saleRefundDetail->product_name = $saleTransactionDetail->product_name;
            $saleRefundDetail->product_sku = $saleTransactionDetail->product_sku;
            $saleRefundDetail->product_code = $saleTransactionDetail->product_code;
            $saleRefundDetail->product_description = $saleTransactionDetail->product_description;

            $saleRefundDetail->product_category_name = $saleTransactionDetail->product_category_name;

            $saleRefundDetail->product_unit_name = $saleTransactionDetail->product_unit_name;

            $saleRefundDetail->tax_name = $saleTransactionDetail->tax_name;
            $saleRefundDetail->tax_rate = $saleTransactionDetail->tax_rate;
            $saleRefundDetail->tax_setting = $saleTransactionDetail->tax_setting;

            $saleRefundDetail->notes = $saleTransactionDetail->notes;
            $saleRefundDetail->quantity = $saleTransactionDetail->cancelled_quantity;

            $this->setFieldTaxAmount($saleRefundDetail, $saleTransactionDetail, 'sell_price');
            $this->setFieldTaxAmount($saleRefundDetail, $saleTransactionDetail, 'promo_amount');
            $this->setFieldTaxAmount($saleRefundDetail, $saleTransactionDetail, 'discount_amount');
            $this->setFieldTaxAmount($saleRefundDetail, $saleTransactionDetail, 'surcharge_amount');
            $this->setFieldTaxAmount($saleRefundDetail, $saleTransactionDetail, 'free_of_charge_amount');
            $this->setCalculatedTaxAmount($saleRefundDetail, $saleTransactionDetail, 'total_line_amount', $this->calculateTotalLineAmount($saleTransactionDetail));

            $this->setFieldTaxAmount($saleRefundDetail, $saleTransactionDetail, 'service_charge');

            $saleRefundDetail->service_charge_rate = $saleTransactionDetail->service_charge_rate;
            $saleRefundDetail->service_charge_include_tax = $saleTransactionDetail->service_charge_include_tax;

            $this->setFieldTaxAmount($saleRefundDetail, $saleTransactionDetail, 'prorate_promo_amount');
            $this->setFieldTaxAmount($saleRefundDetail, $saleTransactionDetail, 'prorate_discount_amount');
            $this->setFieldTaxAmount($saleRefundDetail, $saleTransactionDetail, 'prorate_surcharge_amount');
            $this->setFieldTaxAmount($saleRefundDetail, $saleTransactionDetail, 'prorate_free_of_charge_amount');

            $saleRefundDetail->modifier_subtotal = 0;
            $saleRefundDetail->modifier_subtotal_tax_amount = 0;
            $saleRefundDetail->modifier_service_charge = 0;
            $saleRefundDetail->modifier_service_charge_tax_amount = 0;
            $saleRefundDetail->modifier_prorate_promo_amount = 0;
            $saleRefundDetail->modifier_prorate_promo_amount_tax_amount = 0;
            $saleRefundDetail->modifier_prorate_discount_amount = 0;
            $saleRefundDetail->modifier_prorate_discount_amount_tax_amount = 0;
            $saleRefundDetail->modifier_prorate_surcharge_amount = 0;
            $saleRefundDetail->modifier_prorate_surcharge_amount_tax_amount = 0;
            $saleRefundDetail->modifier_prorate_promo_amount_tax_amount = 0;
            $saleRefundDetail->modifier_prorate_free_of_charge_amount = 0;
            $saleRefundDetail->modifier_prorate_free_of_charge_amount_tax_amount = 0;
            $saleRefundDetail->modifier_total_amount = 0;
            $saleRefundDetail->modifier_total_amount_tax_amount = 0;

            $this->setCalculatedTaxAmount($saleRefundDetail, $saleTransactionDetail, 'total_amount', $this->calculateTotalAmount($saleTransactionDetail));

            $saleRefundDetail->save();

            $this->importStockMovement(
                $saleRefundDetail,
                $saleTransactionDetail->location()->first(),
                $saleTransactionDetail->product()->first(),
                $saleTransactionDetail->productUnit()->first(),
                $saleRefundDetail->quantity,
            );
        }
    }

    private function createPaymentDetails(SaleRefund $saleRefund, array $paymentParams)
    {
        foreach($paymentParams as $paymentParam)
        {
            if ($paymentParam['payment_method_id'] == 0) {
                continue;
            }

            $payment = new SaleRefundPayment();
            $payment->sale_refund_id = $saleRefund->id;

            $paymentMethod = PaymentMethod::where('entity_id', $this->saleTransaction->entity_id)->where('id', $paymentParam['payment_method_id'])->first();

            $payment->payment_method_id = $paymentMethod->id;
            $payment->payment_method_name = $paymentMethod->name;

            $payment->amount_receive = $paymentParam['amount_receive'];
            $payment->change = $paymentParam['change'];
            $payment->subsidize = 0;
            $payment->platform_fee = 0;

            $payment->save();
        }
    }

    private function calculateTotalLineAmount(SaleTransactionDetail $saleTransactionDetail): int
    {
        return $saleTransactionDetail->sell_price - $saleTransactionDetail->promo_amount -
            $saleTransactionDetail->discount_amount +
            $saleTransactionDetail->surcharge_amount -
            $saleTransactionDetail->free_of_charge_amount -
            $saleTransactionDetail->prorate_promo_amount - 
            $saleTransactionDetail->prorate_discount_amount +
            $saleTransactionDetail->prorate_surcharge_amount -
            $saleTransactionDetail->prorate_free_of_charge_amount;
    }

    private function calculateTotalAmount(SaleTransactionDetail $saleTransactionDetail): int
    {
        return $this->calculateTotalLineAmount($saleTransactionDetail); # add modifier
    }

    private function setCalculatedTaxAmount(SaleRefundDetail $saleRefundDetail, SaleTransactionDetail $saleTransactionDetail, string $key, ?int $amount = null)
    {
        $amount = $amount ?? $saleTransactionDetail[$key];
        $taxCalculated = TaxCalculator::calculate($amount, $saleTransactionDetail->tax_rate, $saleTransactionDetail->tax_setting);
        
        $saleRefundDetail[$key] = $taxCalculated->getPrice();
        $saleRefundDetail[$key . '_tax_amount'] = $taxCalculated->getTaxAmount();
    }

    private function setFieldTaxAmount(SaleRefundDetail $saleRefundDetail, SaleTransactionDetail $saleTransactionDetail, string $key)
    {
        $saleRefundDetail[$key] = $saleTransactionDetail[$key];
        $saleRefundDetail[$key . '_tax_amount'] = $saleTransactionDetail[$key . '_tax_amount'];
    }

    private function importStockMovement(SaleRefundDetail $saleTransactionDetail, Location $location, Product $product, ProductUnit $productUnit, int $stock)
    {
        $data = new ProductStockMovement();

        $data->product_id = $product->id;
        $data->location_id = $location->id;
        $data->product_unit_id = $productUnit->id;

        $data->original_product_unit_id = $productUnit->id;

        $data->resource_id = $saleTransactionDetail->id;
        $data->resource_type = $saleTransactionDetail::class;

        $data->original_stock_out = 0;
        $data->original_stock_in = $stock ?? 0;
        $data->original_buying_price = 0;
        $data->conversion_stock = 1; # should find conversion, not for now

        $data->stock_in = $data->original_stock_in * $data->conversion_stock;
        $data->stock_out = $data->original_stock_out * $data->conversion_stock;
        $data->buying_price = $data->original_buying_price * $data->conversion_stock;

        $data->save();
    }
}
