<?php

namespace App\Http\Controllers\Report;

use App\Helpers\Queries\ReportEmployeeSummaryQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\IndexReportEmployeeSummaryRequest;
use App\Http\Responses\BaseJsonWithPagingResponse;

class ReportEmployeeSummaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexReportEmployeeSummaryRequest $request)
    {
        //
        $params = array_merge($request->validated(), ['entity' => $request->entity]);
        $result = (new ReportEmployeeSummaryQuery($params));

        $response = new BaseJsonWithPagingResponse($result->filter());
        return $response->setPaging($result->generatePaging())->response();
    }
}
