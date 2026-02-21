<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\IndexTop5ProductRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\SaleTransactionDetail;
use Illuminate\Support\Facades\DB;

class Top5ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexTop5ProductRequest $request)
    {
        //
        $params = $request->validated();
        $location_ids = $request->entity->locations()->pluck('id')->toArray();

        $datas = SaleTransactionDetail::select('product_category_id')
            ->select('product_category_name', DB::raw('sum(total_line_amount) as total_line_amount'), DB::raw('sum(quantity) as quantity'))
            ->where('local_sales_at', '>=', $params['start_at'])
            ->where('local_sales_at', '<=', $params['end_at'])
            ->where('status', 'ok')
            ->whereIn('location_id', $location_ids)
            ->groupBy('product_category_id', 'product_category_name')
            ->orderByDesc('total_line_amount')
            ->orderByDesc('quantity')
            ->limit(5);

        if ($params['select_all_location'] == 'true' && array_key_exists('exclude_locs', $params)) {
            $datas->whereNotIn('location_id', $params['exclude_locs']);
        } else if ($params['select_all_location'] == 'false' && array_key_exists('locs', $params)) {
            $datas->whereIn('location_id', $params['locs']);
        }

        $response = new BaseJsonResponse($datas->get());
        return $response->response();
    }
}
