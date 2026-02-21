<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\IndexSalesRefundSummaryRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\SaleRefund;
use Illuminate\Support\Facades\DB;

class SalesRefundSummaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexSalesRefundSummaryRequest $request)
    {
        //
        $params = $request->validated();
        $location_ids = $request->entity->locations()->pluck('id')->toArray();

        $datas = SaleRefund::select(
                DB::raw('sum(net_sales_after_tax) as net_sales_after_tax'),
            )
            ->whereIn('location_id', $location_ids)
            ->where('sales_at', '>=', $params['start_at'])
            ->where('sales_at', '<=', $params['end_at']);

        if ($params['select_all_location'] == 'true' && array_key_exists('exclude_locs', $params)) {
            $datas->whereNotIn('location_id', $params['exclude_locs']);
        } else if ($params['select_all_location'] == 'false' && array_key_exists('locs', $params)) {
            $datas->whereIn('location_id', $params['locs']);
        }

        $response = new BaseJsonResponse($datas->get());
        return $response->response();
    }
}
