<?php

namespace App\Http\Controllers;

use App\Enums\LegalEntityTypeEnum;
use App\Enums\PhoneNumberCountryCodeEnum;
use App\Http\Requests\StoreLegalEntityRequest;
use App\Http\Requests\UpdateLegalEntityRequest;
use App\Models\LegalEntity;
use App\Services\LegalEntityService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LegalEntityController extends Controller
{
    public function __construct(
        protected LegalEntityService $service
    ) {}

    public function index(): Response
    {
        return Inertia::render('legal-entities/index', [
            'pagination' => $this->service->getLegalEntities(),
            'legalTypes' => LegalEntityTypeEnum::options(),
            'phoneCountryCodes' => PhoneNumberCountryCodeEnum::options(),
        ]);
    }

    public function deleted(): Response
    {
        return Inertia::render('legal-entities/index', [
            'pagination' => $this->service->getLegalEntities(onlyTrashed: true),
            'onlyTrashed' => true,
            'legalTypes' => LegalEntityTypeEnum::options(),
            'phoneCountryCodes' => PhoneNumberCountryCodeEnum::options(),
        ]);
    }

    public function store(StoreLegalEntityRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('legal-entities.index')->with('success', 'CV berhasil ditambahkan');
    }

    public function update(UpdateLegalEntityRequest $request, LegalEntity $legalEntity): RedirectResponse
    {
        $this->ensureBelongsToEntity($legalEntity);

        $this->service->update($request->validated(), $legalEntity);

        return to_route('legal-entities.index')->with('success', 'CV berhasil diperbarui');
    }

    public function destroy(LegalEntity $legalEntity): RedirectResponse
    {
        $this->ensureBelongsToEntity($legalEntity);

        $this->service->delete($legalEntity);

        return to_route('legal-entities.index')->with('success', 'CV berhasil dihapus');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->service->restore($id);

        return to_route('legal-entities.deleted')->with('success', 'CV berhasil dipulihkan');
    }

    private function ensureBelongsToEntity(LegalEntity $legalEntity): void
    {
        abort_unless((int) $legalEntity->entity_id === (int) auth()->user()?->entity?->id, 404);
    }
}
