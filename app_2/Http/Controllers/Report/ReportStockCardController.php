<?php

namespace App\Http\Controllers\Report;

use App\Helpers\Queries\ReportStockCardQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\IndexReportStockCardRequest;
use App\Http\Responses\BaseJsonWithPagingResponse;

class ReportStockCardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexReportStockCardRequest $request)
    {
        //
        $params = array_merge($request->validated(), ['entity' => $request->entity]);
        $result = (new ReportStockCardQuery($params));

        $response = new BaseJsonWithPagingResponse($result->filter());
        return $response->setPaging($result->generatePaging())->response();
    }
}
