<?php

namespace App\Helpers\Services\Promo;

use App\Models\Entity;
use App\Models\Promo;
use App\Models\PromoReward;
use App\Models\PromoRewardProduct;
use App\Models\PromoRule;
use App\Models\PromoRuleCustomerCategory;
use App\Models\PromoRuleOrderType;
use App\Models\PromoRuleProduct;

class PromoUpdater extends PromoCreator
{
    private Promo $promo;

    /**
     * Create a new class instance.
     */
    public function __construct(Entity $entity, Promo $promo, array $params)
    {
        //
        parent::__construct($entity, $params);

        $this->promo = $promo;
    }

    public function create(): Promo
    {
        $this->promo->select_all_location = true; # hardcode for now
        $this->promo->fill($this->params);
        $this->promo->save();
        
        $this->deletePromoRule();
        $this->deletePromoReward();

        $this->createPromoRule($this->promo);
        $this->createPromoReward($this->promo);

        return $this->promo;
    }

    private function deletePromoRule()
    {
        PromoRuleCustomerCategory::where('promo_id', $this->promo->id)->delete();
        PromoRuleOrderType::where('promo_id', $this->promo->id)->delete();
        PromoRuleProduct::where('promo_id', $this->promo->id)->delete();
        PromoRule::where('promo_id', $this->promo->id)->delete();
    }

    private function deletePromoReward()
    {
        PromoRewardProduct::where('promo_id', $this->promo->id)->delete();
        PromoReward::where('promo_id', $this->promo->id)->delete();
    }
}
