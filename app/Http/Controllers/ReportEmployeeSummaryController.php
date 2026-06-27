<?php

namespace App\Http\Controllers;

use App\Services\SalesPerformService;
use Inertia\Inertia;

class ReportEmployeeSummaryController
{
    public function __construct(
        protected SalesPerformService $service
    ) {}

    public function index()
    {
        $pagination = $this->service->getEmployeeSalesSummary();
        $locationOptions = $this->service->getLocationOptions();

        return Inertia::render('reports/employees/sales-perform', compact('locationOptions', 'pagination'));
    }
}