<?php

namespace App\Imports;

use Exception;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ProductImportServiceBarangReader implements WithHeadingRow, ToCollection
{

    public function collection(Collection $rows)
    {
        $result = [];

        foreach ($rows as $row) 
        {
            if (!isset($row['kode'])) {
                continue;
            }

            array_push($result, $row['kode']);
        }

        return $result;
    }
}
