<?php

namespace App\Http\Controllers;

use App\Services\ReportPdfService;
use App\Services\SaleReportByLocationService;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class SalesReportByLocationController extends Controller
{
    public function __construct(
        protected SaleReportByLocationService $service
    ) {}

    public function index()
    {
        $pagination = $this->service->getSaleReportByLocations();
        $locationOptions = $this->service->getLocationOptions();

        return Inertia::render('reports/sellings/bylocation', compact('locationOptions', 'pagination'));
    }

    public function pdf(ReportPdfService $pdf): Response
    {
        return $pdf->stream(
            'Laporan Penjualan Per Toko',
            [
                ReportPdfService::column('location_name', 'Lokasi'),
                ReportPdfService::column('quantity', 'Qty', 'number', true),
                ReportPdfService::column('cancelled_quantity', 'Qty Batal', 'number', true),
                ReportPdfService::column('cost_of_goods_sold', 'HPP', 'currency', true),
                ReportPdfService::column('gross_sales', 'Penjualan Kotor', 'currency', true),
                ReportPdfService::column('gross_refund', 'Refund', 'currency', true),
                ReportPdfService::column('discount', 'Diskon', 'currency', true),
                ReportPdfService::column('total', 'Penjualan Bersih', 'currency', true),
                ReportPdfService::column('gross_profit', 'Laba Kotor', 'currency', true),
                ReportPdfService::column('profit', 'Laba Bersih', 'currency', true),
            ],
            $this->service->getSaleReportByLocationsForPdf(),
            [
                'Periode' => $pdf->periodLabel(),
                'Lokasi' => $pdf->locationsLabel(false),
                'Diskon' => $pdf->discountLabel(),
            ],
        );
    }
}
