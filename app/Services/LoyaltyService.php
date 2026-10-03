<?php

namespace App\Services;

use App\Helpers\UniqueCodeGenerator;
use App\Models\Loyalty;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Logika mengikuti LoyaltyController di backend lama (zakiah-backend):
 * hanya satu loyalty aktif per entity, loyalty aktif tidak boleh dihapus.
 */
class LoyaltyService
{
    private const LOYALTY_FIELDS = [
        'name',
        'description',
        'miniminal_transaction_value',
        'reward_point',
        'allow_multiple',
    ];

    public function getLoyalties(): LengthAwarePaginator
    {
        $search = request('search', '');

        return Loyalty::query()
            ->where('entity_id', $this->entityId())
            ->withCount('rewardProducts')
            ->when($search, fn ($query) => $query->whereLike('name', "%{$search}%"))
            ->orderByRaw("case status when 'active' then 0 when 'in_active' then 1 else 2 end")
            ->orderByDesc('updated_at')
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function find(int $id): Loyalty
    {
        return Loyalty::query()
            ->where('entity_id', $this->entityId())
            ->with([
                'rewardProducts.product:id,name,sku,barcode,sell_price',
                'rewardProducts.productUnit:id,name',
            ])
            ->findOrFail($id);
    }

    public function store(array $data): Loyalty
    {
        return DB::transaction(function () use ($data) {
            $user = auth()->user();

            $loyalty = new Loyalty(Arr::only($data, self::LOYALTY_FIELDS));
            $loyalty->entity_id = $this->entityId();
            $loyalty->code = UniqueCodeGenerator::generateCode();
            $loyalty->status = 'active';
            $loyalty->created_by = $user->id;
            $loyalty->updated_by = $user->id;
            $loyalty->save();

            $this->syncRewardProducts($loyalty, $data['reward_products'] ?? []);
            $this->deactivateOthers($loyalty);

            return $loyalty;
        });
    }

    public function update(Loyalty $loyalty, array $data): Loyalty
    {
        return DB::transaction(function () use ($loyalty, $data) {
            $loyalty->fill(Arr::only($data, self::LOYALTY_FIELDS));
            $loyalty->updated_by = auth()->id();
            $loyalty->save();

            $this->syncRewardProducts($loyalty, $data['reward_products'] ?? []);

            return $loyalty;
        });
    }

    public function destroy(Loyalty $loyalty): void
    {
        if ($loyalty->status === 'active') {
            throw ValidationException::withMessages([
                'loyalty' => 'Loyalty yang aktif tidak dapat dihapus. Nonaktifkan terlebih dahulu.',
            ]);
        }

        DB::transaction(function () use ($loyalty) {
            $loyalty->rewardProducts()->delete();
            $loyalty->delete();
        });
    }

    public function activate(Loyalty $loyalty): void
    {
        DB::transaction(function () use ($loyalty) {
            $this->setStatus($loyalty, 'active');
            $this->deactivateOthers($loyalty);
        });
    }

    public function deactivate(Loyalty $loyalty): void
    {
        $this->setStatus($loyalty, 'in_active');
    }

    public function archive(Loyalty $loyalty): void
    {
        $this->setStatus($loyalty, 'archived');
    }

    /**
     * Baris dengan id di-update, tanpa id dibuat baru, dan yang tidak dikirim lagi dihapus
     * (setara flag `_destroy` di backend lama).
     */
    private function syncRewardProducts(Loyalty $loyalty, array $rows): void
    {
        $userId = auth()->id();
        $keptIds = [];

        foreach ($rows as $row) {
            $attributes = [
                'product_id' => $row['product_id'],
                'product_unit_id' => $row['product_unit_id'],
                'point_needed' => $row['point_needed'],
                'maximum_quantity' => $row['maximum_quantity'] ?? null,
                'updated_by' => $userId,
            ];

            $existing = ! empty($row['id'])
                ? $loyalty->rewardProducts()->find($row['id'])
                : null;

            if ($existing) {
                $existing->update($attributes);
                $keptIds[] = $existing->id;

                continue;
            }

            $created = $loyalty->rewardProducts()->create($attributes + [
                'entity_id' => $loyalty->entity_id,
                'created_by' => $userId,
            ]);
            $keptIds[] = $created->id;
        }

        $loyalty->rewardProducts()->whereNotIn('id', $keptIds)->delete();
    }

    private function setStatus(Loyalty $loyalty, string $status): void
    {
        $loyalty->status = $status;
        $loyalty->updated_by = auth()->id();
        $loyalty->save();
    }

    private function deactivateOthers(Loyalty $loyalty): void
    {
        Loyalty::query()
            ->where('entity_id', $loyalty->entity_id)
            ->where('id', '!=', $loyalty->id)
            ->where('status', 'active')
            ->update(['status' => 'in_active', 'updated_by' => auth()->id()]);
    }

    private function entityId(): ?int
    {
        return auth()->user()?->entity?->id;
    }
}
