<?php

namespace App\Http\Controllers;

use App\Services\PaymentRecapService;
use App\Services\ReportPdfService;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class PaymentRecapController extends Controller
{
    public function __construct(
        private PaymentRecapService $service
    ) {}

    public function index(): Response
    {
        $locationOptions = $this->service->getLocationOptions();
        $recaps = $this->service->getRecaps();
        $pagination = $this->service->paginateRows($recaps);
        $summary = $this->service->summarize($recaps);

        return Inertia::render('revenue/payment-recaps/index', compact('pagination', 'summary', 'locationOptions'));
    }

    /**
     * Master-detail: satu baris per rekapan shift, rincian metode pembayaran di bawahnya.
     */
    public function pdf(ReportPdfService $pdf): HttpResponse
    {
        $recaps = $this->service->getRecaps();

        $rows = $recaps->map(fn (array $recap) => [
            ...$recap,
            '_details' => array_map(fn (array $payment) => [
                ...$payment,
                'label' => $payment['payment_method'],
                'transaction_count' => $payment['sales_count'],
            ], $recap['payments']),
        ]);

        return $pdf->stream(
            'Laporan Detail Rekapan',
            [
                ReportPdfService::column('date', 'Tanggal', 'datetime'),
                ReportPdfService::column('location', 'Lokasi'),
                ReportPdfService::column('shift', 'Shift'),
                ReportPdfService::column('employee', 'Ditutup Oleh'),
                ReportPdfService::column('transaction_count', 'Transaksi', 'number', true),
                ReportPdfService::column('sales_amount', 'Penjualan', 'currency', true),
                ReportPdfService::column('refund_amount', 'Pengembalian', 'currency', true),
                ReportPdfService::column('recorded_amount', 'Tercatat', 'currency', true),
                ReportPdfService::column('counted_amount', 'Dihitung', 'currency', true),
                ReportPdfService::column('difference_amount', 'Selisih', 'currency', true),
            ],
            $rows,
            [
                'Periode' => $pdf->periodLabel(now()->startOfMonth()),
                'Lokasi' => $pdf->locationsLabel(),
            ],
            fn () => collect($this->service->summarize($recaps)['payments'])
                ->mapWithKeys(fn (array $payment) => [$payment['payment_method'] => 'Rp '.number_format($payment['recorded_amount'], 0, ',', '.')])
                ->all(),
        );
    }
}
