<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;

class RoleService
{
    public function getRoles()
    {
        $search = request('search');
        $entityId = auth()->user()?->entity?->id;
        return Role::with('parentRole:id,name')
            ->when($search, fn ($query) =>
                $query->whereLike('name', "%{$search}%")
            )
    
            ->when(request('parent_ids'), fn ($query, $parentIds) =>
                $query->whereIn('parent_id', (array) $parentIds)
            )
    
            ->when(request('show_system') === 'true',
                function ($query) use ($entityId) {
                    $query->where(function (Builder $q) use ($entityId) {
                        $q->where('entity_id', $entityId)
                          ->orWhereNull('entity_id');
                    });
                },
                function ($query) use ($entityId) {
                    $query->where('entity_id', $entityId);
                }
            )
    
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
