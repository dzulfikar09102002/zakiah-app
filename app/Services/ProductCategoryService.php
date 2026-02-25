<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Models\ProductCategory;
use Illuminate\Support\Str;

class ProductCategoryService
{
    public function getCategories()
    {
        $search = request('search', '');

        return ProductCategory::where('entity_id', auth()->user()?->entity?->id)
            ->whereLike('name', "%$search%")
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function getDeletedCategories()
    {
        $search = request('search', '');

        return ProductCategory::onlyTrashed()->where('entity_id', auth()->user()?->entity?->id)
            ->whereLike('name', "%$search%")
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function store(string $name)
    {
        $user = auth()->user();

        return ProductCategory::create([
            'name' => $name,
            'search_name' => Str::lower($name),
            'status' => StatusEnum::Active,
            'entity_id' => $user->entity?->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function update(ProductCategory $category, string $name)
    {
        return $category->update([
            'name' => $name,
            'search_name' => Str::lower($name),
            'updated_by' => auth()->user()->id,
        ]);
    }

    public function delete(ProductCategory $category)
    {
        return $category->delete();
    }

    public function restore(int $id)
    {
        $category = ProductCategory::withTrashed()->findOrFail($id);

        return $category->restore();
    }
}
