<?php

namespace App\Helpers\Services\SaleTransaction;

use App\Models\Location;
use App\Models\OrderType;
use App\Models\SaleTransaction;

class HeaderFieldSetter
{
    public static function locationHeader(array $mappedLocation, SaleTransaction $saleTransaction, int $locationId)
    {
        if (!array_key_exists($locationId, $mappedLocation)) {
            $mappedLocation[$locationId] = Location::find($locationId);
        }

        $saleTransaction->location_id = $locationId;
        $saleTransaction->location_name = $mappedLocation[$locationId]->name;
        $saleTransaction->location_initial = $mappedLocation[$locationId]->initial;
        $saleTransaction->location_timezone = $mappedLocation[$locationId]->timezone ?? 'UTC';
    }

    public static function orderTypeHeader(array $mappedOrderType, SaleTransaction $saleTransaction, int $orderTypeId)
    {
        if (!array_key_exists($orderTypeId, $mappedOrderType)) {
            $mappedOrderType[$orderTypeId] = OrderType::find($orderTypeId);
        }

        $saleTransaction->order_type_id = $orderTypeId;
        $saleTransaction->order_type_name = $mappedOrderType[$orderTypeId]->name;
    }
}
