<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockDocumentNoteRequest;
use App\Http\Requests\StoreStockCountRequest;
use App\Services\ProductOpnameService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductOpnameServiceController extends Controller
{
    public function __construct(
        protected ProductOpnameService $service
    ) {}

    public function index(): Response
    {
        $pagination = $this->service->getDocuments();
        $locationOptions = $this->service->getLocationOptions();

        return Inertia::render('stock-opnames/index', compact('pagination', 'locationOptions'));
    }

    public function create(): Response
    {
        return Inertia::render('stock-opnames/form', [
            'document' => null,
            'locationOptions' => $this->service->getLocationOptions(),
        ]);
    }

    public function store(StoreStockCountRequest $request): RedirectResponse
    {
        $document = $this->service->store($request->validated());

        return to_route('product-opname-services.show', $document->id)
            ->with('success', 'Stok opname berhasil diajukan');
    }

    public function show(int $productOpnameService): Response
    {
        return Inertia::render('stock-opnames/show', [
            'document' => $this->service->find($productOpnameService),
        ]);
    }

    public function edit(int $productOpnameService): Response
    {
        return Inertia::render('stock-opnames/form', [
            'document' => $this->service->find($productOpnameService),
            'locationOptions' => $this->service->getLocationOptions(),
        ]);
    }

    public function update(StoreStockCountRequest $request, int $productOpnameService): RedirectResponse
    {
        $this->service->update($this->service->find($productOpnameService), $request->validated());

        return to_route('product-opname-services.show', $productOpnameService)
            ->with('success', 'Stok opname berhasil diperbarui');
    }

    public function destroy(int $productOpnameService): RedirectResponse
    {
        $this->service->destroy($this->service->find($productOpnameService));

        return to_route('product-opname-services.index')->with('success', 'Stok opname berhasil dihapus');
    }

    public function approve(StockDocumentNoteRequest $request, int $id): RedirectResponse
    {
        $this->service->approve($this->service->find($id), $request->validated('note'));

        return back()->with('success', 'Stok opname disetujui, stok telah disesuaikan');
    }

    public function reject(StockDocumentNoteRequest $request, int $id): RedirectResponse
    {
        $this->service->reject($this->service->find($id), $request->validated('note'));

        return back()->with('success', 'Stok opname ditolak');
    }
}
