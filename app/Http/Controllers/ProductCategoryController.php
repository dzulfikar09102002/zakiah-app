<?php

namespace App\Http\Controllers;

use App\Http\Services\ProductCategoryService;
use App\Models\ProductCategory;
use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use Inertia\Inertia;

class ProductCategoryController extends Controller
{
    public function __construct(
        private ProductCategoryService $service
    ) {}

    public function index()
    {
        $perPage = request('per_page', 10);
        $entityId = auth()->user()?->entity?->id;

        $categories = $this->service->paginateByEntity($entityId, $perPage);

        return Inertia::render('categories/index', compact('categories'));
    }

    public function store(StoreProductCategoryRequest $request)
    {
        $entityId = auth()->user()?->entity?->id;

        $this->service->store(
            $request->validated(),
            $entityId,
            auth()->id()
        );

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan');
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $this->service->update(
            $productCategory,
            $request->validated(),
            auth()->id()
        );

        return redirect()->back()->with('success', 'Kategori berhasil diperbarui');
    }

    public function destroy(ProductCategory $productCategory)
    {
        $this->service->delete($productCategory);

        return redirect()->back()->with('success', 'Kategori berhasil dihapus');
    }
}
