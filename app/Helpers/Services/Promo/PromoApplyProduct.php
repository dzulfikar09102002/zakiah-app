<?php

namespace App\Helpers\Services\Promo;

use App\Enums\PromoRewardTemplateEnum;
use App\Helpers\Data\CustomerOrder\CustomerOrder as CustomerOrderData;
use App\Helpers\Data\CustomerOrder\ProductMap;
use App\Helpers\Data\Promo\PromoApplicable;
use App\Helpers\Data\Promo\PromoData;
use App\Helpers\PercentageCalculation;
use App\Helpers\ProductSellPriceFinder;
use App\Helpers\Services\CustomerOrder\RelationBuilder;
use App\Models\Promo;
use App\Models\PromoRewardProduct;
use Illuminate\Database\Eloquent\Collection;

# TODO
class PromoApplyProduct
{
    private CustomerOrderData $customerOrderData;
    private PromoApplicable $appliedPromos;
    private RelationBuilder $relationBuilder;
    private array $lines;
    private array $productPrices;

    /**
     * Create a new class instance.
     */
    public function __construct(CustomerOrderData $customerOrderData, RelationBuilder $relationBuilder, array $lines, PromoApplicable $appliedPromos)
    {
        //
        $this->customerOrderData = $customerOrderData;
        $this->appliedPromos = $appliedPromos;
        $this->lines = $lines;
        $this->relationBuilder = $relationBuilder;

        $this->productPrices = $this->relationBuilder->getProductPrices();
    }

    public function apply(): array
    {
        $products = $this->relationBuilder->getProducts();
        
        $promoRewardQuota = $this->buildPromoRewardQuota($this->buildMergedParams($products));

        $lines = [];
        $splittedLines = [];
        foreach ($this->sortedLines($products) as $line)
        {
            $foundProduct = $products->get($line['product_id']);
            $productId = $foundProduct->id;
            $productCategoryId = $foundProduct->product_category_id ?? 0;

            $sellPrice = ProductSellPriceFinder::findProductSellPriceFromParam($foundProduct, $this->productPrices, $line);
            
            foreach ($this->appliedPromos->getPromoProduct() as $promo)
            {
                $result = $this->buildSplitLineProduct($promoRewardQuota['product'][$promo->id], $productId, $line, $promo, $sellPrice);
                if ($result == null) {
                    continue;
                }

                $this->updateQuotaProduct($promoRewardQuota['product'][$promo->id], $productId, $result['rewardedQuantity']);

                if ($result['splittedLine'] != null) {
                    array_push($splittedLines, $result['splittedLine']);
                    continue;
                }
            }

            array_push($lines, $line);
        }

        $mergedLines = array_merge($lines, $splittedLines, $this->loyaltyLines());
        $promos = [];
        $promoIds = [];
        foreach ($mergedLines as $mergedLine)
        {
            if (!array_key_exists('promo', $mergedLine)) {
                continue;
            }

            array_push($promos, $mergedLine['promo']);
            array_push($promoIds, $mergedLine['promo']->getPromoId());
        }

        $this->customerOrderData->setPromos($promos)->setPromoIds($promoIds);

        return $mergedLines;
    }

    private function sortedLines(ProductMap $products): array
    {
        $productPrices = $this->productPrices;
        $nonLoyaltyLines = $this->nonLoyaltyLines();

        usort($nonLoyaltyLines, function($a, $b) use($products, $productPrices) {
            $sellPriceA = ProductSellPriceFinder::findProductSellPriceFromParam($products->get($a['product_id']), $productPrices, $a);
            $sellPriceB = ProductSellPriceFinder::findProductSellPriceFromParam($products->get($b['product_id']), $productPrices, $b);

            if ($sellPriceA > $sellPriceB) {
                return 1;
            } elseif ($sellPriceA < $sellPriceB) {
                return -1;
            }

            return 0;
        });

        return $this->nonLoyaltyLines();
    }

    private function nonLoyaltyLines(): array
    {
        return array_filter($this->lines, function ($line) {
            if (array_key_exists('loyalty_id', $line) && array_key_exists('loyalty_reward_product_id', $line)) {
                return null;
            }

            return $line;
        });
    }

    private function loyaltyLines(): array
    {
        return array_filter($this->lines, function ($line) {
            if (array_key_exists('loyalty_id', $line) && array_key_exists('loyalty_reward_product_id', $line)) {
                return $line;
            }

            return null;
        });
    }

    private function updateQuotaProduct(array &$quota, int $productId, int $quantity)
    {
        if ($quota == null) {
            return;
        }

        if ($quota[$productId] == 0) {
            return;
        }

        $quota[$productId] -= $quantity;
    }

