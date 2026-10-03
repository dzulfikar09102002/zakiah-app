<?php

namespace App\Services;

use App\Helpers\Services\ProductOpname\ProductOpnameApprovalService;
use App\Models\ProductOpnameService as ProductOpname;
use App\Models\ProductOpnameServiceDetail;
use Illuminate\Database\Eloquent\Model;

class ProductOpnameService extends StockCountService
{
    protected function documentClass(): string
    {
        return ProductOpname::class;
    }

    protected function detailClass(): string
    {
        return ProductOpnameServiceDetail::class;
    }

    protected function detailsRelation(): string
    {
        return 'productOpnameServiceDetails';
    }

    protected function foreignKey(): string
    {
        return 'product_opname_service_id';
    }

    protected function moveStock(Model $document): void
    {
        (new ProductOpnameApprovalService($document))->moveStock();
    }

    protected function errorKey(): string
    {
        return 'product_opname_service';
    }
}
