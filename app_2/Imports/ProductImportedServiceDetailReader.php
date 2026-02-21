<?php

namespace App\Imports;

use App\Models\Entity;
use App\Models\ProductImportService;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductImportedServiceDetailReader implements WithMultipleSheets, SkipsUnknownSheets
{
    public function sheets(): array
    {
        return [
            0 => new ProductImportServiceBarangReader(),
        ];
    }
    
    public function onUnknownSheet($sheetName)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$sheetName} was skipped");
    }
}
