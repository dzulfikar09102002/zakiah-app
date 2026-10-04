<?php

namespace App\Services;

use App\Models\LegalEntityLocation;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LegalEntityLocationService
{
    /**
     * Daftar lokasi beserta CV yang terdaftar di masing-masing lokasi.
     */
    public function getLocations(): LengthAwarePaginator
    {
        $search = request('search', '');

        return Location::query()
            ->where('entity_id', auth()->user()?->entity?->id)
            ->select(['id', 'name', 'kind', 'city'])
            ->with([
                'legalEntityLocations' => fn ($query) => $query
                    ->whereHas('legalEntity')
                    ->with('legalEntity:id,name,initial,legal_type,npwp,is_pkp,status')
                    ->orderBy('id'),
            ])
            ->when($search, function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('legalEntityLocations.legalEntity', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('name')
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    /**
     * Baris lama yang ter-soft-delete dipulihkan karena index unique
     * (legal_entity_id, location_id) tidak mengabaikan deleted_at.
     */
    public function store(array $data): void
    {
        DB::transaction(function () use ($data) {
            foreach ($data['legal_entity_ids'] as $legalEntityId) {
                $legalEntityLocation = LegalEntityLocation::withTrashed()->firstOrNew([
                    'legal_entity_id' => $legalEntityId,
                    'location_id' => $data['location_id'],
                ]);

                $legalEntityLocation->deleted_at = null;
                $legalEntityLocation->save();
            }
        });
    }

    public function delete(LegalEntityLocation $legalEntityLocation): void
    {
        $legalEntityLocation->delete();
    }

    public function ensureBelongsToEntity(LegalEntityLocation $legalEntityLocation): void
    {
        abort_unless(
            $legalEntityLocation->location()->where('entity_id', auth()->user()?->entity?->id)->exists(),
            404,
        );
    }
}
