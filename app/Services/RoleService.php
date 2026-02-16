<?php

namespace App\Services;

use App\Models\Role;

class RoleService
{
    public function getRoles()
    {
        $search = request('search', '');

        return Role::whereLike('name', "%$search%")
        ->paginate(request('per_page', 10))
        ->withQueryString();
    }

    public function store(array $data, int $userId)
    {
        return Role::create([
            'name' => $data['name'],
            'tier' => 1,
            'level' => 1,
            'entity_permission' => [],
            'location_permission' => [],
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function update(Role $role, array $data, int $userId)
    {
        return $role->update([
            'name' => $data['name'],
            'updated_by' => $userId,
        ]);
    }

    public function delete(Role $role)
    {
        return $role->delete();
    }
}
