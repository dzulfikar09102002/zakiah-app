<?php

namespace App\Http\Controllers;

use App\Enums\PhoneNumberCountryCodeEnum;
use App\Helpers\Helper;
use App\Http\Requests\StorelocationRequest;
use App\Http\Requests\UpdatelocationRequest;
use App\Models\location;
use App\Services\LocationService;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function __construct(
        protected LocationService $service
    ) {}

    public function index()
    {
        $phoneCountryCodes = PhoneNumberCountryCodeEnum::options();
        $pagination = Helper::getPaginatedData($this->service->getLocations());

        return Inertia::render('locations/index', compact('pagination', 'phoneCountryCodes'));
    }

    public function deleted()
    {
        $onlyTrashed = true;
        $phoneCountryCodes = PhoneNumberCountryCodeEnum::options();
        $pagination = $this->service->getDeletedLocations();

        return Inertia::render('locations/index', compact('pagination', 'onlyTrashed', 'phoneCountryCodes'));
    }

    public function store(StorelocationRequest $request)
    {
        $this->service->store($request->validated());

        return to_route('locations.index')->with('success', 'Lokasi berhasil ditambahkan');
    }

    public function update(UpdatelocationRequest $request, location $location)
    {
        $this->service->update($request->validated(), $location);

        return to_route('locations.index')->with('success', 'Lokasi berhasil diperbarui');
    }

    public function destroy(Location $location)
    {
        $this->service->delete($location);

        return to_route('locations.index')->with('success', 'Lokasi berhasil dihapus');
    }

    public function restore(int $id)
    {
        $this->service->restore($id);

        return to_route('locations.index')->with('success', 'Lokasi berhasil dipulihkan');
    }
}
