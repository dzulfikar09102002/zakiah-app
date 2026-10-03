<?php

namespace App\Http\Controllers;

use App\Services\ReportPdfService;
use App\Services\StockReportService;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class StockCardReportController extends Controller
{
    public function __construct(
        protected StockReportService $service
    ) {}

    public function index(): Response
    {
        $pagination = $this->service->getStockCards();
        $locationOptions = $this->service->getLocationOptions();

        return Inertia::render('reports/stocks/cards/index', compact('locationOptions', 'pagination'));
    }

    public function pdf(ReportPdfService $pdf): HttpResponse
    {
        $locationId = (int) request('loc');

        abort_if($locationId <= 0, 422, 'Silakan pilih lokasi terlebih dahulu.');

        return $pdf->stream(
            'Laporan Kartu Stok',
            [
                ReportPdfService::column('product_name', 'Produk'),
                ReportPdfService::column('product_sku', 'SKU'),
                ReportPdfService::column('sell_price', 'Harga Jual', 'currency'),
                ReportPdfService::column('product_unit_name', 'Satuan'),
                ReportPdfService::column('stock_in', 'Stok Masuk', 'number', true),
                ReportPdfService::column('stock_out', 'Stok Keluar', 'number', true),
                ReportPdfService::column('difference', 'Selisih', 'number', true),
            ],
            $this->service->getStockCardsForPdf($locationId),
            [
                'Periode' => $pdf->periodLabel(),
                'Lokasi' => $pdf->locationName($locationId),
            ],
        );
    }
}
