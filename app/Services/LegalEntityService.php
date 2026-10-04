<?php

namespace App\Services;

use App\Helpers\UniqueCodeGenerator;
use App\Models\LegalEntity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LegalEntityService
{
    public function getLegalEntities(bool $onlyTrashed = false): LengthAwarePaginator
    {
        $search = request('search', '');

        return LegalEntity::query()
            ->where('entity_id', auth()->user()?->entity?->id)
            ->when($onlyTrashed, fn (Builder $query) => $query->onlyTrashed())
            ->with('locations:id,name')
            ->when($search, function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('initial', 'like', "%{$search}%")
                        ->orWhere('npwp', 'like', "%{$search}%");
                });
            })
            ->when(request('statuses'), fn (Builder $query, $statuses) => $query->whereIn('status', (array) $statuses))
            ->orderBy('name')
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function getLegalEntityOptions(): Collection
    {
        return LegalEntity::query()
            ->where('entity_id', auth()->user()?->entity?->id)
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'initial', 'legal_type'])
            ->map(fn (LegalEntity $legalEntity) => [
                'label' => "{$legalEntity->name} ({$legalEntity->initial})",
                'value' => $legalEntity->id,
            ]);
    }

    public function store(array $data): LegalEntity
    {
        return DB::transaction(function () use ($data) {
            $legalEntity = new LegalEntity;

            $legalEntity->fill($data);
            $legalEntity->status = $data['status'] ?? 'active';
            $legalEntity->entity_id = auth()->user()->entity->id;
            $legalEntity->code = UniqueCodeGenerator::generateCode();
            $legalEntity->initial = $data['initial'] ?? strtoupper(UniqueCodeGenerator::generateInitial($data['name']));
            $legalEntity->save();

            return $legalEntity;
        });
    }

    public function update(array $data, LegalEntity $legalEntity): LegalEntity
    {
        return DB::transaction(function () use ($data, $legalEntity) {
            $legalEntity->fill($data);
            $legalEntity->initial = $data['initial'] ?? $legalEntity->getOriginal('initial');
            $legalEntity->status = $data['status'] ?? $legalEntity->getOriginal('status');
            $legalEntity->save();

            return $legalEntity;
        });
    }

    /**
     * Relasi ke lokasi ikut dihapus supaya CV yang terhapus tidak lagi
     * dipakai sebagai CV toko.
     */
    public function delete(LegalEntity $legalEntity): void
    {
        DB::transaction(function () use ($legalEntity) {
            $legalEntity->legalEntityLocations()->get()->each->delete();
            $legalEntity->delete();
        });
    }

    public function restore(int $id): bool
    {
        return LegalEntity::onlyTrashed()
            ->where('entity_id', auth()->user()?->entity?->id)
            ->findOrFail($id)
            ->restore();
    }
}
