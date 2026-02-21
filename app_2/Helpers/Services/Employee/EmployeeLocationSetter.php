<?php

namespace App\Helpers\Services\Employee;

use App\Helpers\UniqueCodeGenerator;
use App\Models\Employee;
use App\Models\EmployeeLocation;
use App\Models\Location;
use App\Models\Role;

class EmployeeLocationSetter
{
    private Location $location;

    /**
     * Create a new class instance.
     */
    public function __construct(Location $location)
    {
        //
        $this->location = $location;
    }

    public function create()
    {
        $employees = Employee::where('entity_id', $this->location->entity_id)->where('select_all_location', true)->get();

        foreach($employees as $employee)
        {
            $employeeLocation = new EmployeeLocation();
            $employeeLocation->code = UniqueCodeGenerator::generateCode();
            $employeeLocation->location_id = $this->location->id;
            $employeeLocation->employee_id = $employee->id;
            $employeeLocation->role_id = $employee->role_id;

            $employeeLocation->pin = '1234';

            $role = Role::find($employeeLocation->role_id);
            $employeeLocation->entity_permission = $role->entity_permission;
            $employeeLocation->location_permission = $role->location_permission;

            $employeeLocation->save();
        }
    }
}
