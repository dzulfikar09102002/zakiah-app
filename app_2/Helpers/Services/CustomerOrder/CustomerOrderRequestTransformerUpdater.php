<?php

namespace App\Helpers\Services\CustomerOrder;

use App\Helpers\Data\CustomerOrder\CustomerOrderLine;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderDetail;
use App\Models\Entity;
use App\Models\Device;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class CustomerOrderRequestTransformerUpdater extends CustomerOrderRequestTransformer
{
    private CustomerOrder $customerOrder;
    private Collection $customerOrderDetails;
    
    /**
     * Create a new class instance.
     */
    public function __construct(Entity $entity, Device $device, CustomerOrder $customerOrder, Request $request)
    {
        //
        parent::__construct($entity, $device, $request);

        $this->customerOrder = $customerOrder;
        $this->customerOrderDetails = $this->customerOrder->customerOrderDetails()->get();

        $this->mergeHeaderParam();
        $this->mergeProductsParam();
    }

    private function mergeHeaderParam()
    {
        $this->params = array_merge($this->params, array("code" => $this->customerOrder->code));

        if (!array_key_exists('order_type_id', $this->params)) {
            $this->params = array_merge($this->params, array("order_type_id" => $this->customerOrder->order_type_id));
        }

        if (!array_key_exists('location_id', $this->params)) {
            $this->params = array_merge($this->params, array("location_id" => $this->customerOrder->location_id));
        }

        if (!array_key_exists('adjustment', $this->params)) {
            $this->params = array_merge($this->params, array("adjustment" => $this->customerOrder->adjustment));
        }
    }

    private function mergeProductsParam()
    {
        $existedLine = array();

        # products = [1,2] , details = [1,2,3]
        foreach ($this->params['products'] as $product) {
            $customerOrderDetail = $this->findExistingFromDetail($product['id'] ?? 0);
            if ($customerOrderDetail == null) {
                continue;
            }

            $product = array_merge($product, [
                'id' => $customerOrderDetail->id,
                'product_id' => $customerOrderDetail->product_id,
                'brand_id' => $customerOrderDetail->brand_id,
                'order_type_id' => $this->params['order_type_id'],
                'product_unit_id' => $customerOrderDetail->product_unit_id,
                'product_category_id' => $customerOrderDetail->product_category_id,
                'catalogue_detail_id' => $customerOrderDetail->catalogue_detail_id,
                'sell_price' => $customerOrderDetail->sell_price,
                'custom_price' => $customerOrderDetail->custom_price,
            ]);

            array_push($existedLine, $customerOrderDetail->id);
        }

        foreach ($this->customerOrderDetails as $customerOrderDetail) {
            if (in_array($customerOrderDetail->id, $existedLine)) {
                continue;
            }

            $product = [
                'id' => $customerOrderDetail->id,
                'product_id' => $customerOrderDetail->product_id,
                'brand_id' => $customerOrderDetail->brand_id,
                'order_type_id' => $this->params['order_type_id'],
                'product_unit_id' => $customerOrderDetail->product_unit_id,
                'product_category_id' => $customerOrderDetail->product_category_id,
                'catalogue_detail_id' => $customerOrderDetail->catalogue_detail_id,
                'quantity' => $customerOrderDetail->quantity,
                'sell_price' => $customerOrderDetail->sell_price,
                'custom_price' => $customerOrderDetail->custom_price,
                'adjustment' => $customerOrderDetail->adjustment,
                '_destroy' => true,
            ];

            array_push($this->params['products'], $product);
        }
    }

    private function findExisting($id): array
    {
        foreach ($this->params['products'] as $product) {
            $productId = ($product['id'] ?? 0);
            if ($productId == $id) {
                return $product;
            }
        }

        return null;
    }

    private function findExistingFromDetail($id): ?CustomerOrderDetail
    {
        foreach ($this->customerOrderDetails as $customerOrderDetail) {
            $productId = $customerOrderDetail->id;
            if ($productId == $id) {
                return $customerOrderDetail;
            }
        }

        return null;
    }
}
