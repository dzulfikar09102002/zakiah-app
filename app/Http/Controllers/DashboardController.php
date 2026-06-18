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
        $profitPotential = $this->service->getProfitPotential();
        $locationOptions = $this->service->getLocationOptions();
        return Inertia::render('dashboard', compact('profitPotential', 'locationOptions'));
    }
}
