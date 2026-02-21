<?php

namespace App\Http\Controllers\Report;

use App\Helpers\Queries\ReportSalesQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\IndexReportSalesRequest;
use App\Http\Responses\BaseJsonWithPagingResponse;

class ReportSalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexReportSalesRequest $request)
    {
        //
        $params = array_merge($request->validated(), ['entity' => $request->entity]);
        $result = (new ReportSalesQuery($params));

        $response = new BaseJsonWithPagingResponse($result->filter());
        return $response->setPaging($result->generatePaging())->response();
    }
}
