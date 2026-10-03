<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockDocumentNoteRequest;
use App\Http\Requests\StoreStockTransferRequest;
use App\Services\StockTransferService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductTransferServiceController extends Controller
{
    public function __construct(
        protected StockTransferService $service
    ) {}

    public function index(): Response
    {
        $pagination = $this->service->getTransfers();
        $locationOptions = $this->service->getLocationOptions();

        return Inertia::render('stock-transfers/index', compact('pagination', 'locationOptions'));
    }

    public function create(): Response
    {
        return Inertia::render('stock-transfers/form', [
            'locationOptions' => $this->service->getLocationOptions(),
        ]);
    }

    public function store(StoreStockTransferRequest $request): RedirectResponse
    {
        $transfer = $this->service->store($request->validated());

        return to_route('product-transfer-services.show', $transfer->id)
            ->with('success', 'Pindah stok berhasil diajukan');
    }

    public function show(int $productTransferService): Response
    {
        return Inertia::render('stock-transfers/show', [
            'document' => $this->service->find($productTransferService),
        ]);
    }

    public function approve(StockDocumentNoteRequest $request, int $productTransferService): RedirectResponse
    {
        $this->service->approve($this->service->find($productTransferService), $request->validated('note'));

        return back()->with('success', 'Pindah stok disetujui');
    }

    public function reject(StockDocumentNoteRequest $request, int $productTransferService): RedirectResponse
    {
        $this->service->reject($this->service->find($productTransferService), $request->validated('note'));

        return back()->with('success', 'Pindah stok ditolak');
    }

    public function cancel(StockDocumentNoteRequest $request, int $productTransferService): RedirectResponse
    {
        $this->service->cancel($this->service->find($productTransferService), $request->validated('note'));

        return back()->with('success', 'Pindah stok dibatalkan');
    }
}
