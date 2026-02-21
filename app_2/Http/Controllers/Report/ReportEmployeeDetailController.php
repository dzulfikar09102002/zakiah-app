<?php

namespace App\Http\Controllers\Report;

use App\Helpers\Queries\ReportEmployeeDetailQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\IndexReportEmployeeDetailRequest;
use App\Http\Responses\BaseJsonWithPagingResponse;

class ReportEmployeeDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexReportEmployeeDetailRequest $request)
    {
        //
        $params = array_merge($request->validated(), ['entity' => $request->entity]);
        $result = (new ReportEmployeeDetailQuery($params));

        $response = new BaseJsonWithPagingResponse($result->filter());
        return $response->setPaging($result->generatePaging())->response();
    }
}
