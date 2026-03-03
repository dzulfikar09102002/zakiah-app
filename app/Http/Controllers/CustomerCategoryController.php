<?php

namespace App\Http\Controllers;
use App\Http\Requests\StoreCustomerCategoryRequest;
use App\Http\Requests\UpdateCustomerCategoryRequest;
use App\Models\CustomerCategory;
use App\Services\CustomerCategoryService;
use Inertia\Inertia;

class CustomerCategoryController extends Controller
{
    public function __construct(
            private CustomerCategoryService $service
    ) {}
    public function index()
    {
        $pagination = $this->service->getCustomerCategories();
        return Inertia::render("customers/categories", compact("pagination"));
    }

    public function store(StoreCustomerCategoryRequest $request)
    {
        $this->service->store($request->validated());
        return to_route('customer-categories.index')->with('success', 'Kategori pelanggan berhasil ditambahkan');
    }

    public function update(UpdateCustomerCategoryRequest $request, CustomerCategory $customerCategory)
    {
        $this->service->update($customerCategory, $request->validated());
        return to_route('customer-categories.index')->with('success', 'Kategori pelanggan berhasil diperbarui');
    }
    
    public function destroy(CustomerCategory $customerCategory)
    {
        $this->service->delete($customerCategory);
        return to_route('customer-categories.index')->with('success', 'Kategori pelanggan berhasil dihapus');
    }

    public function deleted()
    {
        $onlyTrashed = true;
        $pagination = $this->service->getDeleted();

        return Inertia::render("customers/categories", compact('pagination', 'onlyTrashed'));
    }

    public function restore(int $id)
    {
        $this->service->restore($id);
        return to_route('customer-categories.index')->with('success', 'Kategori pelanggan berhasil dipulihkan');
    }
}
