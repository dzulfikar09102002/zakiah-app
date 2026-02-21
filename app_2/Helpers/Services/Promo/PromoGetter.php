<?php

namespace App\Helpers\Services\Promo;

use App\Enums\PromoRewardAppliedToEnum;
use App\Helpers\Data\CustomerOrder\CustomerOrder as CustomerOrderData;
use App\Helpers\Data\Promo\PromoApplicable;
use App\Helpers\Services\CustomerOrder\LineCalculator;
use App\Models\Promo;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class PromoGetter
{
    private CustomerOrderData $customerOrderData;
    private LineCalculator $lineCalculator;
    private array $promoIds;

    /**
     * Create a new class instance.
     */
    public function __construct(array $promoIds, CustomerOrderData $customerOrderData, LineCalculator $lineCalculator)
    {
        //
        $this->customerOrderData = $customerOrderData;
        $this->lineCalculator = $lineCalculator;
        $this->promoIds = $promoIds;
    }

    public function get(): PromoApplicable
    {
        $promoIds = [];
        $promos = [
            PromoRewardAppliedToEnum::TotalOrder->value => [],
            PromoRewardAppliedToEnum::Product->value => [],
            PromoRewardAppliedToEnum::ProductCategory->value => [],
        ];

        foreach($this->getApplicablePromos() as $applicablePromo)
        {
            array_push($promoIds, $applicablePromo->id);

            switch ($applicablePromo['promoReward']['applied_to']) {
                case PromoRewardAppliedToEnum::TotalOrder->value:
                    array_push($promos[PromoRewardAppliedToEnum::TotalOrder->value], $applicablePromo);
                    break;
                case PromoRewardAppliedToEnum::Product->value:
                    array_push($promos[PromoRewardAppliedToEnum::Product->value], $applicablePromo);
                    break;
                case PromoRewardAppliedToEnum::ProductCategory->value:
                    array_push($promos[PromoRewardAppliedToEnum::ProductCategory->value], $applicablePromo);
                    break;
            }
        }

        return (new PromoApplicable())
            ->setPromoIds($promoIds)
            ->setPromoProduct($promos[PromoRewardAppliedToEnum::Product->value])
            ->setPromoProductCategory($promos[PromoRewardAppliedToEnum::ProductCategory->value])
            ->setPromoTotalOrder($promos[PromoRewardAppliedToEnum::TotalOrder->value]);
    }

    private function getApplicablePromos()
    {
        $applicablePromos = [];

        foreach ($this->getPromos() as $promo)
        {
            if (!(new PromoValidator($promo, $this->customerOrderData, $this->lineCalculator))->validate()) {
                continue;
            }

            array_push($applicablePromos, $promo);
        }

        return $applicablePromos;
    }

    private function getPromos(): Collection
    {
        $promoIds = $this->promoIds;
        $locationId = $this->customerOrderData->getLocation()->id;
        $timeNow = new DateTime();
        // $timezone = new DateTimeZone($this->customerOrderData->getLocation()->timezone ?? 'UTC');

        return Promo::with([
                'promoRule.promoRuleCustomerCategories',
                'promoRule.promoRuleOrderTypes:order_type_id',
                'promoRule.promoRuleProducts',
                'promoReward.promoRewardProducts',
            ])
            ->where(function (Builder $query) use($promoIds) {
                $query
                    ->where('auto_apply', true)
                    ->orWhereIn('id', $promoIds);
            })
            ->where(function (Builder $query) use($locationId) {
                $query
                    ->where('select_all_location', true)
                    ->orWhere('owner_location_id', $locationId);
            })
            ->where(function (Builder $query) use($timeNow) {
                $query
                    ->where('end_at', '>=', $timeNow)
                    ->orWhere('end_at', null);
            })
            ->where('start_at', '<=', $timeNow)
            ->get();
    }
}
