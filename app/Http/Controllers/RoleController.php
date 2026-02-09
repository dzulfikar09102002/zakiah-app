<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Illuminate\Container\Attributes\Auth;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::paginate(10);
        return Inertia::render('roles/index', compact('roles'));
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request)
    {       
        $validated = $request->validated();
    Role::create([
        'name' => $validated['name'],
        'tier' => 1,
        'level' => 1,
        'entity_permission'=>[],
        'location_permission' => [],
        'created_by'=>auth()->id(),
        'updated_by'=>auth()->id()
    ]);

    return redirect()->back()->with('success', 'Role berhasil ditambahkan');
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $validated = $request->validated();
        $role->update([
            'name' => $validated['name'],
            'updated_by' => auth()->id()
        ]);
        return redirect()->back()->with('success', 'Role berhasil diperbarui');
    }
    public function destroy(Role $role)
    {
        $role -> delete();
        return redirect()->back()->with('success', 'Role berhasil dihapus');
    }
}
