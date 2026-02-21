<?php

namespace App\Helpers\Services\Checkpoint;

use App\Models\Device;
use App\Models\Location;
use App\Models\SaleTransaction;
use Illuminate\Database\Eloquent\Builder;

class SaleTransactionCheckpoint extends CustomerOrderCheckpoint
{
    /**
     * Create a new class instance.
     */
    public function __construct(Device $device, Location $location)
    {
        //
        parent::__construct($device, $location);
    }

    public function checkpoint()
    {
        $deviceIds = $this->deviceIds();

        SaleTransaction::where('taking_id', null)
            ->where('location_id', $this->location->id)
            ->where(function (Builder $builder) use($deviceIds) {
                $builder->whereIn('checkpoint_device_id', $deviceIds)->orWhere('checkpoint_device_id', null);
            })
            ->update(['checkpoint_device_id' => $this->device->id]);
    }
}
