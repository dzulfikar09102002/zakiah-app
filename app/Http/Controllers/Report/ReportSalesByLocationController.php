<?php

namespace App\Http\Controllers\Report;

use App\Helpers\Queries\ReportSalesByLocationQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\IndexReportSalesByLocationRequest;
use App\Http\Responses\BaseJsonWithPagingResponse;

class ReportSalesByLocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexReportSalesByLocationRequest $request)
    {
        //
        $params = array_merge($request->validated(), ['entity' => $request->entity]);
        $result = (new ReportSalesByLocationQuery($params));

        $response = new BaseJsonWithPagingResponse($result->filter());
        return $response->setPaging($result->generatePaging())->response();
    }
}
