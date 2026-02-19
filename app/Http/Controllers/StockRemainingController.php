<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Services\StockRemainingService;
use Inertia\Inertia;

class StockRemainingController extends Controller
{
    public function __construct(
        private StockRemainingService $service
    ) {}

    public function chooseLocation()
    {
        $locations = $this->service->getLocations();

        return Inertia::render('reports/stocks/remainings/choose-location', compact('locations'));
    }

    public function report(Location $location)
    {
        $pagination = $this->service->getRemainingStock($location->id);
        $categoryOptions = $this->service->getCategoryOptions();
        $locations = $this->service->getLocations();

        return Inertia::render('reports/stocks/remainings/report', compact('pagination', 'categoryOptions', 'locations', 'location'));
    }
}
