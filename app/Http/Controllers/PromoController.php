<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePromoRequest;
use App\Http\Requests\UpdatePromoRequest;
use App\Services\PromoService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PromoController extends Controller
{
    public function __construct(
        protected PromoService $service
    ) {}

    public function index(): Response
    {
        $pagination = $this->service->getPromos();

        return Inertia::render('promos/index', compact('pagination'));
    }

    public function create(): Response
    {
        return $this->form(null, 'create');
    }

    public function store(StorePromoRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return to_route('promos.index')->with('success', 'Promo berhasil ditambahkan');
    }

    public function show(int $promo): Response
    {
        return $this->form($promo, 'show');
    }

    public function edit(int $promo): Response
    {
        return $this->form($promo, 'edit');
    }

    public function update(UpdatePromoRequest $request, int $promo): RedirectResponse
    {
        $this->service->update($this->service->find($promo), $request->validated());

        return to_route('promos.index')->with('success', 'Promo berhasil diperbarui');
    }

    private function form(?int $id, string $mode): Response
    {
        return Inertia::render('promos/form', [
            'promo' => $id ? $this->service->find($id) : null,
            'mode' => $mode,
            'locationOptions' => $this->service->getLocationOptions(),
            'customerCategoryOptions' => $this->service->getCustomerCategoryOptions(),
        ]);
    }
}
