<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockDocumentNoteRequest;
use App\Http\Requests\StoreStockCountRequest;
use App\Services\ProductAdjustmentStockService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductAdjustmentStockController extends Controller
{
    public function __construct(
        protected ProductAdjustmentStockService $service
    ) {}

    public function index(): Response
    {
        $pagination = $this->service->getDocuments();
        $locationOptions = $this->service->getLocationOptions();

        return Inertia::render('stock-adjustments/index', compact('pagination', 'locationOptions'));
    }

    public function create(): Response
    {
        return Inertia::render('stock-adjustments/form', [
            'document' => null,
            'locationOptions' => $this->service->getLocationOptions(),
        ]);
    }

    public function store(StoreStockCountRequest $request): RedirectResponse
    {
        $document = $this->service->store($request->validated());

        return to_route('product-adjustment-stocks.show', $document->id)
            ->with('success', 'Penyesuaian stok berhasil disimpan');
    }

    public function show(int $productAdjustmentStock): Response
    {
        return Inertia::render('stock-adjustments/show', [
            'document' => $this->service->find($productAdjustmentStock),
        ]);
    }

    public function approve(StockDocumentNoteRequest $request, int $id): RedirectResponse
    {
        $this->service->approve($this->service->find($id), $request->validated('note'));

        return back()->with('success', 'Penyesuaian stok disetujui, stok telah disesuaikan');
    }

    public function reject(StockDocumentNoteRequest $request, int $id): RedirectResponse
    {
        $this->service->reject($this->service->find($id), $request->validated('note'));

        return back()->with('success', 'Penyesuaian stok ditolak');
    }
}
