<?php

namespace App\Imports;

use App\Models\Entity;
use App\Models\ProductImportService;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductImportedServiceDetailImport implements WithMultipleSheets, SkipsUnknownSheets
{
    private Entity $entity;
    private ProductImportService $importService;

    public function __construct(Entity $entity, ProductImportService $importService)
    {
        $this->entity = $entity;
        $this->importService = $importService;
    }
    
    public function sheets(): array
    {
        return [
            0 => new ProductImportServiceBarangImport($this->entity, $this->importService),
        ];
    }
    
    public function onUnknownSheet($sheetName)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$sheetName} was skipped");
    }
}
