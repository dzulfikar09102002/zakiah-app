<?php

namespace App\Services;

use App\Helpers\Services\ProductAdjustmentStock\ProductAdjustmentStockApprovalService;
use App\Models\ProductAdjustmentStock;
use App\Models\ProductAdjustmentStockDetail;
use Illuminate\Database\Eloquent\Model;

class ProductAdjustmentStockService extends StockCountService
{
    protected function documentClass(): string
    {
        return ProductAdjustmentStock::class;
    }

    protected function detailClass(): string
    {
        return ProductAdjustmentStockDetail::class;
    }

    protected function detailsRelation(): string
    {
        return 'productAdjustmentStockDetails';
    }

    protected function foreignKey(): string
    {
        return 'product_adjustment_stock_id';
    }

    protected function moveStock(Model $document): void
    {
        (new ProductAdjustmentStockApprovalService($document))->adjustStock();
    }

    protected function errorKey(): string
    {
        return 'product_adjustment_stock';
    }
}
