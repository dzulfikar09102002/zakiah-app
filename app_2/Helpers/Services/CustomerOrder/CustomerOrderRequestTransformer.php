<?php

namespace App\Helpers\Services\CustomerOrder;

use App\Enums\TaxSettingEnum;
use App\Helpers\Data\Customer\CustomerData;
use App\Helpers\Data\CustomerOrder\Adjustment;
use App\Helpers\Data\CustomerOrder\CustomerOrder;
use App\Helpers\Data\CustomerOrder\CustomerOrderLine;
use App\Helpers\Data\Promo\PromoApplicable;
use App\Helpers\ProductSellPriceFinder;
use App\Helpers\ProrateCalculation;
use App\Helpers\Services\Customer\CustomerDataBuilder;
use App\Helpers\Services\Promo\PromoApply;
use App\Helpers\Services\Promo\PromoApplyProduct;
use App\Helpers\Services\Promo\PromoGetter;
use App\Helpers\UniqueCodeGenerator;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Entity;
use App\Models\Location;
use App\Models\OrderType;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductSellPrice;
use Exception;
use Illuminate\Http\Request;

class CustomerOrderRequestTransformer
{
    protected Entity $entity;
    protected Device $device;
    protected Employee $employee;
    protected Request $request;
    protected array $params;
    protected CustomerOrder $customerOrderData;
    protected RelationBuilder $relationBuilder;
    protected LineCalculator $lineCalculator;
    protected PromoApplicable $promoApplicable;
    protected int $subTotalOrder, $totalItemOrder;
    protected string $code;

    /**
     * Create a new class instance.
     */
    public function __construct(Entity $entity, Device $device, Request $request)
    {
        //
        ## TODO
        # generate variable
        $this->entity = $entity;
        $this->device = $device;

        $this->request = $request;
        $this->params = $this->request->validated();
        $this->employee = $this->request->employee;
        $this->customerOrderData = new CustomerOrder();
        $this->promoApplicable = new PromoApplicable();

        $this->code = UniqueCodeGenerator::generateCode();
    }

    public function transform(): CustomerOrder
    {
        try {
            $this->generateVariable();
        } catch (Exception $e) {
            throw $e;
        }

        return $this->customerOrderData;
    }

    private function generateVariable()
    {
        $this->customerOrderData->setCode($this->params['code'] ?? $this->code);
        $this->customerOrderData->setEntity($this->entity);
        $this->customerOrderData->setEntity($this->entity);
        $this->customerOrderData->setDevice($this->device);
        $this->customerOrderData->setLocation($this->getLocation());
        $this->customerOrderData->setOrderType($this->getOrderType($this->params['order_type_id']));

        $serviceChargeGetter = new ServiceChargeGetter($this->customerOrderData->getLocation());
        $this->customerOrderData->setServiceChargeRate($serviceChargeGetter->getServiceChargeRate());
        $this->customerOrderData->setServiceChargeIncludeTax($serviceChargeGetter->getServiceChargeIncludeTax());

        $this->relationBuilder = $this->getRelationBuilder();
        $this->lineCalculator = $this->getLineCalculator();
        $this->subTotalOrder = $this->lineCalculator->getSubtotal();
        $this->totalItemOrder = $this->lineCalculator->getTotalItem();

        $this->customerOrderData->setSubTotal($this->subTotalOrder);
        $this->customerOrderData->setTotalItem($this->totalItemOrder);
        $this->customerOrderData->setCustomerData($this->getCustomerData());

        $this->promoApplicable = $this->getApplicablePromo();

        $newLines = (new PromoApplyProduct($this->customerOrderData, $this->relationBuilder, $this->params['products'], $this->promoApplicable ))->apply();

        $this->lineCalculator->recalculate($newLines);
        $this->subTotalOrder = $this->lineCalculator->getSubtotal();
        $this->totalItemOrder = $this->lineCalculator->getTotalItem();
        $this->customerOrderData->setSubTotal($this->subTotalOrder);
        $this->customerOrderData->setTotalItem($this->totalItemOrder);

        (new PromoApply($this->customerOrderData, $this->promoApplicable ))->apply();

        $this->customerOrderData->setAdjustment($this->getAdjustment($this->params, $this->subTotalOrder));
        $this->customerOrderData->setTaxExclusiveAmount($this->lineCalculator->getTaxExclusiveAmount());
        $this->customerOrderData->setTaxInclusiveAmount($this->lineCalculator->getTaxInclusiveAmount());
        $this->customerOrderData->setServiceCharge($this->lineCalculator->getServiceCharge());
        $this->customerOrderData->setDiscountAmount($this->customerOrderData->getAdjustment()?->getDiscountAmount() ?? 0);
        $this->customerOrderData->setSurchargeAmount($this->customerOrderData->getAdjustment()?->getSurchargeAmount() ?? 0);
        $this->customerOrderData->setFreeOfChargeAmount(0);
        $this->customerOrderData->setRoundingAmount(0);
        $this->customerOrderData->setPaymentPlatformFee($this->getCalculatedPaymentFee(
            $this->subTotalOrder - $this->customerOrderData->getDiscountAmount() - 
            $this->customerOrderData->getPromoAmount() + $this->customerOrderData->getSurchargeAmount()
        ));
        $this->customerOrderData->setPlatformFee(0);
        $this->customerOrderData->setDeliveryFee(0);
        $this->customerOrderData->setPromoDeliveryFee(0);
        $this->customerOrderData->setTotalAmount();
        $this->customerOrderData->setNotes($this->params['notes'] ?? null);

        $this->customerOrderData->setCustomerOrderLines($this->getCustomerOrderLines($newLines));
    }

