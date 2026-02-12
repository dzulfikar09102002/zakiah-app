<?php

namespace App\Http\Services;

use App\Models\ProductCategory;

class ProductCategoryService
{
    public function paginateByEntity(int $entityId, int $perPage = 10)
    {
        return ProductCategory::where('entity_id', $entityId)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function store(array $data, int $entityId, int $userId)
    {
        return ProductCategory::create([
            'name' => $data['name'],
            'entity_id' => $entityId,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function update(ProductCategory $category, array $data, int $userId)
    {
        return $category->update([
            'name' => $data['name'],
            'updated_by' => $userId,
        ]);
    }

    public function delete(ProductCategory $category)
    {
        return $category->delete();
    }
}
