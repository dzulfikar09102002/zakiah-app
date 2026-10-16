<?php

namespace App\Http\Controllers;

use App\Services\ReportPdfService;
use App\Services\TopCustomerReportService;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response;

class TopCustomerReportController extends Controller
{
    public function __construct(
        protected TopCustomerReportService $service
    ) {}

    public function index(): InertiaResponse
    {
        return Inertia::render('reports/customers/top', [
            'data' => $this->service->getTopCustomers(),
            'locationOptions' => $this->service->getLocationOptions(),
            'topOptions' => TopCustomerReportService::TOP_OPTIONS,
            'top' => $this->service->top(),
        ]);
    }

    public function pdf(ReportPdfService $pdf): Response
    {
        return $pdf->stream(
            'Laporan Top Pelanggan',
            [
                ReportPdfService::column('customer_name', 'Pelanggan'),
                ReportPdfService::column('phone_number', 'No. HP'),
                ReportPdfService::column('category', 'Kategori'),
                ReportPdfService::column('transaction_count', 'Jml Transaksi', 'number', true),
                ReportPdfService::column('total_spend', 'Total Belanja', 'currency', true),
                ReportPdfService::column('average_spend', 'Rata-rata/Transaksi', 'currency'),
                ReportPdfService::column('last_sales_at', 'Terakhir Belanja', 'datetime'),
            ],
            $this->service->getTopCustomers(),
            [
                'Periode' => $pdf->periodLabel(),
                'Lokasi' => $pdf->locationsLabel(),
                'Jumlah' => 'Top '.$this->service->top(),
            ],
        );
    }
}
