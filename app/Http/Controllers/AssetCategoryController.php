<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Services\AssetCategoryService;
use App\Services\ReportPdfService;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AssetCategoryController extends Controller
{
    public function __construct(
        protected AssetCategoryService $service
    ) {}

    public function index()
    {
        try {
            $pagination = $this->service->getCategoryAssets();
            $locationOptions = $this->service->getLocationOptions();
            $summary = $this->service->getAssetSummary();

            return Inertia::render('reports/stocks/assetsbycategories/index', compact(
                'pagination',
                'locationOptions',
                'summary'
            ));
        } catch (Throwable $e) {
            Helper::logException($e, [
                'source' => self::class,
                'method' => __FUNCTION__,
            ]);

            throw $e;
        }
    }

    public function pdf(ReportPdfService $pdf): Response
    {
        return $pdf->stream(
            'Laporan Nilai Aset Per Kategori',
            [
                ReportPdfService::column('name', 'Nama Kategori'),
                ReportPdfService::column('total_products', 'Varian Produk', 'number', true),
                ReportPdfService::column('total_stock', 'Total Stok Unit', 'number', true),
                ReportPdfService::column('total_buying_asset', 'Nilai Aset (Harga Beli)', 'currency', true),
                ReportPdfService::column('total_selling_asset', 'Estimasi Nilai Jual', 'currency', true),
            ],
            $this->service->getCategoryAssetsForPdf(),
            [
                'Lokasi' => $pdf->locationsLabel(),
                'Per Tanggal' => now()->locale('id')->translatedFormat('d M Y H:i'),
            ],
            function (): array {
                $summary = $this->service->getAssetSummary();

                return [
                    'Kategori' => number_format((int) $summary?->total_categories, 0, ',', '.'),
                    'Varian Produk' => number_format((int) $summary?->total_products, 0, ',', '.'),
                    'Total Stok' => number_format((int) $summary?->grand_total_stock, 0, ',', '.'),
                    'Nilai Aset' => 'Rp '.number_format((int) $summary?->grand_buying_asset, 0, ',', '.'),
                    'Estimasi Nilai Jual' => 'Rp '.number_format((int) $summary?->grand_selling_asset, 0, ',', '.'),
                ];
            },
            'portrait',
        );
    }
}
