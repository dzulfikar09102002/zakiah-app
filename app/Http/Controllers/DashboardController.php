<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $service
    ) {}
    public function index()
    {
        return Inertia::render('dashboard', [

            'locationOptions' => fn() =>
                $this->service->getLocationOptions(),

            'profitPotential' => fn() =>
                $this->service->getProfitPotential(),

            'salesRefundSummary' => fn() =>
                $this->service->getSalesRefundSummary(),

            'salesSummary' => fn() =>
                $this->service->getSalesSummary(),

            'top5' => fn() => array_merge(
                $this->service->getTopProductsAndCategories(),
                [
                    'locations' => $this->service->getTopLocations(),
                ]
            ),

            'salesByDate' => fn() =>
                $this->service->getSalesByDate(),

            'monthlySales' => fn() =>
                $this->service->getMonthlySales(),

            'yearlySales' => fn() =>
                $this->service->getYearlySales(),
        ]);
    }
}
