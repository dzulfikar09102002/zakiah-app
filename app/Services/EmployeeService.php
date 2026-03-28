<?php

namespace App\Services;

use App\Helpers\UniqueCodeGenerator;
use App\Models\Employee;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EmployeeService
{
    public function getEmployees()
    {
        $entityId = auth()->user()?->entity?->id;
        return Employee::with([
                'role:id,name',
                'user:id,email',
                'employeeLocations:id,employee_id,location_id,role_id,entity_permission'
            ])
            ->where('entity_id', $entityId)
            ->when(request('search'), fn(Builder $query, $search) => 
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
                })
            )
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }
    public function getRoles()
    {
        return Role::select('id', 'name')
    ->whereNotNull('parent_id')
    ->get();
    }

    public function getLocations()
    {
        $entityId = auth()->user()?->entity?->id;
        return Location::select('id', 'name')->where('entity_id', $entityId)->get();
    }

    public function store(array $data)
    { 
        $entity = auth()->user()?->entity;

        return DB::transaction(function () use ($data, $entity) {

            $user = User::where('email', $data['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $data['first_name'] . ' ' . $data['last_name'],
                    'email' => $data['email'],
                    'password' => bcrypt($data['password'] ?? '12345678'),
                ]);
            }

            if (!empty($data['password'])) {
                $user->password = bcrypt($data['password']);
                $user->save();
            }

            $employee = new Employee();
            $employee->entity_id = $entity->id;
            $employee->user_id = $user->id;
            $employee->code = UniqueCodeGenerator::generateCode();
            $employee->fill($data);

            $role = Role::find($data['role_id']);

            if (array_key_exists('entity_permission', $data)) {
                $employee->entity_permission = array_merge($data['entity_permission'], $role->entity_permission);
            } else {
                $employee->entity_permission = $role->entity_permission;
            }

            if (array_key_exists('location_permission', $data)) {
                $employee->location_permission = array_merge($data['location_permission'], $role->location_permission);
            } else {
                $employee->location_permission = $role->location_permission;
            }

            $employee->save();

            if (!empty($data['locations'])) {

                $employeeLocations = [];

                foreach ($data['locations'] as $location) {

                    $role = Role::find($location['role_id']);

                    $employeeLocations[] = [
                        'location_id' => $location['location_id'],
                        'role_id' => $location['role_id'],
                        'code' => UniqueCodeGenerator::generateCode(),
                        'pin' => '1234',
                        'entity_permission' => array_merge($role->entity_permission, $location['entity_permission'] ?? []),
                        'location_permission' => array_merge($role->location_permission, $location['location_permission'] ?? []),
                    ];
                }

                $employee->employeeLocations()->createMany($employeeLocations);
            }

            return $employee;
        });
    }

    public function update(Employee $employee, array $data)
    {
        return DB::transaction(function () use ($employee, $data) {

            $user = $employee->user;

            // Update basic info
            $user->name = $data['first_name'] . ' ' . $data['last_name'];
            $user->email = $data['email'];

            // Update password kalau diisi
            if (!empty($data['password'])) {
                $user->password = bcrypt($data['password']);
            }

            $user->save();

            $employee->fill($data);

            $role = Role::find($data['role_id']);

            // entity_permission
            if (array_key_exists('entity_permission', $data)) {
                $employee->entity_permission = array_merge(
                    $data['entity_permission'],
                    $role->entity_permission
                );
            } else {
                $employee->entity_permission = $role->entity_permission;
            }

            // location_permission
            if (array_key_exists('location_permission', $data)) {
                $employee->location_permission = array_merge(
                    $data['location_permission'],
                    $role->location_permission
                );
            } else {
                $employee->location_permission = $role->location_permission;
            }

            $employee->save();

            if (isset($data['locations'])) {

                $employee->employeeLocations()->delete();

                $employeeLocations = [];

                foreach ($data['locations'] as $location) {

                    $role = Role::find($location['role_id']);

                    $employeeLocations[] = [
                        'location_id' => $location['location_id'],
                        'role_id' => $location['role_id'],
                        'code' => UniqueCodeGenerator::generateCode(),
                        'pin' => '1234',
                        'entity_permission' => array_merge(
                            $role->entity_permission,
                            $location['entity_permission'] ?? []
                        ),
                        'location_permission' => array_merge(
                            $role->location_permission,
                            $location['location_permission'] ?? []
                        ),
                    ];
                }

                $employee->employeeLocations()->createMany($employeeLocations);
            }

            return $employee->fresh(['user', 'employeeLocations']);
        });
    }

    public function delete(Employee $employee)
    {
        return $employee->delete();
    }
    public function getDeletedEmployees()
    {
        $entityId = auth()->user()?->entity?->id;
        return Employee::with([
                'role:id,name',
                'user:id,email',
                'employeeLocations:id,employee_id,location_id,role_id,entity_permission'
            ])->onlyTrashed()
            ->where('entity_id', $entityId)
            ->when(request('search'), fn(Builder $query, $search) => 
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
                })
            )
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }
    public function restore(int $id)
    {
        $employees = Employee::withTrashed()->findOrFail($id);

        return $employees->restore();
    }
}
