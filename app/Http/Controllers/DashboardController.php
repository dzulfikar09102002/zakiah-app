<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {}


    public function index()
    {
        return Inertia::render('dashboard');
    }


    public function locationOptions()
    {
        return response()->json(
            $this->service->getLocationOptions()
        );
    }


    public function profitPotential()
    {
        return response()->json(
            $this->service->getProfitPotential()
        );
    }


    public function salesRefundSummary()
    {
        return response()->json(
            $this->service->getSalesRefundSummary()
        );
    }


    public function salesSummary()
    {
        return response()->json(
            $this->service->getSalesSummary()
        );
    }


    public function top5()
    {
        return response()->json(
            array_merge(
                $this->service->getTopProductsAndCategories(),
                [
                    'locations' =>
                        $this->service->getTopLocations()
                ]
            )
        );
    }


    public function salesByDate()
    {
        return response()->json(
            $this->service->getSalesByDate()
        );
    }


    public function monthlySales()
    {
        return response()->json(
            $this->service->getMonthlySales()
        );
    }


    public function yearlySales()
    {
        return response()->json(
            $this->service->getYearlySales()
        );
    }
}