    private function getLocation(): Location
    {
        return $this->entity->locations()->where('id', $this->params['location_id'])->first();
    }

    private function getOrderType(int $orderTypeId): OrderType
    {
        return $this->entity->orderTypes()->where('id', $orderTypeId)->first();
    }

    private function getApplicablePromo(): PromoApplicable
    {
        $promoIds = [];
        if (array_key_exists('promo_ids', $this->params)) {
            $promoIds = $this->params['promo_ids'];
        }

        return (new PromoGetter(
            $promoIds,
            $this->customerOrderData,
            $this->lineCalculator,
        ))->get();
    }

    private function getCustomerData(): ?CustomerData
    {
        if (!array_key_exists('customer', $this->params)) {
            return null;
        }

        if ($this->params['customer'] == null) {
            return null;
        }

        $builder = new CustomerDataBuilder(
            $this->entity,
            $this->customerOrderData->getLocation(),
            $this->params['customer'],
        );

        return $builder->build();
    }

    private function getCalculatedPaymentFee(int $subTotalOrder): int
    {
        if (!array_key_exists('payments', $this->params)) {
            return 0;
        }

        if ($this->params['payments'] == null) {
            return 0;
        }

        $fee = 0;
        foreach ($this->params['payments'] as $line) {
            $payment = PaymentMethod::find($line['payment_method_id']);

            $fee += $payment->fixed_fee;
            $fee += $payment->variable_fee * $subTotalOrder / 100;
        }

        return $fee;
    }

    /**
     * 
     * @return CustomerOrderLine[]
     * 
     */
    private function getCustomerOrderLines(array $lines): array
    {
        $builder = $this->relationBuilder;

        $products = $builder->getProducts();
        $productCategories = $builder->getProductCategories();
        $productPrices = $builder->getProductPrices();
        $brands = $builder->getBrands();
        $orderTypes = $builder->getOrderTypes();
        $productUnits = $builder->getProductUnits();
        $taxes = $builder->getTaxes();

        $idx = 0;
        $customerOrderLines = array_fill(0, count($lines), []);

        foreach ($lines as $line)
        {
            $customerOrderLine = new CustomerOrderLine();
            $customerOrderLine->setId($line['id'] ?? 0);
            $customerOrderLine->setCustomerOrderDetailId($line['customer_order_detail_id'] ?? 0);
            $customerOrderLine->setDestroy($line['_destroy'] ?? false);
            $customerOrderLine->setSubTotalOrder($this->subTotalOrder);

            $foundProduct = $products->get($line['product_id']);
            $customerOrderLine->setProduct($foundProduct);
            $customerOrderLine->setProductCategory($this->findProductCategory($foundProduct, $productCategories));

            $customerOrderLine->setEmployee($this->employee);
            $customerOrderLine->setCustomerData($this->customerOrderData->getCustomerData());
            $customerOrderLine->setTax($this->findTax($foundProduct, $taxes, $productPrices, $line));
            $customerOrderLine->setTaxSetting($this->findTaxSetting($foundProduct, $productPrices));
            $customerOrderLine->setOrderType($orderTypes[$line['order_type_id'] ?? $builder->getDefaultOrderTypeId()]);
            $customerOrderLine->setProductUnit($productUnits[$line['product_unit_id']]);

            $customerOrderLine->setBrand(null);
            if (array_key_exists('brand_id', $line) && $line['brand_id'] != null) {
                $customerOrderLine->setBrand($brands[$line['brand_id']]);
            }

            $this->setLoyalty($customerOrderLine, $line);
            $this->setSellPriceLine($foundProduct, $productPrices, $customerOrderLine, $line);

            $customerOrderLine->setQuantity($line['quantity']);
            $customerOrderLine->setNotes($line['notes'] ?? null);
            $customerOrderLine->setServiceChargeIncludeTax($this->customerOrderData->getServiceChargeIncludeTax());
            $customerOrderLine->setServiceChargeRate($this->customerOrderData->getServiceChargeRate());
            $customerOrderLine->setPromoAmountOrder($this->customerOrderData->getPromoAmount());
            $customerOrderLine->setDiscountAmountOrder($this->customerOrderData->getDiscountAmount());
            $customerOrderLine->setSurchargeAmountOrder($this->customerOrderData->getSurchargeAmount());

            if (array_key_exists('promo', $line)) {
                $customerOrderLine->setPromo($line['promo']);
            } else {
                $customerOrderLine->setPromo(null);
            }

            $customerOrderLine->setTotalLineAmount();

            $customerOrderLine->setProrateDiscountAmount(ProrateCalculation::calculate(
                $customerOrderLine->getSellPrice(), $this->subTotalOrder, $this->customerOrderData->getDiscountAmount()
            ));
            $customerOrderLine->setProratePromoAmount(ProrateCalculation::calculate(
                $customerOrderLine->getSellPrice(), $this->subTotalOrder, $this->customerOrderData->getPromoAmount()
            ));
            $customerOrderLine->setProrateSurchargeAmount(ProrateCalculation::calculate(
                $customerOrderLine->getSellPrice(), $this->subTotalOrder, $this->customerOrderData->getSurchargeAmount()
            ));

            $customerOrderLine->setModifierTotalAmount(0);
            $customerOrderLine->setTotalAmount();

            $customerOrderLines[$idx] = $customerOrderLine;
            $idx++;
        }

        return $customerOrderLines;
    }

