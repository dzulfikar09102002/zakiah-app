<?php

namespace App\Http\Controllers;

use App\Services\EmployeeReportDetailService;
use App\Services\LocationService;
use Inertia\Inertia;

class EmployeeReportDetailController extends Controller
{
    public function __construct(
        protected EmployeeReportDetailService $employeeReportDetailService,
        protected LocationService $locationService,
    ) {
    }

    public function index()
    {
        return Inertia::render('reports/employees/sales-perform-detail', [
            'employeeSalesDetail' => fn () => $this->employeeReportDetailService->getEmployeeSalesDetail(),
            'locationOptions' => fn () => $this->employeeReportDetailService->getLocationOptions(),
        ]);
    }
}