<?php

namespace App\Http\Controllers;


use App\Services\SaleReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SalesReportController extends Controller
{
    public function __construct(
        protected SaleReportService $service
    ) {}

    public function index(Request $request)
    {
        $pagination = $this->service->getSaleReports($request->all());

        return Inertia::render('reports/sellings/summary', [
            'pagination' => $pagination
        ]);
    }
}