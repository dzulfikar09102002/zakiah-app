<?php

namespace App\Services;

use App\Enums\PromoStatusEnum;
use App\Helpers\Services\Promo\PromoCreator;
use App\Helpers\Services\Promo\PromoUpdater;
use App\Models\CustomerCategory;
use App\Models\Location;
use App\Models\Promo;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pembuatan/perubahan promo memakai PromoCreator/PromoUpdater (logika backend lama).
 * Promo berlaku di kasir berdasarkan start_at/end_at (lihat PromoGetter).
 */
class PromoService
{
    public function getPromos(): LengthAwarePaginator
    {
        $search = request('search', '');
        $now = Carbon::now();

        return Promo::query()
            ->where('entity_id', $this->entityId())
            ->with(['ownerLocation:id,name', 'promoReward:id,promo_id,template,reward_amount,reward_maximum_amount'])
            ->when($search, function ($query) use ($search) {
                $query->where(fn ($q) => $q->whereLike('name', "%{$search}%")->orWhereLike('code', "%{$search}%"));
            })
            ->when(request('period') === 'running', function ($query) use ($now) {
                $query->where('start_at', '<=', $now)
                    ->where(fn ($q) => $q->whereNull('end_at')->orWhere('end_at', '>=', $now));
            })
            ->when(request('period') === 'scheduled', fn ($query) => $query->where('start_at', '>', $now))
            ->when(request('period') === 'ended', fn ($query) => $query->where('end_at', '<', $now))
            ->orderByDesc('start_at')
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function find(int $id): Promo
    {
        return Promo::query()
            ->where('entity_id', $this->entityId())
            ->with([
                'ownerLocation:id,name',
                'promoRule.promoRuleCustomerCategories',
                'promoReward',
            ])
            ->findOrFail($id);
    }

    public function store(array $data): Promo
    {
        return DB::transaction(
            fn () => (new PromoCreator(auth()->user()->entity, $this->toParams($data, true)))->create()
        );
    }

    public function update(Promo $promo, array $data): Promo
    {
        return DB::transaction(
            fn () => (new PromoUpdater(auth()->user()->entity, $promo, $this->toParams($data, false)))->create()
        );
    }

    public function getLocationOptions(): Collection
    {
        return Location::query()
            ->where('entity_id', $this->entityId())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Location $location) => [
                'id' => $location->id,
                'name' => Str::title(Str::lower($location->name)),
            ]);
    }

    public function getCustomerCategoryOptions(): Collection
    {
        return CustomerCategory::query()
            ->where('entity_id', $this->entityId())
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Susun parameter seperti yang dikirim backoffice lama (pos-secaca) ke PromoCreator.
     */
    private function toParams(array $data, bool $creating): array
    {
        $userId = auth()->id();
        $startAt = Carbon::parse($data['start_at'])->startOfDay();
        $isPercentage = $data['promo_reward']['template'] === 'discount_percentage';
        $minimumSales = $data['promo_rule']['minimum_sales_purchase'] ?? null;
        $customerCategoryIds = $data['promo_rule']['customer_category_ids'] ?? [];

        $audit = ['updated_by' => $userId] + ($creating ? ['created_by' => $userId] : []);

        return $audit + [
            'owner_location_id' => $data['owner_location_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'channel' => 'pos',
            'goal' => 'increase_sales',
            'start_at' => $startAt,
            'end_at' => ! empty($data['end_at']) ? Carbon::parse($data['end_at'])->endOfDay() : null,
            // Kolom status wajib diisi; kasir sendiri memakai start_at/end_at.
            'status' => $startAt->isFuture() ? PromoStatusEnum::Scheduled : PromoStatusEnum::ACTIVE,
            'auto_apply' => true,
            'combine_promo' => true,
            'promo_rule' => $audit + [
                'minimum_sales_purchase' => $minimumSales,
                'customer_only' => count($customerCategoryIds) > 0,
                'customer_category' => count($customerCategoryIds) > 0,
                'customer_category_ids' => $customerCategoryIds,
                'product_buy_condition' => 'or',
                'product_category_buy_condition' => 'or',
            ],
            'promo_reward' => $audit + [
                'template' => $data['promo_reward']['template'],
                'applied_to' => 'total_order',
                'percentage' => $isPercentage,
                'reward_amount' => $data['promo_reward']['reward_amount'],
                'reward_maximum_amount' => $isPercentage
                    ? ($data['promo_reward']['reward_maximum_amount'] ?? null)
                    : null,
                'in_house_percentage' => 100,
            ],
        ];
    }

    private function entityId(): ?int
    {
        return auth()->user()?->entity?->id;
    }
}
