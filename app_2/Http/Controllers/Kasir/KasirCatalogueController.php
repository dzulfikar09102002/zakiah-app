<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\IndexKasirCatalogueRequest;
use App\Http\Requests\Kasir\SearchKasirCatalogueRequest;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\JoinClause;

class KasirCatalogueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexKasirCatalogueRequest $request)
    {
        //
        $params = $request->validated();

        $datas = ProductCategory::where('entity_id', $request->entity->id);

        return $datas->cursorPaginate($request->limit ?? 15)->appends($params);
    }

    public function productSearch(SearchKasirCatalogueRequest $request)
    {
        //
        $params = $request->validated();
        $locId = $params['location'];
        $orderTypeId = $request->order_type;

        $datas = Product::with([
            'productUnit:id,name',
            'productCategory:id,name',
            'productLocationStock' => function (HasOne $query) use ($locId, $orderTypeId) {
                $query
                    ->select(['product_id', 'stock', 'average_buy_price'])
                    ->where('location_id', $locId);
            },
            // 'productSellPrice' => function (HasOne $query) use ($locId, $orderTypeId) {
            //     $query
            //         ->select(['product_id', 'order_type_id','sell_price'])
            //         ->where('location_id', $locId);
            // },
            // 'productSellPrices' => function (HasMany $query) use ($locId, $orderTypeId) {
            //     $query
            //         ->select(['product_id', 'order_type_id','sell_price'])
            //         ->where('location_id', $locId);
            // }
        ])
        ->select(['products.id', 'name', 'code', 'image_url', 'sku', 'product_category_id', 'products.product_unit_id', 'sell_price', 'barcode'])
        ->active()
        ->where('entity_id', $request->entity->id);

        if (array_key_exists('filter_stock', $params) && $params['filter_stock'] == 'true') {
            $datas ->join('product_location_stocks', function (JoinClause $join) use($locId) {
                $join->on('products.id', '=', 'product_location_stocks.product_id')
                    ->where('product_location_stocks.stock', '>', 0)
                    ->where('product_location_stocks.location_Id', $locId);
            });
        }

        if (array_key_exists('keyword', $params)) {
            $keyword =  $params['keyword'];
            $keywordLike =  "%" . $keyword . "%";

            $datas->where(function (Builder $builder) use($keywordLike, $keyword) {
                $builder->where('name', 'like', $keywordLike)
                    ->orWhere('sku', $keyword)
                    ->orWhere('barcode', $keyword)
                    ->orWhere('code', $keyword);
            });
        }

        return $datas->cursorPaginate($request->limit ?? 12)->appends($params);
    }
}
