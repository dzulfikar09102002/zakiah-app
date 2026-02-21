<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\IndexSalesAnnualSummaryRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\SaleTransaction;
use Illuminate\Support\Facades\DB;

class SalesAnnualSummaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexSalesAnnualSummaryRequest $request)
    {
        //
        $params = $request->validated();
        $location_ids = $request->entity->locations()->pluck('id')->toArray();

        $datas = SaleTransaction::select(
                DB::raw('sum(net_sales_after_tax) as net_sales_after_tax'),
                DB::raw('sum(net_profit) as net_profit'),
                DB::raw("DATE_FORMAT(local_sales_at, '%Y-%m') as local_sales_date")
            )
            ->where('status', 'ok')
            ->whereIn('sale_transactions.location_id', $location_ids)
            ->whereIn(DB::raw("DATE_FORMAT(sales_at, '%Y')"),  array($params['first_year'], $params['second_year']))
            ->groupBy('local_sales_date');

        if ($params['select_all_location'] == 'true' && array_key_exists('exclude_locs', $params)) {
            $datas->whereNotIn('location_id', $params['exclude_locs']);
        } else if ($params['select_all_location'] == 'false' && array_key_exists('locs', $params)) {
            $datas->whereIn('location_id', $params['locs']);
        }

        $grouped = array();
        foreach($datas->get() as $result) {
            $grouped[$result['local_sales_date']] = array(
                "net_sales_after_tax" => $result['net_sales_after_tax'],
                "net_profit" => $result['net_profit'],
            );
        }

        $result = array();
        for ($x = 0; $x < 12; $x++) {
            if ($x == 0) {
                $result = array(
                    'months' => [
                        'Jan', 'Feb', 'Mar', 'Apr',
                        'Mei', 'Jun', 'Jul', 'Aug',
                        'Sep', 'Okt', 'Nov', 'Des',
                    ],
                    'first_year_net_sales_after_tax' => [],
                    'first_year_net_profit' => [],
                );

                if ($params['first_year'] != $params['second_year']) {
                    $result['second_year_net_sales_after_tax'] = [];
                    $result['second_year_net_profit'] = [];
                }
            }

            $keys = $this->buildDateFormat($params['first_year'], $x + 1);
            if (array_key_exists($keys, $grouped)) {
                array_push($result['first_year_net_sales_after_tax'], $grouped[$keys]['net_sales_after_tax']);
                array_push($result['first_year_net_profit'], $grouped[$keys]['net_profit']);
            } else {
                array_push($result['first_year_net_sales_after_tax'], 0);
                array_push($result['first_year_net_profit'], 0);
            }

            if ($params['first_year'] != $params['second_year']) {
                $keys = $this->buildDateFormat($params['second_year'], $x + 1);
                if (array_key_exists($keys, $grouped)) {
                    array_push($result['second_year_net_sales_after_tax'], $grouped[$keys]['net_sales_after_tax']);
                    array_push($result['second_year_net_profit'], $grouped[$keys]['net_profit']);
                } else {
                    array_push($result['second_year_net_sales_after_tax'], 0);
                    array_push($result['second_year_net_profit'], 0);
                }
            }
        }

        $response = new BaseJsonResponse($result);
        return $response->response();
    }

    private function buildDateFormat($year, $month)
    {
        if ($month >= 10) {
            return $year .'-'. $month;
        }

        return $year .'-0'. $month;
    }
}
