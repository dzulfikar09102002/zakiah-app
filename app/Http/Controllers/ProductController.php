<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $service
    ) {}

    public function index()
    {
        $pagination = $this->service->getProducts();
        $categoryOptions = $this->service->getCategoryOptions();

        return Inertia::render('products/index', compact('pagination', 'categoryOptions'));
    }
}
