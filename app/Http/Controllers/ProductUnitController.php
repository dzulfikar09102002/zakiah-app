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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductUnitRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductUnit $productUnit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductUnit $productUnit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductUnitRequest $request, ProductUnit $productUnit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductUnit $productUnit)
    {
        //
    }
}
