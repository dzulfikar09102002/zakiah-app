<?php

namespace App\Http\Controllers;

use App\Helpers\Services\Product\ProductCreatorServices;
use App\Helpers\Services\Product\ProductTransformerFromRequestForUpdateServices;
use App\Helpers\Services\Product\ProductTransformerFromRequestServices;
use App\Helpers\Services\Product\ProductUpdaterImportServices;
use App\Helpers\Services\Product\ProductUpdaterServices;
use App\Http\Requests\BaseRequest;
use App\Http\Requests\ImportProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Jobs\ImportProductLogJob;
use App\Models\Product;
use App\Services\ProductService;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
    public function importPage()
    {
        $categoryOptions = $this->service->getCategoryOptions();
        $locationOptions = $this->service->getLocationOptions();
        $unitOptions = $this->service->getProuductUnitOptions();

        return Inertia::render('products/import', compact('categoryOptions', 'locationOptions', 'unitOptions'));
    }
    public function importStockLookup(Request $request)
    {
        $skus = $request->input('skus', []);

        return response()->json(
            $this->service->getCurrentStockMap($skus)
        );
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

            $processedProducts = [];

            foreach ($request->validated('products') as $productData) {

                $storeRequest = StoreProductRequest::createFrom($request);

                $storeRequest->replace($productData);

                $storeRequest->merge([
                    'employee' => $employee,
                    'entity' => $entity,
                    'for_import' => true,
                ]);

                $storeRequest->setRedirector(redirect());

                $storeRequest->setContainer(app())->validateResolved();

                $transforming = new ProductTransformerFromRequestServices($storeRequest);
                $creatorRequest = $transforming->transform();

                $product = Product::query()
                    ->where('entity_id', $entity->id)
                    ->where('sku', $storeRequest->validated('sku'))
                    ->first();

                $isNew = ! $product;

                if ($product) {
                    (new ProductUpdaterImportServices(
                        $creatorRequest,
                        $product
                    ))->update();
                } else {
                    (new ProductCreatorServices(
                        $creatorRequest
                    ))->create();
                }

                $processedProducts[] = array_merge($productData, [
                    '_import_created' => $isNew,
                ]);
            }

            DB::commit();

        } catch (Exception $e) {

            DB::rollBack();

            Log::error('Error saat mengimpor produk: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            throw $e;
        }
        try {
            ImportProductLogJob::dispatch(
                $entity->id,
                $employee->id,
                $request->user()->id,  
                $processedProducts       
            )->afterCommit()->afterResponse();
        } catch (Exception $e) {
            Log::error('Gagal dispatch ImportProductLogJob (produk tetap tersimpan): '.$e->getMessage(), [
                'entity_id' => $entity->id,
                'employee_id' => $employee->id,
                'exception' => $e,
            ]);
        }

        $count = count($request->validated('products'));

        return to_route('products.index')
            ->with('success', "{$count} produk berhasil diimpor.");
    }
}
