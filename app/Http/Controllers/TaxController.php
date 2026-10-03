<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaxRequest;
use App\Http\Requests\UpdateTaxRequest;
use App\Models\Tax;
use App\Services\TaxService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TaxController extends Controller
{
    public function __construct(
        protected TaxService $service
    ) {}

    public function index(): Response
    {
        $pagination = $this->service->getTaxes();

        return Inertia::render('taxes/index', compact('pagination'));
    }

    public function store(StoreTaxRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return back()->with('success', 'Pajak berhasil ditambahkan');
    }

    public function update(UpdateTaxRequest $request, Tax $tax): RedirectResponse
    {
        $this->service->ensureOwnedByEntity($tax);
        $this->service->update($tax, $request->validated());

        return back()->with('success', 'Pajak berhasil diperbarui');
    }

    public function toggleStatus(Tax $tax): RedirectResponse
    {
        $this->service->ensureOwnedByEntity($tax);
        $this->service->toggleStatus($tax);

        return back()->with('success', 'Status pajak berhasil diperbarui');
    }
}
