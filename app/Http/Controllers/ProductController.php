<?php

namespace App\Http\Controllers;

use App\Helpers\Services\Product\ProductCreatorServices;
use App\Helpers\Services\Product\ProductTransformerFromRequestForUpdateServices;
use App\Helpers\Services\Product\ProductTransformerFromRequestServices;
use App\Helpers\Services\Product\ProductUpdaterServices;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\ImportProductRequest;
use App\Http\Requests\StoreProductImportRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Product;
use App\Models\ProductLocationStock;
use App\Models\ProductStockMovement;
use App\Services\ProductService;
use DB;
use Exception;
use Illuminate\Http\Request;
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

    public function destroy(Product $product)
    {
        if ($product->entity_id != request()->entity->id) {
            Inertia::flash(key: 'error', value: 'Entitas tidak valid');
            return back();
        }

        $product->delete();

        Inertia::flash(key: 'success', value: 'Produk berhasil dihapus');
        return back();
    }

    public function import(ImportProductRequest $request)
    {
        $employee = $request->employee;
        $entity = $request->entity;
        DB::beginTransaction();

        try {
            foreach ($request->validated('products') as $productData) {

            $storeRequest = StoreProductImportRequest::createFrom($request);

            $storeRequest->replace($productData);

            $storeRequest->merge([
                'employee' => $employee,
                'entity' => $entity,
            ]);

            $storeRequest->setContainer(app())->validateResolved();

            $product = Product::where('sku', $productData['sku'])
                ->orWhere('barcode', $productData['barcode'])
                ->first();
            if ($product) {

                $product->update([
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'sell_price' => $productData['sell_price'],
                    'last_buying_price' => $productData['last_buying_price'],
                    'product_category_id' => $productData['product_category_id'],
                    'child_product_category_id' => $productData['child_product_category_id'] ?? null,
                    'product_unit_id' => $productData['product_unit_id'],
                    'product_sell_unit_id' => $productData['product_sell_unit_id'],
                    'updated_by' => $employee->id,
                ]);

                foreach ($productData['stock_movements'] as $movement) {

                    $stock = ProductLocationStock::firstOrNew([
                        'product_id' => $product->id,
                        'location_id' => $movement['location_id'],
                        'product_unit_id' => $productData['product_unit_id'],
                    ]);

                    $currentStock = $stock->stock ?? 0;

                    $stock->stock = $currentStock + $movement['stock'];

                    $stock->save();

                    ProductStockMovement::create([
                        'product_id' => $product->id,
                        'location_id' => $movement['location_id'],
                        'product_unit_id' => $productData['product_unit_id'],
                        'original_product_unit_id' => $productData['product_unit_id'],
                        'resource_id' => $product->id,
                        'resource_type' => Product::class,
                        'original_stock_out' => $currentStock,
                        'original_stock_in' => $movement['stock'],
                        'original_buying_price' => $movement['buying_price'],
                        'conversion_stock' => 1,
                        'stock_in' => $movement['stock'],
                        'stock_out' => $currentStock,
                        'buying_price' => $movement['buying_price'],
                    ]);
                }

            }
            else {

                $transforming = new ProductTransformerFromRequestServices($storeRequest);

                $creator = new ProductCreatorServices(
                    $transforming->transform()
                );
                $creator->create();
            }
        }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }

        $count = count($request->validated('products'));

        Inertia::flash(key: 'success', value: "{$count} produk berhasil diimpor.");

        return back();
    }
}
