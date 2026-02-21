<?php

namespace App\Helpers\Services\Promo;

use App\Helpers\UniqueCodeGenerator;
use App\Models\CustomerCategory;
use App\Models\Entity;
use App\Models\OrderType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promo;
use App\Models\PromoReward;
use App\Models\PromoRewardProduct;
use App\Models\PromoRule;
use App\Models\PromoRuleCustomerCategory;
use App\Models\PromoRuleOrderType;
use App\Models\PromoRuleProduct;

class PromoCreator
{
    protected array $params;
    protected array $mappedProducts, $mappedProductCategories;
    protected Entity $entity;

    /**
     * Create a new class instance.
     */
    public function __construct(Entity $entity, array $params)
    {
        //
        $this->entity = $entity;
        $this->params = $params;

        $this->mappedProducts = array();
        $this->mappedProductCategories = array();
    }

    public function create(): Promo
    {
        $promo = new Promo();

        $promo->entity_id = $this->entity->id;
        $promo->code = UniqueCodeGenerator::generateCode();
        $promo->select_all_location = true; # hardcode for now
        $promo->fill($this->params);
        $promo->save();

        $this->createPromoRule($promo);
        $this->createPromoReward($promo);

        return $promo;
    }

    protected function createPromoReward(Promo $promo): PromoReward
    {
        $reward = new PromoReward();
        $reward->promo_id = $promo->id;
        $reward->fill($this->params['promo_reward']);
        $reward->save();

        $this->createPromoRewardProduct($promo, $reward);

        return $reward;
    }

    protected function createPromoRewardProduct(Promo $promo, PromoReward $reward)
    {
        if (!array_key_exists('promo_reward', $this->params) || !array_key_exists('products', $this->params['promo_reward'])) {
            return;
        }

        foreach ($this->params['promo_reward']['products'] as $product)
        {
            $rewardProduct = new PromoRewardProduct();
            $rewardProduct->promo_id = $promo->id;
            $rewardProduct->promo_reward_id = $reward->id;
            $rewardProduct->fill($product);

            # n + 1
            $this->buildPromoRewardProductForProduct($product, $rewardProduct);
            $this->buildPromoRewardProductForProductCategory($product, $rewardProduct);

            $rewardProduct->save();
        }
    }

    protected function buildPromoRewardProductForProduct(array $product, PromoRewardProduct $rewardProduct)
    {
        if (!array_key_exists('product_id', $product)) {
            return;
        }

        $id = $product['product_id'];
        if (!array_key_exists($id, $this->mappedProducts))
        {
            $this->mappedProducts[$id] = Product::find($id);
        }

        $rewardProduct->product_id = $this->mappedProducts[$id]->id;
        $rewardProduct->product_name = $this->mappedProducts[$id]->name;
    }

    protected function buildPromoRewardProductForProductCategory(array $product, PromoRewardProduct $rewardProduct)
    {
        if (!array_key_exists('product_category_id', $product))  {
            return;
        }

        $id = $product['product_category_id'];
        if (!array_key_exists($id, $this->mappedProductCategories))
        {
            $this->mappedProductCategories[$id] = ProductCategory::find($id);
        }

        $rewardProduct->product_category_id = $this->mappedProductCategories[$id]->id;
        $rewardProduct->product_category_name = $this->mappedProductCategories[$id]->name;
    }

    protected function createPromoRule(Promo $promo): PromoRule
    {
        $promoRule = new PromoRule();
        $promoRule->promo_id = $promo->id;
        $promoRule->fill($this->params['promo_rule']);
        $promoRule->save();

        $this->createPromoRuleCustomerCategory($promo, $promoRule);
        $this->createPromoRuleOrderType($promo, $promoRule);
        $this->createPromoRuleProduct($promo, $promoRule);

        return $promoRule;
    }

    protected function createPromoRuleCustomerCategory(Promo $promo, PromoRule $promoRule)
    {
        if (!array_key_exists('promo_rule', $this->params) || !array_key_exists('customer_category_ids', $this->params['promo_rule'])) {
            return;
        }

        $customerCategoryIds = $this->params['promo_rule']['customer_category_ids'];
        $customerCategories = CustomerCategory::where('entity_id', $this->entity->id)->whereIn('id', $customerCategoryIds)->get();
        foreach ($customerCategories as $customerCategory)
        {
            $promoRuleCustCategory = new PromoRuleCustomerCategory();
            $promoRuleCustCategory->promo_id = $promo->id;
            $promoRuleCustCategory->promo_rule_id = $promoRule->id;
            $promoRuleCustCategory->customer_category_id = $customerCategory->id;
            $promoRuleCustCategory->customer_category_name = $customerCategory->name;

            $promoRuleCustCategory->save();
        }
    }

    protected function createPromoRuleOrderType(Promo $promo, PromoRule $promoRule)
    {
        if (!array_key_exists('promo_rule', $this->params) || !array_key_exists('order_type_ids', $this->params['promo_rule'])) {
            return;
        }

        $ids = $this->params['promo_rule']['order_type_ids'];
        $datas = OrderType::where('entity_id', $this->entity->id)->whereIn('id', $ids)->get();
        foreach ($datas as $data)
        {
            $promoRuleCustCategory = new PromoRuleOrderType();
            $promoRuleCustCategory->promo_id = $promo->id;
            $promoRuleCustCategory->promo_rule_id = $promoRule->id;
            $promoRuleCustCategory->order_type_id = $data->id;
            $promoRuleCustCategory->order_type_name = $data->name;

            $promoRuleCustCategory->save();
        }
    }

    protected function createPromoRuleProduct(Promo $promo, PromoRule $promoRule)
    {
        if (!array_key_exists('promo_rule', $this->params) || !array_key_exists('products', $this->params['promo_rule'])) {
            return;
        }

        foreach ($this->params['promo_rule']['products'] as $product)
        {
            $promoRuleProduct = new PromoRuleProduct();
            $promoRuleProduct->promo_id = $promo->id;
            $promoRuleProduct->promo_rule_id = $promoRule->id;
            $promoRuleProduct->minimum_purchase = $product['minimum_purchase'];

            # n + 1
            $this->buildPromoRuleProductForProduct($product, $promoRuleProduct);
            $this->buildPromoRuleProductForProductCategory($product, $promoRuleProduct);

            $promoRuleProduct->save();
        }
    }

    protected function buildPromoRuleProductForProduct(array $product, PromoRuleProduct $promoRuleProduct)
    {
        if (!array_key_exists('product_id', $product)) {
            return;
        }

        $id = $product['product_id'];
        if (array_key_exists($id, $this->mappedProducts))
        {
            $this->mappedProducts[$id] = Product::find($id);
        }

        $promoRuleProduct->product_id = $this->mappedProducts[$id]->id;
        $promoRuleProduct->product_name = $this->mappedProducts[$id]->name;
    }

    protected function buildPromoRuleProductForProductCategory(array $product, PromoRuleProduct $promoRuleProduct)
    {
        if (!array_key_exists('product_category_id', $product))  {
            return;
        }

        $id = $product['product_category_id'];
        if (array_key_exists($id, $this->mappedProductCategories))
        {
            $this->mappedProductCategories[$id] = ProductCategory::find($id);
        }

        $promoRuleProduct->product_category_id = $this->mappedProductCategories[$id]->id;
        $promoRuleProduct->product_category_name = $this->mappedProductCategories[$id]->name;
    }
}
