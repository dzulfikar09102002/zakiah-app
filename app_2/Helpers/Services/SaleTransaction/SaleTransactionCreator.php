<?php

namespace App\Helpers\Services\SaleTransaction;

use App\Helpers\Data\SaleTransaction\SaleTransactionDetailData;
use App\Helpers\Services\CustomerPoint\CustomerPointEarnService;
use App\Helpers\Services\CustomerPoint\CustomerPointRedeemService;
use App\Helpers\Services\Taxes\TaxCalculator;
use App\Helpers\UniqueCodeGenerator;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderDetail;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Loyalty;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductStockMovement;
use App\Models\ProductUnit;
use App\Models\SalesInvoiceNumber;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionDetail;
use App\Models\SaleTransactionPayment;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\DB;

class SaleTransactionCreator
{
    private ?Employee $receivedBy;
    private Employee $cashierBy;
    private ?Customer $paidBy;
    private Device $device;
    private CustomerOrder $customerOrder;
    private array $params;
    private array $mappedBrand, $mappedLocation, $mappedOrderType, $mappedProduct, $mappedProductCategory, $mappedProductUnit;
    private array $mappedTax, $mappedPaymentMethod;

    /**
     * Create a new class instance.
     */
    public function __construct(CustomerOrder $customerOrder, Device $device, ?Employee $receivedBy, ?Customer $paidBy, array $params, Employee $cashierBy)
    {
        //
        $this->customerOrder = $customerOrder;
        $this->receivedBy = $receivedBy;
        $this->cashierBy = $cashierBy;
        $this->paidBy = $paidBy;
        $this->device = $device;
        $this->params = $params;

        $this->mappedBrand = array();
        $this->mappedLocation = array();
        $this->mappedOrderType = array();
        $this->mappedProduct = array();
        $this->mappedProductCategory = array();
        $this->mappedProductUnit = array();
        $this->mappedTax = array();
        $this->mappedPaymentMethod = array();
    }

    public function create() : SaleTransaction {
        $this->validate(); # throw error if any

        $saleTransaction = $this->createSaleTransacation();

        # update customer order
        $this->updateCustomerOrder();

        return $saleTransaction;
    }

    private function validate()
    {
    }

    private function updateCustomerOrder()
    {
        $this->customerOrder->status = 'paid';
        $this->customerOrder->paid_at = new DateTime();
        $this->customerOrder->save();
    }

