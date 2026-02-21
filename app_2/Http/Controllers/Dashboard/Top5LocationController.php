<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\IndexTop5ProductRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\SaleTransaction;
use Illuminate\Support\Facades\DB;

class Top5LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexTop5ProductRequest $request)
    {
        //
        $params = $request->validated();
        $location_ids = $request->entity->locations()->pluck('id')->toArray();

        $datas = SaleTransaction::select('location_name')
            ->select('location_name', DB::raw('sum(net_sales_after_tax) as net_sales_after_tax'))
            ->where('local_sales_at', '>=', $params['start_at'])
            ->where('local_sales_at', '<=', $params['end_at'])
            ->where('status', 'ok')
            ->whereIn('location_id', $location_ids)
            ->groupBy('location_name')
            ->orderByDesc('net_sales_after_tax')
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
