<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Services\DailySaleService;
use App\Services\SaleTransactionService;
use App\Services\StrukService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SaleTransactionController extends Controller
{
    public function __construct(
        private SaleTransactionService $service
    ) {}

    public function index(DailySaleService $dailySaleService)
    {
        $pagination = $this->service->getSaleTransactions();
        $locationOptions = $dailySaleService->getLocationOptions();
        $orderTypeOptions = $this->service->getOrderTypeOptions();

        return Inertia::render('revenue/sale-transactions/index', compact('pagination', 'locationOptions', 'orderTypeOptions'));
    }

    public function show(int $id)
    {
        $saleTransaction = $this->service->findSaleTransaction($id);
        abort_if($saleTransaction == null, 404);

        $saleTransaction = $this->service->getDetail($saleTransaction);

        return Inertia::render('revenue/sale-transactions/show', compact('saleTransaction'));
    }

    /**
     * Arahkan ke struk penjualan di NewZakicaPOS (tautan bertanda tangan, langsung cetak).
     */
    public function struk(int $id, StrukService $struk): RedirectResponse
    {
        $saleTransaction = $this->service->findSaleTransaction($id);
        abort_if($saleTransaction == null, 404);
        abort_unless($struk->enabled(), 503, 'API struk belum dikonfigurasi (STRUK_API_URL / STRUK_API_SECRET).');

        return redirect()->away($struk->url(
            StrukService::KIND_SALE,
            $saleTransaction->id,
            $saleTransaction->entity_id,
            $saleTransaction->location_id,
        ));
    }

    public function void(Request $request, int $id)
    {
        $data = $request->validate([
            'reason' => 'required|string',
            'notes' => 'required|string',
        ]);

        $saleTransaction = $this->service->findSaleTransaction($id);
        abort_if($saleTransaction == null, 404);

        if ($saleTransaction->status === StatusEnum::Void->value) {
            return back()->withErrors(['reason' => 'Transaksi sudah dibatalkan']);
        }

        $employee = $request->user()->employee;
        if ($employee == null) {
            return back()->withErrors(['reason' => 'Akun Anda tidak terhubung dengan karyawan']);
        }

        $this->service->void($saleTransaction, $employee, $data);

        return to_route('sale-transactions.show', $saleTransaction->id)->with('success', 'Transaksi berhasil dibatalkan');
    }
}