    private function createSaleTransacation() : SaleTransaction 
    {
        $saleDetailTransactionsData = $this->buildDetails();

        $saleTransaction = new SaleTransaction();

        $saleTransaction->entity_id = $this->customerOrder->entity_id;
        $saleTransaction->customer_order_id = $this->customerOrder->id;
        
        $customer = $this->findCustomer($this->customerOrder->customer_id);
        $saleTransaction->customer_id = $this->customerOrder->customer_id;
        $saleTransaction->customer_first_name = $customer?->first_name;
        $saleTransaction->customer_last_name = $customer?->last_name;
        $saleTransaction->customer_phone_number = $customer?->phone_number;
        $saleTransaction->customer_phone_number_country_code = $customer?->phone_number_country_code;
        
        $saleTransaction->device_id = $this->device->id;

        $employeeSales = $this->findEmployee($this->params['employee_sales_id']);
        $saleTransaction->employee_sales_id = $this->params['employee_sales_id'];
        $saleTransaction->employee_sales_first_name = $employeeSales->first_name;
        $saleTransaction->employee_sales_last_name = $employeeSales->last_name;

        $saleTransaction->cashier_id = $this->cashierBy->id;
        $saleTransaction->cashier_first_name = $this->cashierBy->first_name;
        $saleTransaction->cashier_last_name = $this->cashierBy->last_name;

        HeaderFieldSetter::orderTypeHeader($this->mappedOrderType, $saleTransaction, $this->customerOrder->order_type_id);
        HeaderFieldSetter::locationHeader($this->mappedLocation, $saleTransaction, $this->customerOrder->location_id);

        $timezone = new DateTimeZone($saleTransaction->location_timezone);
        $timeNow = new DateTime();
        $localTimeNow = (new DateTime())->setTimezone($timezone);

        $saleTransaction->code = UniqueCodeGenerator::generateCode();
        $saleTransaction->sales_no = $this->generateSalesNo($localTimeNow, $saleTransaction->location_initial, $saleTransaction->location_id);
        $saleTransaction->receipt_no = $this->generateReceiptNo();

        $saleTransaction->receive_paid_by = $this->receivedBy?->id;
        $saleTransaction->receive_paid_by_first_name = $this->receivedBy?->first_name;
        $saleTransaction->receive_paid_by_last_name = $this->receivedBy?->last_name;

        $saleTransaction->paid_by = $this->paidBy?->id;
        $saleTransaction->paid_by_first_name = $this->paidBy?->first_name;
        $saleTransaction->paid_by_last_name = $this->paidBy?->last_name;

        $saleTransaction->paid_at = $timeNow;
        $saleTransaction->local_paid_at = $localTimeNow;

        $saleTransaction->sales_at = $timeNow;
        $saleTransaction->local_sales_at = $localTimeNow;

        $saleTransaction->gross_sales = $saleDetailTransactionsData->getGrossSales();
        $saleTransaction->gross_profit = $saleDetailTransactionsData->getGrossProfit();
        $saleTransaction->discount_amount_before_tax = $saleDetailTransactionsData->getDiscountAmountBeforeTax();
        $saleTransaction->surcharge_amount_before_tax = $saleDetailTransactionsData->getSurchargeAmountBeforeTax();
        $saleTransaction->promo_amount_before_tax = $saleDetailTransactionsData->getPromoAmountBeforeTax();
        $saleTransaction->free_of_charge_amount_before_tax = $saleDetailTransactionsData->getFreeOfChargeAmountBeforeTax();
        $saleTransaction->net_sales = $saleDetailTransactionsData->getNetSales();
        $saleTransaction->net_profit = $saleDetailTransactionsData->getNetProfit();
        $saleTransaction->service_charge_before_tax = $saleDetailTransactionsData->getServiceChargeBeforeTax();
        $saleTransaction->tax_amount = $saleDetailTransactionsData->getTaxAmount();
        $saleTransaction->rounding_amount = $saleDetailTransactionsData->getRoundingAmount();
        $saleTransaction->rounding_tax_amount = $saleDetailTransactionsData->getRoundingTaxAmount();
        $saleTransaction->rounding_service_charge_amount = $saleDetailTransactionsData->getRoundingServiceChargeAmount();
        $saleTransaction->net_sales_after_tax = $saleDetailTransactionsData->getNetSalesAfterTax();
        $saleTransaction->platform_fee = $this->customerOrder->platform_fee;
        $saleTransaction->payment_platform_fee = $this->customerOrder->payment_platform_fee;
        $saleTransaction->total_processing_fee = 0;
        $saleTransaction->total_subsidize = 0;

        $saleTransaction->product_ids = $saleDetailTransactionsData->getProductIds();
        $saleTransaction->product_category_ids = $saleDetailTransactionsData->getProductCategoryIds();
        $saleTransaction->modifier_ids = [];
        $saleTransaction->modifier_option_ids = [];

        $saleTransaction->discount_amount = $this->customerOrder->discount_amount;
        $saleTransaction->surcharge_amount = $this->customerOrder->surcharge_amount;
        $saleTransaction->promo_amount = $this->customerOrder->promo_amount;
        $saleTransaction->subtotal = $this->customerOrder->subtotal;

        $saleTransaction->save();

        $this->createDetails($saleTransaction, $saleDetailTransactionsData->getSaleDetailTransactions());
        $this->createPayments($saleTransaction);
        $this->createPromos($saleTransaction);

        $activeLoyalty = $this->activeLoyalty($saleTransaction->entity_id);
        $earnPoint = (new CustomerPointEarnService($saleTransaction, $activeLoyalty))->earn();
        $redeemPoint = (new CustomerPointRedeemService($saleTransaction, $activeLoyalty))->redeem();

        $saleTransaction->update([
            'earn_point' => $earnPoint,
            'redeem_point' => $redeemPoint,
        ]);

        return $saleTransaction;
    }

