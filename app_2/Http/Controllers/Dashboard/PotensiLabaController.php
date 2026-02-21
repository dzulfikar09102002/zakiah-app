<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\IndexPotensiLabaRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\ProductLocationStock;
use Illuminate\Support\Facades\DB;

class PotensiLabaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexPotensiLabaRequest $request)
    {
        //
        $params = $request->validated();

        $location_ids = $request->entity->locations()->pluck('id')->toArray();

        $datas = ProductLocationStock::select(
            DB::raw('sum(product_location_stocks.stock) as stock'),
            DB::raw('sum(product_location_stocks.stock * CAST(products.last_buying_price as SIGNED)) as cogs'),
            DB::raw('sum(product_location_stocks.stock * CAST(products.sell_price as SIGNED)) as sell_price')
        )
        ->join('products', 'products.id', '=', 'product_location_stocks.product_id')
        ->whereIn('product_location_stocks.location_id', $location_ids);

        if ($params['select_all_location'] == 'true' && array_key_exists('exclude_locs', $params)) {
            $datas->whereNotIn('product_location_stocks.location_id', $params['exclude_locs']);
        } else if ($params['select_all_location'] == 'false' && array_key_exists('locs', $params)) {
            $datas->whereIn('product_location_stocks.location_id', $params['locs']);
        }

        $response = new BaseJsonResponse($datas->get());
        return $response->response();
    }
}
