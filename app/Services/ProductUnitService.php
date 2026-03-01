<?php

namespace App\Services;
use App\Enums\StatusEnum;
use App\Models\ProductUnit;
use Illuminate\Support\Str;

class ProductUnitService
{
    public function getUnits()
    {
        $search = request('search', '');

        return ProductUnit::query()
            ->where('entity_id', auth()->user()?->entity?->id)

            ->when($search, fn ($query) =>
                $query->whereLike('name', "%{$search}%")
            )

            ->when(request('statuses'), fn ($query, $statuses) =>
                $query->whereIn('status', (array) $statuses)
            )

            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function getDeletedUnits()
    {
        $search = request('search', '');

        return ProductUnit::query()
            ->where('entity_id', auth()->user()?->entity?->id)
            ->onlyTrashed()

            ->when($search, fn ($query) =>
                $query->whereLike('name', "%{$search}%")
            )

            ->when(request('statuses'), fn ($query, $statuses) =>
                $query->whereIn('status', (array) $statuses)
            )

            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function store(string $name)
    {
        $user = auth()->user();

        return ProductUnit::create([
            'name' => $name,
            'search_name' => Str::lower($name),
            'status' => StatusEnum::Active,
            'entity_id' => $user->entity?->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function update(ProductUnit $units, string $name)
    {
        return $units->update([
            'name' => $name,
            'search_name' => Str::lower($name),
            'updated_by' => auth()->user()->id,
        ]);
    }

    public function delete(ProductUnit $unit)
    {
        return $unit->delete();
    }
    public function restore(int $id)
    {
        $category = ProductUnit::withTrashed()->findOrFail($id);

        return $category->restore();
    }
}