    private function setSellPriceLine(?Product $foundProduct, array $productPrices, CustomerOrderLine &$customerOrderLine, array $line): int
    {
        $sellPrice = ProductSellPriceFinder::findProductSellPriceFromParam($foundProduct, $productPrices, $line);

        $customerOrderLine->setSellPrice($sellPrice);
        $customerOrderLine->setCustomPrice($line['custom_price']);

        if ($customerOrderLine->getLoyaltyId() != null) {
            $customerOrderLine->setAdjustment(null);
        } else {
            $customerOrderLine->setAdjustment($this->getAdjustment($line, $sellPrice));
        }

        return $sellPrice;
    }

    private function setLoyalty(CustomerOrderLine &$customerOrderLine, array $line)
    {
        if (!array_key_exists('loyalty_id', $line) || !array_key_exists('loyalty_reward_product_id', $line) || !array_key_exists('loyalty_point', $line)) {
            $customerOrderLine->setLoyaltyId(null);
            $customerOrderLine->setLoyaltyRewardProductId(null);
            $customerOrderLine->setLoyaltyRewardProductPoint(0);

            return;
        }

        $customerOrderLine->setLoyaltyId($line['loyalty_id']);
        $customerOrderLine->setLoyaltyRewardProductId($line['loyalty_reward_product_id']);
        $customerOrderLine->setLoyaltyRewardProductPoint($line['loyalty_point']);
    }

    private function getRelationBuilder(): RelationBuilder
    {
        return new RelationBuilder(
            $this->params['products'],
            $this->entity,
            $this->customerOrderData->getLocation(),
            $this->customerOrderData->getOrderType(),
        );
    }

    private function getLineCalculator(): LineCalculator
    {
        return new LineCalculator($this->relationBuilder, $this->params['products']);   
    }

    /**
     * @param ProductSellPrice[] $productPrices
     */
    private function findTax(Product $product, array $taxes, array $productPrices, array $line): ?int
    {
        return null;
        
        if (count($taxes) <= 0) {
            return null;
        }

        # next
        # find using product price
        $key = $line['product_id'] . $line['order_type_id'] . $line['product_unit_id'];
        if (array_key_exists($key, $productPrices)) {
            return $taxes[$productPrices[$key]->tax_id];
        }

        return $taxes[$product->tax_id];
    }

    /**
     * @param ProductSellPrice[] $productPrices
     */
    private function findTaxSetting(Product $product, array $productPrices): ?TaxSettingEnum
    {
        # next
        # find using product price
        if (array_key_exists($product->id, $productPrices)) {
            return $productPrices[$product->id]->tax_setting;
        }

        return $product->tax_setting;
    }

    private function getAdjustment(array $line, int $price): ?Adjustment
    {
        if (!array_key_exists('adjustment', $line)) {
            return null;
        }

        $adjustmentParam = $line['adjustment'];
        if ($adjustmentParam == null) {
            return null;
        }

        $promoId = null;
        if (array_key_exists('promo_id', $adjustmentParam)) {
            $promoId = $adjustmentParam['promo_id'];
        }

        return (new Adjustment())
                ->setQuantity($adjustmentParam['quantity'])
                ->setAmount($adjustmentParam['amount'])
                ->setFreeOfCharge($adjustmentParam['free_of_charge'])
                ->setIsPercentage($adjustmentParam['is_percentage'])
                ->setPromoId($promoId)
                ->setPrice($price);
    }

    /**
     * @param ProductSellPrice[] $productPrices
     */
    private function findProductCategory(Product $product, array $productCategories): ?ProductCategory
    {
        if (count($productCategories) <= 0) {
            return null;
        }

        return $productCategories[$product->product_category_id];
    }
}