    private function buildDetails(): SaleTransactionDetailData
    {
        $saleDetailTransactionData = new SaleTransactionDetailData();

        foreach ($this->customerOrder->customerOrderDetails()->get() as $customerOrderDetail)
        {
            $saleTransactionDetail = new SaleTransactionDetail();

            $saleTransactionDetail->customer_order_detail_id = $customerOrderDetail->id;

            DetailFieldSetter::brand($this->mappedBrand, $saleTransactionDetail, $customerOrderDetail);
            DetailFieldSetter::location($this->mappedLocation, $saleTransactionDetail, $this->customerOrder);
            DetailFieldSetter::orderType($this->mappedOrderType, $saleTransactionDetail, $customerOrderDetail);
            DetailFieldSetter::product($this->mappedProduct, $saleTransactionDetail, $customerOrderDetail);
            DetailFieldSetter::productCategory($this->mappedProductCategory, $saleTransactionDetail, $customerOrderDetail);
            DetailFieldSetter::productUnit($this->mappedProductUnit, $saleTransactionDetail, $customerOrderDetail);
            DetailFieldSetter::tax($this->mappedTax, $saleTransactionDetail, $customerOrderDetail);

            $saleTransactionDetail->notes = $customerOrderDetail->notes;
            $saleTransactionDetail->quantity = $customerOrderDetail->quantity;
            $saleTransactionDetail->cancelled_quantity = 0;

            $this->setCalculatedTaxAmount($saleTransactionDetail, $customerOrderDetail, 'sell_price');
            $this->setCalculatedTaxAmount($saleTransactionDetail, $customerOrderDetail, 'promo_amount');
            $this->setCalculatedTaxAmount($saleTransactionDetail, $customerOrderDetail, 'discount_amount');
            $this->setCalculatedTaxAmount($saleTransactionDetail, $customerOrderDetail, 'surcharge_amount');
            $this->setCalculatedTaxAmount($saleTransactionDetail, $customerOrderDetail, 'free_of_charge_amount');
            $this->setCalculatedTaxAmount($saleTransactionDetail, $customerOrderDetail, 'total_line_amount');

            $this->setServiceCharge($saleTransactionDetail, $customerOrderDetail);

            $this->setCalculatedTaxAmount($saleTransactionDetail, $customerOrderDetail, 'prorate_promo_amount');
            $this->setCalculatedTaxAmount($saleTransactionDetail, $customerOrderDetail, 'prorate_discount_amount');
            $this->setCalculatedTaxAmount($saleTransactionDetail, $customerOrderDetail, 'prorate_surcharge_amount');
            $this->setCalculatedTaxAmount($saleTransactionDetail, $customerOrderDetail, 'prorate_free_of_charge_amount');

            $saleTransactionDetail->modifier_subtotal = 0;
            $saleTransactionDetail->modifier_subtotal_tax_amount = 0;
            $saleTransactionDetail->modifier_service_charge = 0;
            $saleTransactionDetail->modifier_service_charge_tax_amount = 0;
            $saleTransactionDetail->modifier_prorate_promo_amount = 0;
            $saleTransactionDetail->modifier_prorate_promo_amount_tax_amount = 0;
            $saleTransactionDetail->modifier_prorate_discount_amount = 0;
            $saleTransactionDetail->modifier_prorate_discount_amount_tax_amount = 0;
            $saleTransactionDetail->modifier_prorate_surcharge_amount = 0;
            $saleTransactionDetail->modifier_prorate_surcharge_amount_tax_amount = 0;
            $saleTransactionDetail->modifier_prorate_promo_amount_tax_amount = 0;
            $saleTransactionDetail->modifier_prorate_free_of_charge_amount = 0;
            $saleTransactionDetail->modifier_prorate_free_of_charge_amount_tax_amount = 0;
            $saleTransactionDetail->modifier_total_amount = 0;
            $saleTransactionDetail->modifier_total_amount_tax_amount = 0;

            $saleTransactionDetail->loyalty_id = $customerOrderDetail->loyalty_id;
            $saleTransactionDetail->loyalty_reward_product_id = $customerOrderDetail->loyalty_reward_product_id;
            $saleTransactionDetail->loyalty_point = $customerOrderDetail->loyalty_point;

            $this->setCalculatedTaxAmount($saleTransactionDetail, $customerOrderDetail, 'total_amount');

            $saleDetailTransactionData
                ->addSaleDetailTransactions($saleTransactionDetail)
                ->addProductIds($saleTransactionDetail->product_id)
                ->addProductCategoryIds($saleTransactionDetail->product_category_id)
                ->addGrossSales($saleTransactionDetail)
                ->addGrossProfit($saleTransactionDetail)
                ->addDiscountAmountBeforeTax($saleTransactionDetail)
                ->addSurchargeAmountBeforeTax($saleTransactionDetail)
                ->addPromoAmountBeforeTax($saleTransactionDetail)
                ->addFreeOfChargeAmountBeforeTax($saleTransactionDetail)
                ->addServiceChargeBeforeTax($saleTransactionDetail)
                ->addTaxAmount($saleTransactionDetail);
        }

        return $saleDetailTransactionData;
    }

