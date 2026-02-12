<?php

namespace App\Http\Controllers;
use App\Http\Services\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{

    public function __construct(
        private ProductService $service
    ) {}
    public function index()
    {
        $perPage = request('per_page', 10);

        $entityId =  auth()->user()?->entity?->id; 

        $products = $this->service->getProducts($entityId, $perPage);

        $categories = $this->service->getCategories($entityId);

        return Inertia::render('products/index', compact('products', 'categories'));
    }
}
