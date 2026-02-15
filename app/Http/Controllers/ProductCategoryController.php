<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Models\ProductCategory;
use App\Services\ProductCategoryService;
use Inertia\Inertia;

class ProductCategoryController extends Controller
{
    public function __construct(
        private ProductCategoryService $service
    ) {}

    public function index()
    {
        $pagination = $this->service->getCategories();

        return Inertia::render('categories/index', compact('pagination'));
    }

    public function store(StoreProductCategoryRequest $request)
    {
        $this->service->store($request->validated()['name']);

        return to_route('categories.index')->with('success', 'Kategori berhasil ditambahkan');
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $this->service->update($productCategory, $request->validated()['name']);

        return to_route('categories.index')->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy(ProductCategory $productCategory)
    {
        $this->service->delete($productCategory);

        return to_route('categories.index')->with('success', 'Kategori berhasil dihapus');
    }

    public function restore(int $id)
    {
        $this->service->restore($id);

        return to_route('categories.index')->with('success', 'Kategori berhasil dipulihkan');
    }
}
