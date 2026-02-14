<?php

namespace App\Http\Controllers;

use App\Services\LocationService;
use App\Models\location;
use App\Http\Requests\StorelocationRequest;
use App\Http\Requests\UpdatelocationRequest;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function __construct(
        protected LocationService $service
    ) {}
    public function index()
    {
        $perPage = request('per_page', 10);
        $entityId = auth()->user()?->entity?->id;
        $pagination = $this->service->paginate($entityId, $perPage);
        return Inertia::render('locations/index', compact('pagination'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorelocationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(location $location)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(location $location)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatelocationRequest $request, location $location)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(location $location)
    {
        //
    }
}
