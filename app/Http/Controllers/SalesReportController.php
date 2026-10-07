<?php

namespace App\Http\Controllers;

use App\Services\ReportPdfService;
use App\Services\SaleReportService;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class SalesReportController extends Controller
{
    public function __construct(
        protected SaleReportService $service
    ) {}

    public function index()
    {
        $pagination = $this->service->getSaleReports();
        $locationOptions = $this->service->getLocationOptions();

        return Inertia::render('reports/sellings/summary', compact('locationOptions', 'pagination'));
    }

    public function pdf(ReportPdfService $pdf): Response
    {
        return $pdf->stream(
            'Laporan Penjualan Per Transaksi',
            [
                ReportPdfService::column('transaction_no', 'No. Transaksi'),
                ReportPdfService::column('location', 'Lokasi'),
                ReportPdfService::column('date', 'Tanggal', 'datetime'),
                ReportPdfService::column('cashier', 'Kasir'),
                ReportPdfService::column('sales', 'Sales'),
                ReportPdfService::column('member', 'Member'),
                ReportPdfService::column('payment_method', 'Metode Pembayaran'),
                ReportPdfService::column('subtotal', 'Subtotal', 'currency', true),
                ReportPdfService::column('discount', 'Diskon', 'currency', true),
                ReportPdfService::column('adjustment', 'Penyesuaian', 'currency', true),
                ReportPdfService::column('total', 'Total', 'currency', true),
                ReportPdfService::column('profit', 'Laba', 'currency', true),
            ],
            $this->service->getSaleReportsForPdf(),
            [
                'Periode' => $pdf->periodLabel(),
                'Lokasi' => $pdf->locationsLabel(false),
                'Diskon' => $pdf->discountLabel(),
            ],
        );
    }
}
