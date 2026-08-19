<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\AssetCategoryService;
use Inertia\Inertia;
use Throwable;

class AssetCategoryController extends Controller
{
    public function __construct(
        protected AssetCategoryService $service
    ) {}

    public function index()
    {
        try {
            $pagination = $this->service->getCategoryAssets();
            $locationOptions = $this->service->getLocationOptions();
            $summary = $this->service->getAssetSummary();

            return Inertia::render('reports/stocks/assetsbycategories/index', compact(
                'pagination',
                'locationOptions',
                'summary'
            ));
        } catch (Throwable $e) {
            Helper::logException($e, [
                'source' => self::class,
                'method' => __FUNCTION__,
            ]);

            throw $e;
        }
    }
}