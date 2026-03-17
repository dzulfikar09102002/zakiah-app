<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Services\StockRemainingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

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

    public function export(Request $request, $locationId)
    {
        $location = Location::findOrFail($locationId);
        $data = $this->service->getAllStockForExport($locationId);

        $fileName = 'Stok-Sisa-' . str_replace(' ', '-', $location->name) . '.xlsx';

        return Excel::download(
            new StockRemainingExport($data),
            $fileName
        );
    }
}

class StockRemainingExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
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
        return ['SKU', 'Barcode', 'Nama', 'Kategori', 'Stok', 'HPP', 'Harga Jual'];
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
            $row['Harga Jual'] ?? 0,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,  
            'B' => NumberFormat::FORMAT_TEXT,  
            'F' => '_-"Rp "* #,##0_-',       
            'G' => '_-"Rp "* #,##0_-',       
        ];
    }
}