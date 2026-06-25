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
            'profitPotential' => $this->service->getProfitPotential(),
            'locationOptions' => $this->service->getLocationOptions(),
            'salesRefundSummary' => $this->service->getSalesRefundSummary(),
            'salesSummary' => $this->service->getSalesSummary(),
            'top5' => $this->service->getTopProductsAndCategories(),
            'salesByDate' => $this->service->getSalesByDate(),
            'monthlySales' => fn() => $this->service->getMonthlySales(),
            'yearlySales' => fn() => $this->service->getYearlySales(),
        ]);
    }
}
