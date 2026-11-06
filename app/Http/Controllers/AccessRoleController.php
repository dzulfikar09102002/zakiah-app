<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccessRolePermissionRequest;
use App\Http\Requests\AccessRoleRequest;
use App\Services\AccessRoleService;
use App\Services\MenuAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Role backoffice (tabel access_roles): CRUD nama, lalu tombol Hak Akses terpisah untuk
 * mencentang menu dan aksi. Tab Terhapus memakai ?status=deleted di route index.
 */
class AccessRoleController extends Controller
{
    public function __construct(
        private AccessRoleService $service,
        private MenuAccessService $access,
    ) {}

    public function index(Request $request): Response
    {
        $this->ensureAvailable();

        $onlyTrashed = $request->query('status') === 'deleted';

        return Inertia::render('access-roles/index', [
            'accessRoles' => $this->service->list($onlyTrashed),
            'matrix' => $onlyTrashed ? [] : $this->service->matrix(),
            'onlyTrashed' => $onlyTrashed,
        ]);
    }

    public function store(AccessRoleRequest $request): RedirectResponse
    {
        $this->ensureAvailable();

        $this->service->store($request->validated());

        return back()->with('success', 'Role ditambahkan, atur Hak Aksesnya lewat tombol Hak Akses');
    }

    public function update(AccessRoleRequest $request, int $id): RedirectResponse
    {
        $this->ensureAvailable();

        $this->service->update($this->service->find($id), $request->validated());

        return back()->with('success', 'Role diperbarui');
    }

    public function permissions(AccessRolePermissionRequest $request, int $id): RedirectResponse
    {
        $this->ensureAvailable();

        $this->service->updatePermissions($this->service->find($id), $request->validated('action_ids'));

        return back()->with('success', 'Hak Akses disimpan');
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->ensureAvailable();

        $this->service->delete($this->service->find($id));

        return back()->with('success', 'Role dihapus');
    }

    public function restore(int $id): RedirectResponse
    {
        $this->ensureAvailable();

        $this->service->restore($id);

        return back()->with('success', 'Role dipulihkan');
    }

    private function ensureAvailable(): void
    {
        abort_unless($this->access->available(), 404, 'Tabel menu belum dibuat. Jalankan database/sql/2026_11_06_create_menu_access_tables.sql.');
    }
}
