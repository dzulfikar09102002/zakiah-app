<?php

namespace App\Http\Controllers;

use App\Services\ReportPdfService;
use App\Services\SaleReportByProductService;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class SalesReportByProductController extends Controller
{
    public function __construct(
        protected SaleReportByProductService $service
    ) {}

    public function index()
    {
        $pagination = $this->service->getSaleReportByProducts();
        $locationOptions = $this->service->getLocationOptions();

        return Inertia::render('reports/sellings/byproduct', compact('locationOptions', 'pagination'));
    }

    public function pdf(ReportPdfService $pdf): Response
    {
        return $pdf->stream(
            'Laporan Penjualan Per Produk',
            [
                ReportPdfService::column('product_name', 'Produk'),
                ReportPdfService::column('product_sku', 'SKU'),
                ReportPdfService::column('category', 'Kategori'),
                ReportPdfService::column('quantity', 'Qty', 'number', true),
                ReportPdfService::column('sell_price', 'Harga Jual', 'currency'),
                ReportPdfService::column('cost_of_goods_sold', 'Harga Beli', 'currency'),
                ReportPdfService::column('gross_sales', 'Penjualan Kotor', 'currency', true),
                ReportPdfService::column('discount', 'Total Diskon', 'currency', true),
                ReportPdfService::column('total', 'Penjualan Bersih', 'currency', true),
                ReportPdfService::column('profit', 'Laba', 'currency', true),
            ],
            $this->service->getSaleReportByProductsForPdf(),
            [
                'Periode' => $pdf->periodLabel(),
                'Lokasi' => $pdf->locationsLabel(false),
                'Diskon' => $pdf->discountLabel(),
            ],
        );
    }
}
