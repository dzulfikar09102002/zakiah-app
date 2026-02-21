<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModifierRequest;
use App\Http\Requests\UpdateModifierRequest;
use App\Models\Modifier;

class ModifierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreModifierRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Modifier $modifier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateModifierRequest $request, Modifier $modifier)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Modifier $modifier)
    {
        //
    }
}
