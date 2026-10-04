<?php

namespace App\Http\Controllers;

use App\Enums\LegalEntityTypeEnum;
use App\Http\Requests\StoreLegalEntityLocationRequest;
use App\Models\LegalEntityLocation;
use App\Services\LegalEntityLocationService;
use App\Services\LegalEntityService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LegalEntityLocationController extends Controller
{
    public function __construct(
        protected LegalEntityLocationService $service,
        protected LegalEntityService $legalEntityService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('legal-entity-locations/index', [
            'pagination' => $this->service->getLocations(),
            'legalEntityOptions' => $this->legalEntityService->getLegalEntityOptions(),
            'legalTypes' => LegalEntityTypeEnum::options(),
        ]);
    }

    public function store(StoreLegalEntityLocationRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('legal-entity-locations.index')->with('success', 'CV berhasil ditambahkan ke lokasi');
    }

    public function destroy(LegalEntityLocation $legalEntityLocation): RedirectResponse
    {
        $this->service->ensureBelongsToEntity($legalEntityLocation);

        $this->service->delete($legalEntityLocation);

        return to_route('legal-entity-locations.index')->with('success', 'CV berhasil dilepas dari lokasi');
    }
}
