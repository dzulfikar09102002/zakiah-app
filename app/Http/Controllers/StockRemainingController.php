<?php

namespace App\Http\Controllers;
use App\Services\StockRemainingService;
use Inertia\Inertia;

class StockRemainingController extends Controller
{
    public function __construct(
        private StockRemainingService $service
    ) {}
    public function index()
    {
        $pagination = $this->service->getRemainingStock();
        return Inertia::render('reports/stocks/remaining', compact('pagination'));
    }
}
