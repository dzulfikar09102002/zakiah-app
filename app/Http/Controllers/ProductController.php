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
        $locationOptions = $this->service->getLocationOptions();
        $unitOptions = $this->service->getProuductUnitOptions();

        return Inertia::render('products/index', compact('pagination', 'categoryOptions', 'locationOptions', 'unitOptions'));
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

        Inertia::flash(key: 'success', value: 'Produk baru berhasil ditambahkan');

        return back();
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        if ($request->entity->id != $product->entity_id) {
            Inertia::flash(key: 'error', value: 'Entitas tidak valid');
            return back();
        }

        # start transcation
        DB::transaction(function () use ($request, $product) {
            $transforming = new ProductTransformerFromRequestForUpdateServices($request, $product);
            (new ProductUpdaterServices($transforming->transform(), $product))->update();
        });

        Inertia::flash(key: 'success', value: 'Produk berhasil diperbarui');
        return back();
    }
}
