<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTakingRequest;
use App\Http\Requests\UpdateTakingRequest;
use App\Models\Taking;

class TakingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        # find sale_transaction where taking = null
        # sum all payment
        # find sale_refund where taking = null
        # find all payment method
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTakingRequest $request)
    {
        //
    }
}
