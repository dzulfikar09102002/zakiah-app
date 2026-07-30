<?php

namespace App\Helpers\Services\Product;

use App\Helpers\Data\Product\ProductRequest;
use App\Models\Entity;
use App\Models\Location;
use App\Models\ProductUnit;
use App\Models\Supplier;
use App\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProductTransformerFromRequestServices
{
    protected array $params;
    protected Entity $entity;
    protected Request $request;

    /**
     * Create a new class instance.
     */
    public function __construct(Request $request)
    {
        //
        $this->params = $request->validated();
        $this->entity = $request->entity;
        $this->request = $request;
    }

    public function transform(): ProductRequest
    {
        if (!empty($this->params['supplier_name'])) {
            $supplierName = $this->params['supplier_name'];
            $entityId = $this->entity->id;
            $supplier = Supplier::where('name', $supplierName)
                ->where('entity_id', $entityId)
                ->first();
            if (!$supplier) {
                $baseInitial = strtoupper(substr($supplierName, 0, 3));
                $initial = $baseInitial;
                $counter = 1;
                while (Supplier::where('initial', $initial)->exists()) {
                    $initial = substr($baseInitial, 0, 3 - strlen((string)$counter)) . $counter;
                    $counter++;
                }

                $supplier = Supplier::create([
                    'name'      => $supplierName,
                    'entity_id' => $entityId,
                    'status'    => 'active',
                    'code'      => 'SUP' . strtoupper(substr(uniqid(), -6)), 
                    'initial'   => $initial,
                ]);
            }
            $this->params['supplier_id'] = $supplier->id;
        }
        return (new ProductRequest())
            ->setFillable($this->params)
            ->setCreatedBy($this->request->user())
            ->setUpdatedBy($this->request->user())
            ->setEntity($this->entity)
            ->setLocationIds($this->getLocationIds())
            ->setStockMovements($this->params['stock_movements'] ?? [])
            ->setLocation($this->getLocation([$this->params['location_id']]))
            ->setTax($this->getTax($this->params['tax_id']))
            ->setProductUnit($this->getProductUnit($this->params['product_unit_id']))
            ->setProductSellUnit($this->getProductUnit($this->params['product_sell_unit_id']));
    }

    protected function getLocationIds(): Collection
    {
        $selectAllLocation = $this->params['select_all_location'];
        $locationIds = $this->params['location_ids'];
        $excludeLocationIds = $this->params['exclude_location_ids'];

        $locations = Location::where('entity_id', $this->entity->id)->select(['id'])->distinct()->pluck('id');
        if ($selectAllLocation) {
            if ($excludeLocationIds) {
                $locations = $locations->whereNotIn('id', $excludeLocationIds);
            }
        } else if ($locationIds) {
            $locations = $locations->whereIn('id', $locationIds);
        }

        return $locations;
    }

    protected function getProductUnit(?int $id, bool $required = true): ?ProductUnit
    {
        // dd($this->entity->id, $id);
        $producUnit = ProductUnit::where('entity_id', $this->entity->id)->where('id', $id)->first();
        if ($required && !$producUnit) {
            throw ValidationException::withMessages([
                'product_unit' => 'This value is incorrect',
            ]);
        }

        return $producUnit;
    }

    protected function getTax(?int $id): ?Tax
    {
        return Tax::where('entity_id', $this->entity->id)->where('id', $id)->first();
    }

    protected function getLocation(array $ids): ?Location
    {
        return Location::where('entity_id', $this->entity->id)->whereIn('id', $ids)->first();
    }
}