    private function createDetails(SaleTransaction $saleTransaction, array  $saleDetailTransactions)
    {
        foreach ($saleDetailTransactions as $saleDetailTransaction)
        {
            $saleDetailTransaction->sale_transaction_id = $saleTransaction->id;

            $saleDetailTransaction->sales_at = $saleTransaction->sales_at;
            $saleDetailTransaction->local_sales_at = $saleTransaction->local_sales_at;
            $saleDetailTransaction->modifier_ids = [];
            $saleDetailTransaction->modifier_option_ids = [];

            $saleDetailTransaction->save();

            $this->importStockMovement(
                $saleDetailTransaction,
                $saleDetailTransaction->location()->first(),
                $saleDetailTransaction->product()->first(),
                $saleDetailTransaction->productUnit()->first(),
                $saleDetailTransaction->quantity,
            );
        }
    }

    private function createPayments(SaleTransaction $saleTransaction)
    {
        foreach ($this->params['payments'] as $payment)
        {
            $saleTransactionPayment = new SaleTransactionPayment();

            $saleTransactionPayment->sale_transaction_id = $saleTransaction->id;

            $this->setPaymentMethod($saleTransactionPayment, $payment['payment_method_id']);
            $saleTransactionPayment->amount_receive = $payment['amount_receive'];
            $saleTransactionPayment->change = $payment['change'];
            $saleTransactionPayment->subsidize = 0;
            $saleTransactionPayment->platform_fee = 0;

            $saleTransactionPayment->save();
        }
    }

    private function createPromos(SaleTransaction $saleTransaction)
    {
        $salePromos = [];

        foreach($this->customerOrder->customerOrderPromos()->get() as $customerOrderPromo)
        {
            # only header here
            if ($customerOrderPromo->customer_order_detail_id != null) {
                continue;
            }

            array_push($salePromos, [
                'promo_id' => $customerOrderPromo->promo_id,
                'promo_name' => $customerOrderPromo->promo_name,
                'promo_reward_id' => $customerOrderPromo->promo_reward_id,
                'amount' => $customerOrderPromo->amount,
                'applied_promo_amount' => $customerOrderPromo->applied_promo_amount,
                'promo_reward_template' => $customerOrderPromo->promo_reward_template,
                'promo_reward_percentage' => $customerOrderPromo->promo_reward_percentage,
                'promo_reward_amount' => $customerOrderPromo->promo_reward_amount,
                'promo_reward_maximum_amount' => $customerOrderPromo->promo_reward_maximum_amount,
            ]);
        }

        $saleTransaction->saleTransactionPromos()->createMany($salePromos);
    }

