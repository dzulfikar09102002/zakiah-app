<?php

namespace App\Http\Controllers;

use App\Http\Services\ProductService;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $service
    ) {}

    public function index()
    {
        $entityId = auth()->user()?->entity?->id;
        $pagination = $this->service->getProducts($entityId);
        $categoryOptions = $this->service->getCategories($entityId);
        $categoryOptions->prepend([
            'value' => 'all',
            'label' => 'Semua',
        ]);

        return Inertia::render('products/index', compact('pagination', 'categoryOptions'));
    }
}
