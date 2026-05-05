<?php

namespace App\Http\Controllers;

use App\Helpers\Services\Product\ProductCreatorServices;
use App\Helpers\Services\Product\ProductTransformerFromRequestForUpdateServices;
use App\Helpers\Services\Product\ProductTransformerFromRequestServices;
use App\Helpers\Services\Product\ProductUpdaterServices;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Product;
use App\Services\ProductService;
use DB;
use Exception;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $service
    ) {
    }

    public function index()
    {
        $pagination = $this->service->getProducts();
        $categoryOptions = $this->service->getCategoryOptions();
        $locations = $this->service->getLocationOptions();

        return Inertia::render('products/index', compact('pagination', 'categoryOptions', 'locations'));
    }

    public function store(StoreProductRequest $request)
    {
        # start transcation
        DB::beginTransaction();
        try {
            $transforming = new ProductTransformerFromRequestServices($request);
            $creator = new ProductCreatorServices($transforming->transform());
            $data = $creator->create();

            DB::commit();
        } catch (Exception $e) {
            dd($e);
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        return to_route('products.index')->with('success', value: 'Produk baru berhasil ditambahkan');
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        if ($request->entity->id != $product->entity_id) {
            return to_route('products.index')->with('error', value: 'Entitas tidak valid');
        }

        # start transcation
        DB::transaction(function () use ($request, $product) {
            $transforming = new ProductTransformerFromRequestForUpdateServices($request, $product);
            (new ProductUpdaterServices($transforming->transform(), $product))->update();
        });

        return to_route('products.index')->with('success', value: 'Produk berhasil diperbarui');
    }
}
