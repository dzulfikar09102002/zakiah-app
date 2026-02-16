<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class SellingController extends Controller
{
    public function summary()
    {
        return Inertia::render('reports/sellings/summary');
    }
}
