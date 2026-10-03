<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEntityRequest;
use App\Services\EntityService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EntityController extends Controller
{
    public function __construct(
        protected EntityService $service
    ) {}

    /**
     * Profil entity milik user yang sedang login.
     */
    public function edit(): Response
    {
        $entity = $this->service->current();

        return Inertia::render('entity/index', [
            'entity' => $entity,
            'logoUrl' => $this->service->logoUrl($entity),
            'timezoneOptions' => $this->service->timezoneOptions(),
        ]);
    }

    public function update(UpdateEntityRequest $request): RedirectResponse
    {
        $this->service->update($this->service->current(), $request->validated());

        return back()->with('success', 'Entity berhasil diperbarui');
    }
}
