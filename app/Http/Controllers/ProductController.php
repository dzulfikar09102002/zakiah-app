<?php

namespace App\Http\Controllers;

use App\Http\Services\ProductService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(private ProductService $service){}
    
    public function index()
    {
        $this->service->index();
        return Inertia::render('products/index');
    }
}
