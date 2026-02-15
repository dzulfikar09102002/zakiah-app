<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use App\Models\Role;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function __construct(
        protected RoleService $service
    ) {}

    public function index()
    {
        $pagination = $this->service->getRoles();
        return Inertia::render('roles/index', compact('pagination'));
    }

    public function store(StoreRoleRequest $request)
    {
        $this->service->store(
            $request->validated(),
            auth()->id()
        );

        return redirect()->back()->with('success', 'Role berhasil ditambahkan');
    }

    public function update(UpdateRoleRequest $request, Role $role)
    {
        $this->service->update(
            $role,
            $request->validated(),
            auth()->id()
        );

        return redirect()->back()->with('success', 'Role berhasil diperbarui');
    }

    public function destroy(Role $role)
    {
        $this->service->delete($role);

        return redirect()->back()->with('success', 'Role berhasil dihapus');
    }
}
