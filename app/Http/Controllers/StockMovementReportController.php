<?php

namespace App\Http\Controllers;

use App\Services\ReportPdfService;
use App\Services\StockReportService;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class StockMovementReportController extends Controller
{
    public function __construct(
        protected StockReportService $service
    ) {}

    public function index(): Response
    {
        $pagination = $this->service->getStockMovements();
        $locationOptions = $this->service->getLocationOptions();

        return Inertia::render('reports/stocks/movements/index', compact('locationOptions', 'pagination'));
    }

    public function pdf(ReportPdfService $pdf): HttpResponse
    {
        return $pdf->stream(
            'Laporan Pergerakan Stok',
            [
                ReportPdfService::column('product_name', 'Produk'),
                ReportPdfService::column('product_sku', 'SKU'),
                ReportPdfService::column('sell_price', 'Harga Jual', 'currency'),
                ReportPdfService::column('location_name', 'Lokasi'),
                ReportPdfService::column('product_unit_name', 'Satuan'),
                ReportPdfService::column('date', 'Tanggal', 'date'),
                ReportPdfService::column('stock_in', 'Stok Masuk', 'number', true),
                ReportPdfService::column('stock_out', 'Stok Keluar', 'number', true),
            ],
            $this->service->getStockMovementsForPdf(),
            [
                'Periode' => $pdf->periodLabel(),
                'Lokasi' => $pdf->locationsLabel(),
            ],
        );
    }
}
