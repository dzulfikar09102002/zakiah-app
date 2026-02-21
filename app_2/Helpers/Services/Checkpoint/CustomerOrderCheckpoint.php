<?php

namespace App\Helpers\Services\Checkpoint;

use App\Models\CustomerOrder;
use App\Models\Device;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CustomerOrderCheckpoint
{
    protected Device $device;
    protected Location $location;

    /**
     * Create a new class instance.
     */
    public function __construct(Device $device, Location $location)
    {
        //
        $this->device = $device;
        $this->location = $location;
    }

    public function checkpoint()
    {
        $deviceIds = $this->deviceIds();

        CustomerOrder::inprogress()
            ->where('location_id', $this->location->id)
            ->where(function (Builder $builder) use($deviceIds) {
                $builder->whereIn('checkpoint_device_id', $deviceIds)->orWhere('checkpoint_device_id', null);
            })
            ->update(['checkpoint_device_id' => $this->device->id]);
    }

    protected function deviceIds(): array
    {
        $deviceIds = array_diff($this->allDeviceIds()->all(), $this->activeDeviceIds()->all());
        array_push($deviceIds, $this->device->id);

        return $deviceIds;
    }

    protected function allDeviceIds(): Collection
    {
        return Device::withTrashed()->select(['id'])->where('location_id', $this->location->id)->pluck('id');
    }

    protected function activeDeviceIds(): Collection
    {
        return Device::select(['id'])->where('location_id', $this->location->id)->pluck('id');
    }
}
