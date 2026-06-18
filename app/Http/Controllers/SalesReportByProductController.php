<?php

namespace App\Http\Controllers;

use App\Services\SaleReportByProductService;
use Inertia\Inertia;

class SalesReportByProductController extends Controller
{
    public function __construct(
        protected SaleReportByProductService $service
    ) {}

    public function index()
    {
        $pagination = $this->service->getSaleReportByProducts();
        $locationOptions = $this->service->getLocationOptions();

        return Inertia::render('reports/sellings/byproduct', compact('locationOptions', 'pagination'));
    }
}
