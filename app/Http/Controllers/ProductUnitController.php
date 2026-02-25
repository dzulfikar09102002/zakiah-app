<?php

namespace App\Http\Controllers;

use App\Models\ProductUnit;
use App\Http\Requests\StoreProductUnitRequest;
use App\Http\Requests\UpdateProductUnitRequest;
use App\Services\ProductUnitService;
use Inertia\Inertia;

class ProductUnitController extends Controller
{
    public function __construct(
        private ProductUnitService $service
    ) {}
    public function index()
    {
        $pagination = $this->service->getUnits();
        return Inertia::render('units/index', compact('pagination'));
    }
    
    public function store(StoreProductUnitRequest $request)
    {
        $this->service->store($request->validated()['name']);

        return to_route('product-units.index')->with('success', value: 'Produk unit berhasil ditambahkan');
    }
 
    public function update(UpdateProductUnitRequest $request, ProductUnit $productUnit)
    {
        $this->service->update($productUnit, $request->validated()['name']);

        return to_route('product-units.index')->with('success', 'Produk unit berhasil diperbarui');
    }

    public function destroy(ProductUnit $productUnit)
    {
        dd($productUnit);
        $this->service->delete($productUnit);
        return to_route('product-units.index')->with('success', 'Produk unit berhasil dihapus');
    }

    public function deleted()
    {

    }
}
