<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
class ComingSoonController extends Controller
{
    public function index()
    {
        return Inertia::render("comingsoon/index");
    }
}