<?php

namespace App\Helpers\Services\ProductImported;

use App\Imports\ProductImportedServiceDetailReader;
use Maatwebsite\Excel\Facades\Excel;

class ProductImportedFileReaderService
{
    private const PATH = 'public/productImport';

    private string $fileUrl;

    /**
     * Create a new class instance.
     */
    public function __construct(string $fileUrl)
    {
        //
        $this->fileUrl = $fileUrl;
    }

    public function convert() {
        $array = Excel::toArray(new ProductImportedServiceDetailReader(), self::PATH . '/'. basename($this->fileUrl));

        return $array;
    }

    // private function
}
