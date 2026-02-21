<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\IndexKasirProductLocationStockRequest;
use App\Http\Responses\BaseJsonResponse;
use App\Models\ProductLocationStock;
use App\Models\ProductStockMovement;

class KasirProductLocationStockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexKasirProductLocationStockRequest $request)
    {
        //
        $params = $request->validated();

        $datas = ProductLocationStock::with([
                'product:id,sku,barcode,name,sell_price',
                'productUnit:id,name',
                'location:id,name',
            ])
            ->where('product_id', $params['prod_id']);
        
        return $datas->cursorPaginate($request->limit ?? 15)->appends($params);
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductLocationStock $productLocationStock)
    {
        //
        return $this->detailResponse($productLocationStock)->response();
    }

    private function detailResponse(ProductLocationStock $productLocationStock)
    {
        $data = $productLocationStock->load([
            'product:id,sku,barcode,name,sell_price',
            'productUnit:id,name',
            'location:id,name',
        ]);

        $productStockMovements = ProductStockMovement::with([
            'product:id,sku,barcode,name,sell_price',
        ])
            ->where('location_id', $data->location_id)
            ->where('product_unit_id', $data->product_unit_id)
            ->where('product_id', $data->product_id)
            ->limit(5)
            ->get();

        $result = [
            'productLocationStock' => $data,
            'productStockMovements' => $productStockMovements,
        ];

        return new BaseJsonResponse($result);
    }
}
