<?php

namespace App\Http\Controllers;

use App\Enums\PhoneNumberCountryCodeEnum;
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
        $phoneCountryCodes = PhoneNumberCountryCodeEnum::options();
        $pagination = $this->service->getLocations();
        return Inertia::render('locations/index', compact('pagination', 'phoneCountryCodes'));
    }

    public function store(StorelocationRequest $request)
    {
        dd($request);
        $this->service->store($request->validated());
        return to_route('locations.index')->with('success', 'Lokasi berhasil ditambahkan');
    }

    public function update(UpdatelocationRequest $request, location $location)
    {
        
    }

    public function destroy(location $location)
    {
        
    }
}
