<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Helpers\NumberFormat;

class ProductsExport implements FromCollection
{
    use Exportable;

    private Collection $result;

    public function __construct(Collection $result)
    {
        $this->result = $result;
    }
    
    public function collection()
    {
        $datas = new Collection([
            ['Product', 'SKU', 'Barcode', 'Kategori', 'Harge Beli', 'Harga', 'Stok']
        ]);
        foreach ($this->result as $res) {
            $stock = 0;
            foreach ($res['productLocationStocks'] ?? [] as $product_location_stock) {
                $stock += $product_location_stock['stock'];
            }

            $datas->push([
                $res->name,
                $res->sku,
                $res->barcode,
                $res['productCategory']['name'],
                NumberFormat::money($res['cost_of_goods_sold']),
                NumberFormat::money($res['sell_price']),
                $stock . '',
            ]);
        }

        return $datas;
    }
}
