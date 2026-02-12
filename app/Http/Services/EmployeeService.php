<?php

namespace App\Http\Services;

use App\Models\Employee;
use App\Models\Role;
use App\Models\Location;

class EmployeeService
{
    public function paginateByEntity(int $entityId, int $perPage = 10)
    {
        $employees = Employee::where('entity_id', $entityId)
            ->paginate($perPage)
            ->withQueryString();

        // Ambil roles sekali (biar nggak N+1)
        $roles = Role::select('id', 'name')->get()->keyBy('id');

        // Inject role_name ke setiap employee
        $employees->getCollection()->transform(function ($employee) use ($roles) {
            $employee->role_name = $roles[$employee->role_id]->name ?? '-';
            return $employee;
        });

        return $employees;
    }

    public function getFormOptions()
    {
        return [
            'roles' => Role::select('id', 'name')->get(),
            'locations' => Location::select('id', 'name')->get(),
        ];
    }

    public function store(array $data, int $entityId, int $userId)
    {
        return Employee::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'role_id' => $data['role_id'],
            'location_id' => $data['location_id'] ?? null,
            'entity_id' => $entityId,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function update(Employee $employee, array $data, int $userId)
    {
        return $employee->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'role_id' => $data['role_id'],
            'location_id' => $data['location_id'] ?? null,
            'updated_by' => $userId,
        ]);
    }

    public function delete(Employee $employee)
    {
        return $employee->delete();
    }
}
