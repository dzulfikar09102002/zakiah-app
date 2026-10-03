<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Services\ReportPdfService;
use App\Services\StockRemainingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Symfony\Component\HttpFoundation\Response;

class StockRemainingController extends Controller
{
    public function __construct(
        private StockRemainingService $service
    ) {}

    public function chooseLocation()
    {
        $locations = $this->service->getLocations();

        return Inertia::render(
            'reports/stocks/remainings/choose-location',
            compact('locations')
        );
    }

    public function report(Location $location)
    {
        $pagination = $this->service->getRemainingStock($location->id);
        $categoryOptions = $this->service->getCategoryOptions();
        $locations = $this->service->getLocations();

        return Inertia::render(
            'reports/stocks/remainings/report',
            compact('pagination', 'categoryOptions', 'locations', 'location')
        );
    }

    public function pdf(Location $location, ReportPdfService $pdf): Response
    {
        abort_unless((int) $location->entity_id === (int) auth()->user()?->entity?->id, 404);

        $category = collect($this->service->getCategoryOptions())
            ->firstWhere('value', request('product_category_id', 'all'));

        return $pdf->stream(
            'Laporan Sisa Stok',
            [
                ReportPdfService::column('sku', 'SKU'),
                ReportPdfService::column('barcode', 'Barcode'),
                ReportPdfService::column('name', 'Nama Produk'),
                ReportPdfService::column('category', 'Kategori'),
                ReportPdfService::column('stock', 'Stok', 'number', true),
                ReportPdfService::column('cost_of_goods_sold', 'HPP', 'currency'),
                ReportPdfService::column('sell_price', 'Harga Jual', 'currency'),
            ],
            $this->service->getRemainingStockForPdf($location->id),
            [
                'Lokasi' => $pdf->locationName($location->id),
                'Kategori' => $category['label'] ?? 'Semua kategori',
                'Per Tanggal' => now()->locale('id')->translatedFormat('d M Y H:i'),
            ],
        );
    }

    public function export(Request $request, $locationId)
    {
        $location = Location::findOrFail($locationId);
        $data = $this->service->getAllStockForExport($locationId);
        $entityName = match ($location->entity_id) {
            1 => 'Secaca',
            3 => 'Zakiah',
            default => 'Unknown'
        };
        $fileName = 'Stok-Sisa-'.$entityName.' '.str_replace(' ', '-', $location->name).'.xlsx';

        return Excel::download(
            new StockRemainingExport($data),
            $fileName
        );
    }
}

class StockRemainingExport implements FromCollection, WithColumnFormatting, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return ['SKU', 'Barcode', 'Nama', 'Kategori', 'Stok', 'HPP', 'Harga Beli', 'Harga Jual'];
    }

    public function map($row): array
    {
        return [
            (string) ($row['SKU'] ?? '-'),
            (string) ($row['Barcode'] ?? '-'),

            $row['Nama'] ?? '-',
            $row['Kategori'] ?? '-',
            $row['Stok'] ?? 0,
            $row['HPP'] ?? 0,
            $row['Harga Beli'] ?? 0,
            $row['Harga Jual'] ?? 0,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'F' => '#,##0',
            'G' => '#,##0',
            'H' => '#,##0',
        ];
    }
}
