<?php

namespace App\Http\Controllers;

use App\Http\Requests\StockDocumentNoteRequest;
use App\Services\ProductOpnameService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductOpnameServiceController extends Controller
{
    public function __construct(
        protected ProductOpnameService $service
    ) {}

    /**
     * Daftar dan pembuatan SO sudah pindah ke sesi SO. Detail, setujui, dan tolak dipertahankan
     * untuk dokumen lama yang masih menunggu persetujuan.
     */
    public function index(): RedirectResponse
    {
        return to_route('product-opname-sessions.index');
    }

    public function show(int $productOpnameService): Response
    {
        return Inertia::render('stock-opnames/show', [
            'document' => $this->service->find($productOpnameService),
        ]);
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
