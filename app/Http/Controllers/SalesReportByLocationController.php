<?php

namespace App\Http\Controllers;

use App\Services\SaleReportByLocationService;
use App\Services\SaleReportByProductService;
use Inertia\Inertia;

class SalesReportByLocationController extends Controller
{
    public function __construct(
        protected SaleReportByLocationService $service
    ) {}

    public function index()
    {
        $pagination = $this->service->getSaleReportByLocations();
        $locationOptions = $this->service->getLocationOptions();

        return Inertia::render('reports/sellings/bylocation', compact('locationOptions', 'pagination'));
    }
}
