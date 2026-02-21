<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Helpers\Services\Product\ProductCreatorServices;
use App\Helpers\Services\Product\ProductTransformerFromRequestForUpdateServices;
use App\Helpers\Services\Product\ProductTransformerFromRequestServices;
use App\Helpers\Services\Product\ProductUpdaterServices;
use App\Http\Requests\IndexProductRequest;
use App\Http\Requests\ShowProductRequest;
use App\Http\Requests\StockProductRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\Product;
use App\Models\ProductLocationStock;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexProductRequest $request)
    {
        $params = $request->validated();

        $datas = Product::with([
            'productUnit:id,name',
            'productSellUnit:id,name',
            'productCategory:id,name',
            'productLocationStocks',
            'location:id,name',
        ])->where('entity_id', $request->entity->id);

        if ($request->exists('statuses')) {
            $datas = $datas->whereIn('status', $request->statuses);
        }

        if ($request->exists('barcode_mode') && $request->barcode_mode == 'true') {
            $datas = $datas->where('barcode', $request->keyword);
        } else {
            $datas = $datas->where(function (Builder $query) use ($request) {
                $query->orWhere('name', 'like', "%" . $request->keyword . "%")
                    ->orWhere('sku', $request->keyword)
                    ->orWhere('barcode', $request->keyword);
            });
        }

        if ($request->exists('parent_only')) {
            $datas = $datas->whereNull('parent_variance_id');
        }

        if ($request->exists('flatten_variance')) {
            $datas = $datas->where('has_variance', false);
        }

        if ($request->exists('selectAllProductCategory') && $request->exists('productCategoryIds')) {
            $datas = $datas->whereIn('product_category_id', $request->productCategoryIds);
        }

        if ($request->exists('selectAllProductCategory') && $request->exists('excludeProductCategoryIds')) {
            $datas = $datas->whereNotIn('product_category_id', $request->excludeProductCategoryIds);
        }

        return $datas->paginate($request->limit)->appends($params);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $data = new Product();

        # start transcation
        DB::beginTransaction();
        try {
            $transforming = new ProductTransformerFromRequestServices($request);
            $creator = new ProductCreatorServices($transforming->transform());
            $data = $creator->create();

            DB::commit();
        } catch (Exception $e) {
            # should throw 500
            DB::rollBack();

            throw $e;
        }

        return $this->buildResponse($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowProductRequest $request, Product $product)
    {
        if ($request->entity->id != $product->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        return $this->buildResponse($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        if ($request->entity->id != $product->entity_id) {
            # TEJA check error message
            $response = new BaseJsonResponse(null, __('entity.invalid_entity'));
            return $response->response(422);
        }

        # start transcation
        DB::transaction(function () use ($request, $product) {
            $transforming = new ProductTransformerFromRequestForUpdateServices($request, $product);
            (new ProductUpdaterServices($transforming->transform(), $product))->update();
        });

        return $this->buildResponse($product);
    }

    public function stock(StockProductRequest $request)
    {
        $params = $request->validated();

        $stock = ProductLocationStock::where('location_id', $params['location'] ?? 0)
            ->where('product_id', $params['product'] ?? 0)
            ->where('product_unit_id', $params['product_unit'] ?? 0)
            ->first();

        $response = new BaseJsonResponse($stock);
        return $response->response();
    }

    public function dropdown(IndexProductRequest $request)
    {
        $params = $request->validated();

        $datas = Product::with([
            'productUnit:id,name',
            'productSellUnit:id,name',
            'productCategory:id,name',
        ])
            ->where('entity_id', $request->entity->id)
            ->where(function (Builder $query) use ($request) {
                $query->orWhere('name', 'like', "%" . $request->keyword . "%")
                    ->orWhere('sku', $request->keyword)
                    ->orWhere('barcode', $request->keyword);
            })
            ->select('id', 'name', 'barcode', 'sell_price', 'sku', 'product_unit_id', 'product_category_id', 'product_sell_unit_id');

        if ($request->exists('selected_ids')) {
            $datas = $datas->whereNotIn('id', $request->selected_ids);
        }

        return $datas->cursorPaginate($request->limit)->appends($params);
    }

    public function export(IndexProductRequest $request)
    {
        $datas = Product::with([
            'productUnit:id,name',
            'productSellUnit:id,name',
            'productCategory:id,name',
            'productLocationStocks',
            'location:id,name',
        ])->where('entity_id', $request->entity->id);

        if ($request->exists('statuses')) {
            $datas = $datas->whereIn('status', $request->statuses);
        }

        if ($request->exists('selectAllProductCategory') && $request->exists('productCategoryIds')) {
            $datas = $datas->whereIn('product_category_id', $request->productCategoryIds);
        }

        if ($request->exists('selectAllProductCategory') && $request->exists('excludeProductCategoryIds')) {
            $datas = $datas->whereNotIn('product_category_id', $request->excludeProductCategoryIds);
        }

        return Excel::download(new ProductsExport($datas->get()), 'product.csv', \Maatwebsite\Excel\Excel::CSV, [
            'Content-Type' => 'text/csv',
      ]);
    }

    private function buildResponse(Product $product)
    {
        $response = new BaseJsonResponse($product->load([
            'productUnit:id,name',
            'productSellUnit:id,name',
            'productCategory:id,name',
        ]));

        return $response->response();
    }
}