    private function buildSplitLineProduct(array $quota, int $productId, array &$line, Promo $promo, int $sellPrice): array
    {
        if ($quota == null) {
            return null;
        }

        $productQuantity = $quota[$productId];
        if ($productQuantity == null || $productQuantity == 0) {
            return null;
        }

        $rewardedQuantity = $line['quantity'];
        if ($rewardedQuantity > $productQuantity) {
            $rewardedQuantity = $productQuantity;
        }


        $splittedLine = null;
        if ($line['quantity'] > $productQuantity) {
            # copy
            $splittedLine = array_merge(
                $line,
                ['quantity' => $productQuantity],
                ['promo' => $this->buildPromoData($promo, $line['quantity'], $sellPrice, $productId, null)],
            );

            $line = array_merge(
                $line,
                ['quantity' => $line['quantity'] - $productQuantity]
            );
        }
        else {
            # add adjustment
            $line = array_merge(
                $line,
                ['promo' => $this->buildPromoData($promo, $line['quantity'], $sellPrice, $productId, null)],
            );
        }

        return [
            'appliedLine' => $line,
            'rewardedQuantity' => $rewardedQuantity,
            'splittedLine' => $splittedLine,
        ];
    }

    private function buildPromoData(Promo $promo, int $quantity, int $price, ?int $productId, ?int $productCategoryId): PromoData
    {
        $promoReward = $promo['promoReward'];
        $promoAmount = 0;

        switch ($promoReward->template) {
            case PromoRewardTemplateEnum::DiscountPercentage->value:
                $promoAmount = PercentageCalculation::calculate(
                    $price,
                    $promoReward->reward_amount,
                    $promoReward->reward_maximum_amount,
                );
                break;
            case PromoRewardTemplateEnum::DiscountFixed->value:
                $promoAmount = $promoReward->reward_amount;
                break;
        }

        return (new PromoData())
            ->setPromoId($promo->id)
            ->setPromoName($promo->name)
            ->setPromoRewadId($promoReward['id'])
            ->setQuantity($quantity)
            ->setProductId($productId)
            ->setProductCategoryId($productCategoryId)
            ->setAmount($price)
            ->setAppliedPromoAmount($promoAmount)
            ->setPromoRewardTemplate($promoReward->template)
            ->setPromoRewardPercentage($promoReward->percentage)
            ->setPromoRewardAmount($promoReward->reward_amount)
            ->setPromoRewardMaximumAmount($promoReward->reward_maximum_amount);
    }

    private function buildPromoRewardQuota(array $mergedParam): array
    {
        return [
            'product' => $this->buildPromoRewardProductQuota($mergedParam['product']),
            'productCategory' => $this->buildPromoRewardProductCategoryQuota($mergedParam['productCategory']),
        ];
    }

    private function buildPromoRewardProductQuota(array $mergedProduct): array
    {
        $promoQuota = array();

        foreach ($this->appliedPromos->getPromoProduct() as $promo)
        {
            $promoReward = $promo['promoReward'];
            $promoRewardProducts = $promoReward['promoRewardProducts'];

            foreach ($mergedProduct as $productId => $quantity)
            {
                $foundPromoRewad = $this->findProductIdFromReward($promoRewardProducts, $productId, null);
                if ($foundPromoRewad == null) {
                    continue;
                }

                $promoQuota[$promo->id][$productId] = $foundPromoRewad->reward_quantity ?? $quantity;
            }
        }

        return $promoQuota;
    }

    private function buildPromoRewardProductCategoryQuota(array $mergedProductCategory): array
    {
        $promoQuota = array();

        foreach ($this->appliedPromos->getPromoProductCategory() as $promo)
        {
            $promoReward = $promo['promoReward'];
            $promoRewardProducts = $promoReward['promoRewardProducts'];

            foreach ($mergedProductCategory as $productCategoryId => $quantity)
            {
                $foundPromoRewad = $this->findProductIdFromReward($promoRewardProducts, null, $productCategoryId);
                if ($foundPromoRewad == null) {
                    continue;
                }

                $promoQuota[$promo->id][$productCategoryId] = $foundPromoRewad->reward_quantity ?? $quantity;
            }
        }

        return $promoQuota;
    }

    /**
     * @param PromoRewardProduct[] $promoRewardProducts
     */
    private function findProductIdFromReward(Collection $promoRewardProducts, ?int $productId, ?int $productCategoryId): ?PromoRewardProduct
    {
        foreach ($promoRewardProducts as $promoRewardProduct)
        {
            if ($promoRewardProduct->product_id == $productId) {
                return $promoRewardProduct;
            }

            if ($promoRewardProduct->product_category_id == $productCategoryId) {
                return $promoRewardProduct;
            }
        }

        return null;
    }

    private function buildMergedParams(ProductMap $products)
    {
        $mappedProduct = array();
        $mappedProductCategory = array();

        foreach ($this->lines as $line)
        {
            $foundProduct = $products->get($line['product_id']);
            $productId = $foundProduct->id;
            $productCategoryId = $foundProduct->product_category_id ?? 0;
            
            if (!array_key_exists($productId, $mappedProduct)) {
                $mappedProduct[$productId] = 0;
            }
            $mappedProduct[$productId] += $line['quantity'];
            
            if (!array_key_exists($productCategoryId, $mappedProductCategory)) {
                $mappedProductCategory[$productCategoryId] = 0;
            }
            $mappedProductCategory[$productCategoryId] += $line['quantity'];
        }

        return [
            'product' => $mappedProduct,
            'productCategory' => $mappedProductCategory,
        ];
    }
}