    private function setPaymentMethod(SaleTransactionPayment $saleTransactionPayment, int $paymentMethodId)
    {
        if (!array_key_exists($paymentMethodId, $this->mappedPaymentMethod)) {
            $this->mappedPaymentMethod[$paymentMethodId] = PaymentMethod::find($paymentMethodId);
        }
        $saleTransactionPayment->payment_method_id = $paymentMethodId;
        $saleTransactionPayment->payment_method_name = $this->mappedPaymentMethod[$paymentMethodId]->name;
    }

    private function setCalculatedTaxAmount(SaleTransactionDetail $saleTransactionDetail, CustomerOrderDetail $customerOrderDetail, string $key)
    {
        $taxCalculated = TaxCalculator::calculate($customerOrderDetail[$key], $saleTransactionDetail->tax_rate, $saleTransactionDetail->tax_setting);
        
        $saleTransactionDetail[$key] = $taxCalculated->getPrice();
        $saleTransactionDetail[$key . '_tax_amount'] = $taxCalculated->getTaxAmount();
    }

    private function setServiceCharge(SaleTransactionDetail $saleTransactionDetail, CustomerOrderDetail $customerOrderDetail)
    {
        $taxCalculated = TaxCalculator::calculate($customerOrderDetail->service_charge, $saleTransactionDetail->tax_rate, $saleTransactionDetail->tax_setting);
        
        $saleTransactionDetail->service_charge = $taxCalculated->getPrice();
        $saleTransactionDetail->service_charge_tax_amount = $taxCalculated->getTaxAmount();
        $saleTransactionDetail->service_charge_rate = $customerOrderDetail->service_charge_rate;
        $saleTransactionDetail->service_charge_include_tax = $customerOrderDetail->service_charge_include_tax;
    }

    private function generateReceiptNo()
    {
        return UniqueCodeGenerator::generateCode();
    }

    private function generateSalesNo($localTimeNow, $initial, $locationId)
    {
        $salesNo = DB::transaction(function () use ($localTimeNow, $initial, $locationId) {
            // yymmdd/<initial lokasi>/<5 digit>
            $invoiceNmber = SalesInvoiceNumber::lockForUpdate()->where('location_id', $locationId)->where('sales_date', Carbon::parse($localTimeNow)->format('Ymd'))->first();
            if ($invoiceNmber == null) {
                $invoiceNmber = new SalesInvoiceNumber();
                $invoiceNmber->sales_date = $localTimeNow;
                $invoiceNmber->location_id = $locationId;
            }
            $invoiceNmber->invoice_number = $invoiceNmber->invoice_number + 1;
            $invoiceNmber->save();

            $salesNo = Carbon::parse($localTimeNow)->format('Ymd') . '/';
            $salesNo .= $initial . '/';
            $salesNo .= sprintf('%05d', $invoiceNmber->invoice_number);

            return $salesNo;
        });

        return $salesNo;
    }

    private function activeLoyalty(int $entityId): ?Loyalty
    {
        return Loyalty::where('entity_id', $entityId)->where('status', 'active')->first();
    }

    private function importStockMovement(SaleTransactionDetail $saleTransactionDetail, Location $location, Product $product, ProductUnit $productUnit, int $stock)
    {
        $data = new ProductStockMovement();

        $data->product_id = $product->id;
        $data->location_id = $location->id;
        $data->product_unit_id = $productUnit->id;

        $data->original_product_unit_id = $productUnit->id;

        $data->resource_id = $saleTransactionDetail->id;
        $data->resource_type = $saleTransactionDetail::class;

        $data->original_stock_out = $stock ?? 0;
        $data->original_stock_in = 0;
        $data->original_buying_price = $product->cost_of_goods_sold;
        $data->conversion_stock = 1; # should find conversion, not for now

        $data->stock_in = $data->original_stock_in * $data->conversion_stock;
        $data->stock_out = $data->original_stock_out * $data->conversion_stock;
        $data->buying_price = $data->original_buying_price * $data->conversion_stock;

        $data->save();
    }

    private function findEmployee(?int $employeeId): ?Employee
    {
        if ($employeeId == null) {
            return null;
        }

        return Employee::find($employeeId);
    }

    private function findCustomer(?int $customerId): ?Customer
    {
        if ($customerId == null) {
            return null;
        }

        return Customer::find($customerId);
    }
}
