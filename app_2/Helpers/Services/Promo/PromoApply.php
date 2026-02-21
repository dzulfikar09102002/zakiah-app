<?php

namespace App\Helpers\Services\Promo;

use App\Enums\PromoRewardTemplateEnum;
use App\Helpers\Data\CustomerOrder\CustomerOrder as CustomerOrderData;
use App\Helpers\Data\Promo\PromoApplicable;
use App\Helpers\Data\Promo\PromoData;
use App\Helpers\PercentageCalculation;

class PromoApply
{
    private CustomerOrderData $customerOrderData;
    private PromoApplicable $appliedPromos;

    /**
     * Create a new class instance.
     */
    public function __construct(CustomerOrderData $customerOrderData, PromoApplicable $appliedPromos)
    {
        //
        $this->customerOrderData = $customerOrderData;
        $this->appliedPromos = $appliedPromos;
    }

    public function apply()
    {
        $calculatePromos = $this->calculatePromo();

        $this->customerOrderData->setPromoAmount($calculatePromos['promoAmount']);
        $this->customerOrderData->setPromoIds($this->appliedPromos->getPromoIds());

        $this->setPromos($calculatePromos['promos']);
    }

    private function calculatePromo(): array
    {
        $promoAmount = 0;
        $promos = [];
        $subTotal = $this->customerOrderData->getSubTotal();

        foreach($this->appliedPromos->getPromoTotalOrder() as $promo)
        {
            $promoReward = $promo['promoReward'];
            $rewardedAmount = 0;

            switch ($promoReward->template) {
                case PromoRewardTemplateEnum::DiscountPercentage->value:
                    $rewardedAmount = PercentageCalculation::calculate(
                        $subTotal,
                        $promoReward->reward_amount,
                        $promoReward->reward_maximum_amount,
                    );
                    break;
                case PromoRewardTemplateEnum::DiscountFixed->value:
                    $rewardedAmount = $promoReward->reward_amount;
                    break;
            }

            $promoAmount += $rewardedAmount;

            array_push($promos, (new PromoData())
                ->setPromoId($promo->id)
                ->setPromoName($promo->name)
                ->setPromoRewadId($promoReward->id)
                ->setQuantity(1)
                ->setProductId(null)
                ->setProductCategoryId(null)
                ->setAmount($subTotal)
                ->setAppliedPromoAmount($promoAmount)
                ->setPromoRewardTemplate($promoReward->template)
                ->setPromoRewardPercentage($promoReward->percentage)
                ->setPromoRewardAmount($promoReward->reward_amount)
                ->setPromoRewardMaximumAmount($promoReward->reward_maximum_amount)
            );
        }

        return [
            "promoAmount" => $promoAmount,
            "promos" => $promos,
        ];
    }

    /**
     * @param PromoData[] $promos
     */
    private function setPromos(array $promos)
    {
        foreach ($promos as $promo)
        {
            $this->customerOrderData->appendPromo($promo);
        }
    }
}
