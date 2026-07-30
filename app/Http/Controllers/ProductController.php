<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Helpers\Services\Product\ProductCreatorImportServices;
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
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Throwable;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $service
    ) {
    }

    public function index()
    {
        try {
            $pagination = $this->service->getProducts();
            $categoryOptions = $this->service->getCategoryOptions();
            $locationOptions = $this->service->getLocationOptions();
            $unitOptions = $this->service->getProuductUnitOptions();
            $suppliers = $this->service->getSuppliersName();

            return Inertia::render('products/index', compact(
                'pagination',
                'categoryOptions',
                'locationOptions',
                'unitOptions',
                'suppliers'
            ));
        } catch (Throwable $e) {
            Helper::logException($e, [
                'source' => self::class,
                'method' => __FUNCTION__,
            ]);

            throw $e;
        }
    }
    public function importPage()
    {
        try {
            $categoryOptions = $this->service->getCategoryOptions();
            $locationOptions = $this->service->getLocationOptions();
            $unitOptions = $this->service->getProuductUnitOptions();
            $suppliers = $this->service->getSuppliersName();

            return Inertia::render('products/import', compact(
                'categoryOptions',
                'locationOptions',
                'unitOptions',
                'suppliers'
            ));
        } catch (Exception $e) {
            Helper::logException($e);
            throw $e;
        }
    }
    public function importStockLookup(Request $request)
    {
        try {
            $skus = $request->input('skus', []);

            return response()->json(
                $this->service->getCurrentStockMap($skus)
            );
        } 
        catch (Exception $e) {
            Helper::logException($e, [
                'skus' => $request->input('skus'),
            ]);

            throw $e;
        }
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
        } catch (Throwable $e) {
            DB::rollBack();

            Helper::logException($e, [
                'source' => self::class,
                'method' => __FUNCTION__,
                'request' => $request->except(['image']),
            ]);

            throw $e;
        }

        Inertia::flash(key: 'success', value: 'Produk baru berhasil ditambahkan');

        return back();
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        if ($request->entity->id != $product->entity_id) {
            Inertia::flash('error', 'Entitas tidak valid');
            return back();
        }

        try {
            DB::transaction(function () use ($request, $product) {
                $transforming = new ProductTransformerFromRequestForUpdateServices($request, $product);

                (new ProductUpdaterServices(
                    $transforming->transform(),
                    $product
                ))->update();
            });

            Inertia::flash('success', 'Produk berhasil diperbarui');

            return back();

        } catch (Exception $e) {
            Helper::logException($e, [
                'product_id' => $product->id,
            ]);

            throw $e;
        }
    }

    public function destroy(Product $product)
    {
        if ($product->entity_id != request()->entity->id) {
            Inertia::flash('error', 'Entitas tidak valid');
            return back();
        }

        try {
            $product->delete();

            Inertia::flash('success', 'Produk berhasil dihapus');

            return back();

        } catch (Exception $e) {
            Helper::logException($e, [
                'product_id' => $product->id,
            ]);

            throw $e;
        }
    }

public function import(ImportProductRequest $request)
{
    set_time_limit(300);
    ini_set('memory_limit', '512M');

    $employee = $request->employee;
    $entity   = $request->entity;
    $products = $request->validated('products');

    // 1. LEPAS LOCK SESSION PHP!
    // Supaya Inertia / Ajax di frontend gak ketahan/hanging di server
    session_write_close();

    DB::beginTransaction();

    try {
        $processedProducts = [];

        // 2. Query SKU sekaligus (1 Query saja untuk 118 data, bukan 118 Query!)
        $skus = array_column($products, 'sku');
        $existingProducts = Product::query()
            ->where('entity_id', $entity->id)
            ->whereIn('sku', $skus)
            ->get()
            ->keyBy('sku');
foreach ($products as $productData) {

    // 1. Buat StoreProductRequest dari $request awal (User & Auth state bawaan ter-copy sempurna!)
    $storeRequest = StoreProductRequest::createFrom($request);

    // 2. Timpa input data khusus untuk item produk saat ini
    $mergedData = array_merge($productData, [
        'employee'   => $employee,
        'entity'     => $entity,
        'for_import' => true,
    ]);

    $storeRequest->replace($mergedData);

    // 3. Set data yang dianggap "validated" tanpa menjalankan validateResolved() / query DB ulang
    $storeRequest->setValidator(
        validator($mergedData, $storeRequest->rules())
    );

    // 4. Transformasi data (User/createdBy & data validasi dijamin terisi penuh!)
    $transforming   = new ProductTransformerFromRequestServices($storeRequest);
    $creatorRequest = $transforming->transform();

    $product = $existingProducts->get($productData['sku']);
    $isNew   = ! $product;

    if ($product) {
        (new ProductUpdaterImportServices($creatorRequest, $product))->update();
    } else {
        (new ProductCreatorImportServices($creatorRequest))->create();
    }

    $processedProducts[] = array_merge($productData, [
        '_import_created' => $isNew,
    ]);
}

        DB::commit();

    } catch (Throwable $e) {
        DB::rollBack();

        Helper::logException($e, [
            'source'      => self::class,
            'method'      => __FUNCTION__,
            'entity_id'   => $entity?->id,
            'employee_id' => $employee?->id,
            'user_id'     => $request->user()?->id,
        ]);

        throw $e;
    }

    // Log job jalan di background
    ImportProductLogJob::dispatch(
        $entity->id,
        $employee->id,
        $request->user()->id,
        $processedProducts
    );

    // 4. Buka kembali Session untuk flash data ke Inertia
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $count = count($products);

    // Kirim balik respon redirect ke Inertia
    return redirect()
        ->back()
        ->with('success', "{$count} produk berhasil diimpor.");
}
}
