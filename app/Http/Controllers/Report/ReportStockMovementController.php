<?php

namespace App\Http\Controllers\Report;

use App\Helpers\Queries\ReportStockMovementQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\IndexReportStockMovementRequest;
use App\Http\Responses\BaseJsonWithPagingResponse;

class ReportStockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexReportStockMovementRequest $request)
    {
        //
        $params = array_merge($request->validated(), ['entity' => $request->entity]);
        $result = (new ReportStockMovementQuery($params));

        $response = new BaseJsonWithPagingResponse($result->filter());
        return $response->setPaging($result->generatePaging())->response();
    }
}
