<?php

namespace App\Http\Controllers;

use App\Services\SaleReportService;
use Inertia\Inertia;

class SalesReportController extends Controller
{
    public function __construct(
        protected SaleReportService $service
    ) {}

    public function index()
    {
        $pagination = $this->service->getSaleReports();

        return Inertia::render('reports/sellings/summary', [
            'pagination' => $pagination,
        ]);
    }
}
