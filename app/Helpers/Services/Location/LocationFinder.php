<?php

namespace App\Helpers\Services\Location;

use App\Models\Employee;
use App\Models\Location;

class LocationFinder
{
    private Employee $employee;
    private int $id;

    /**
     * Create a new class instance.
     */
    public function __construct(Employee $employee, int $id)
    {
        //
        $this->employee = $employee;
        $this->id = $id;
    }

    public function get(): ?Location
    {
        $locationIds = $this->employee->employeeLocations()->select(['location_id'])->pluck('location_id')->all();

        return Location::whereIn('id', $locationIds)->where('id', $this->id)->first();
    }
}
