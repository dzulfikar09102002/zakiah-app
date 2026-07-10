<?php

namespace App\Http\Controllers;

use App\Services\EmployeeReportSummaryService;
use Inertia\Inertia;

class EmployeeReportSummaryController extends Controller
{
    public function __construct(
        protected EmployeeReportSummaryService $employeeSummaryService
    ) {
    }

    public function index()
    {
        return Inertia::render('reports/employees/sales-perform', [
            'employeeSalesSummary' => fn () => $this->employeeSummaryService->getEmployeeSalesSummary(),
            'locationOptions' => fn () => $this->employeeSummaryService->getLocationOptions(),
        ]);
    }
}
