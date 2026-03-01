<?php

namespace App\Services;

use App\Models\Role;
use Exception;
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

    public function getParents()
    {
        return Role::whereNull('entity_id')->get();
    }
    public function store(array $data)
{
    $user = auth()->user();

    $parent = Role::findOrFail((int) $data['parent_id']);

    return Role::create([
            ...$data,

            'entity_id' => $user->entity->id,
            'tier' => $parent->tier,
            'level' => $parent->level + 1,

            'entity_permission' => array_merge(
                $parent->entity_permission ?? [],
                $data['entity_permission'] ?? []
            ),

            'location_permission' => array_merge(
                $parent->location_permission ?? [],
                $data['location_permission'] ?? []
            ),

            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }
    public function update(Role $role, array $data)
{
    if ($role->entity_id === null) {
        throw new Exception('Parent role cannot be updated.');
    }

    $parent = Role::findOrFail((int) $data['parent_id']);

    return tap($role)->update([
        ...$data,

        'tier' => $parent->tier,
        'level' => $parent->level + 1,

        'entity_permission' => array_merge(
            $parent->entity_permission ?? [],
            $data['entity_permission'] ?? []
        ),

        'location_permission' => array_merge(
            $parent->location_permission ?? [],
            $data['location_permission'] ?? []
        ),

        'updated_by' => auth()->id(),
        ]);
    }

    public function delete(Role $role)
    {
        return $role->delete();
    }
}
