<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Location;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;

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
            ->withTrashed()
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
        return Role::select('id', 'name')->get();
    }

    public function getLocations()
    {
        return Location::select('id', 'name')->get();
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
