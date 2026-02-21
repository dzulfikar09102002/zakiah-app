<?php

namespace App\Http\Controllers\Report;

use App\Helpers\Queries\ReportByProductQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Report\IndexReportByProductRequest;
use App\Http\Responses\BaseJsonWithPagingResponse;

class ReportByProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexReportByProductRequest $request)
    {
        //
        $params = array_merge($request->validated(), ['entity' => $request->entity]);
        $result = (new ReportByProductQuery($params));

        $response = new BaseJsonWithPagingResponse($result->filter());
        return $response->setPaging($result->generatePaging())->response();
    }
}
