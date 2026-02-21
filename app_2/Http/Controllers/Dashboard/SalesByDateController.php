<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\IndexSalesByDateRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\SaleTransaction;
use Illuminate\Support\Facades\DB;

class SalesByDateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexSalesByDateRequest $request)
    {
        //
        $params = $request->validated();
        $location_ids = $request->entity->locations()->pluck('id')->toArray();

        $datas = SaleTransaction::select(
                DB::raw('sum(net_sales_after_tax) as net_sales_after_tax'),
                DB::raw('sum(net_profit) as net_profit'),
                DB::raw("DATE_FORMAT(local_sales_at, '%Y-%m-%d') as local_sales_date")
            )
            ->where('status', 'ok')
            ->whereIn('location_id', $location_ids)
            ->where('sales_at', '>=', $params['start_at'])
            ->where('sales_at', '<=', $params['end_at'])
            ->groupBy('local_sales_date')
            ->orderBy('local_sales_date')
            ->limit(30);

        if ($params['select_all_location'] == 'true' && array_key_exists('exclude_locs', $params)) {
            $datas->whereNotIn('location_id', $params['exclude_locs']);
        } else if ($params['select_all_location'] == 'false' && array_key_exists('locs', $params)) {
            $datas->whereIn('location_id', $params['locs']);
        }

        $response = new BaseJsonResponse($datas->get());
        return $response->response();
    }
}
