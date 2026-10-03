<?php

namespace App\Http\Controllers;

use App\Enums\StatusEnum;
use App\Services\DailySaleService;
use App\Services\SaleTransactionService;
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
