<?php

namespace App\Helpers\Services\Employee;

use App\Helpers\UniqueCodeGenerator;
use App\Models\Employee;
use App\Models\Entity;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmployeeSaver
{
    private Entity $entity;
    private Employee $employee;
    private array $params;

    /**
     * Create a new class instance.
     */
    public function __construct(Entity $entity, Employee $employee, array $params)
    {
        //
        $this->entity = $entity;
        $this->employee = $employee;
        $this->params = $params;
    }

    public function create(): Employee
    {
        $employee = $this->employee;

        DB::transaction(function () use ($employee) {
            $email = $this->params['email'];
            $firstName = $this->params['first_name'];
            $lastName = $this->params['last_name'];

            $user = User::where('email', $email)->first();
            if ($user == null) {
                $user = User::create([
                    'name' => "$firstName $lastName",
                    'email' => $email,
                    'password' => '12345678'
                ]);
            }

            if (array_key_exists('new_password', $this->params)) {
                $user->password = $this->params['new_password'];
                $user->save();
            }

            # not in fillable
            $employee->entity_id = $this->entity->id;
            $employee->user_id = $user->id;
            $employee->code = UniqueCodeGenerator::generateCode();
            $employee->fill($this->params);

            $role = Role::find($this->params['role_id']);
            $this->setPermission($employee, $role, $this->params);
            
            $employee->save();
    
            if (array_key_exists('locations', $this->params)) {
                $employeeLocations = [];
                foreach ($this->params['locations'] as $location)
                {
                    $employeeLocation = $location;
                    $employeeLocation['code'] = UniqueCodeGenerator::generateCode();

                    $role = Role::find($location['role_id']);
                    $employeeLocation['pin'] = '1234';
                    $employeeLocation['entity_permission'] = array_merge($role->entity_permission, $location['entity_permission'] ?? array());
                    $employeeLocation['location_permission'] = array_merge($role->location_permission, $location['location_permission'] ?? array());

                    array_push($employeeLocations, $employeeLocation);
                }

                $employee->employeeLocations()->createMany($employeeLocations);
            }
        });
           
        return $employee;
    }

    private function setPermission(Employee &$employee, Role $role, array $params) {
        if (array_key_exists('entity_permission', $params)) {
            $employee->entity_permission = array_merge($params['entity_permission'], $role->entity_permission);
        } else {
            $employee->entity_permission = $role->entity_permission;
        }

        if (array_key_exists('location_permission', $params)) {
            $employee->location_permission = array_merge($params['location_permission'], $role->location_permission);
        } else {
            $employee->location_permission = $role->location_permission;
        }
    }
}
