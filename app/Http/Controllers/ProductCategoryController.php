<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductCategoryRequest;
use App\Http\Requests\UpdateProductCategoryRequest;
use App\Http\Services\ProductCategoryService;
use App\Models\ProductCategory;
use Inertia\Inertia;

class ProductCategoryController extends Controller
{
    public function __construct(
        private ProductCategoryService $service
    ) {}

    public function index()
    {
        $categories = $this->service->paginateByEntity();

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
