<?php

namespace App\Services;

use App\Models\AccessRole;
use App\Models\Employee;
use App\Models\MenuHeader;
use App\Models\MenuHeaderDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Hak Akses backoffice per entity: nama + centangan aksi menu.
 * Hak Akses bawaan (is_system) dipakai otomatis oleh karyawan yang belum diberi Hak Akses,
 * sesuai role utamanya; centangannya boleh diubah, tapi tidak bisa dihapus.
 */
class AccessRoleService
{
    public function __construct(
        private EmployeeRoleCatalog $roles,
    ) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function list(bool $onlyTrashed = false): Collection
    {
        $entityId = $this->entityId();

        $explicit = Employee::query()
            ->where('entity_id', $entityId)
            ->whereNotNull('access_role_id')
            ->groupBy('access_role_id')
            ->selectRaw('access_role_id, COUNT(*) as total')
            ->pluck('total', 'access_role_id');

        $byDefault = Employee::query()
            ->where('entity_id', $entityId)
            ->whereNull('access_role_id')
            ->groupBy('role_id')
            ->selectRaw('role_id, COUNT(*) as total')
            ->pluck('total', 'role_id');

        return AccessRole::query()
            ->when($onlyTrashed, fn ($query) => $query->onlyTrashed())
            ->where('entity_id', $entityId)
            ->withCount('actions')
            ->with('actions:id')
            ->orderByDesc('is_system')
            ->orderBy('default_for_role_id')
            ->orderBy('name')
            ->get()
            ->map(fn (AccessRole $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'note' => $role->note,
                'is_system' => $role->is_system,
                'is_active' => $role->is_active,
                'default_for' => $role->default_for_role_id
                    ? $this->roles->label($this->roles->find($role->default_for_role_id)?->name)
                    : null,
                'employees_count' => (int) ($explicit[$role->id] ?? 0)
                    + (int) ($role->default_for_role_id ? ($byDefault[$role->default_for_role_id] ?? 0) : 0),
                'actions_count' => $role->actions_count,
                'action_ids' => $role->actions->pluck('id')->map(fn ($id) => (int) $id)->values(),
            ]);
    }

    /**
     * Matriks centang: header > baris sub menu (dengan nama group) > aksi.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function matrix(): Collection
    {
        $details = MenuHeaderDetail::query()
            ->with(['submenu.actions', 'group'])
            ->orderBy('seq')
            ->get()
            ->groupBy('menu_header_id');

        return MenuHeader::query()
            ->orderByRaw("CASE WHEN section = 'setting' THEN 1 ELSE 0 END")
            ->orderBy('seq')
            ->get()
            ->map(fn (MenuHeader $header) => [
                'id' => $header->id,
                'name' => $header->name,
                'rows' => $details->get($header->id, collect())
                    ->filter(fn (MenuHeaderDetail $detail) => $detail->submenu !== null)
                    ->map(fn (MenuHeaderDetail $detail) => [
                        'submenu_id' => $detail->submenu->id,
                        'name' => $detail->submenu->name,
                        'group' => $detail->group?->name,
                        'actions' => $detail->submenu->actions->map(fn ($action) => [
                            'id' => (int) $action->id,
                            'code' => $action->code,
                            'name' => $action->name,
                        ])->values(),
                    ])->values(),
            ])
            ->filter(fn (array $header) => $header['rows']->isNotEmpty())
            ->values();
    }

    /**
     * Role baru belum punya centangan; diatur lewat tombol Hak Akses.
     *
     * @param  array{name: string, note?: string|null, is_active?: bool}  $data
     */
    public function store(array $data): AccessRole
    {
        return AccessRole::query()->create([
            'entity_id' => $this->entityId(),
            'name' => trim($data['name']),
            'note' => $data['note'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'is_system' => false,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * @param  array{name: string, note?: string|null, is_active?: bool}  $data
     */
    public function update(AccessRole $role, array $data): void
    {
        $role->name = trim($data['name']);
        $role->note = $data['note'] ?? null;
        // Role bawaan selalu aktif: dipakai otomatis oleh karyawan tanpa role.
        $role->is_active = $role->is_system ? true : ($data['is_active'] ?? true);
        $role->updated_by = auth()->id();
        $role->save();
    }

    /**
     * Simpan centangan menu dan aksi (tombol Hak Akses).
     *
     * @param  array<int, int>  $actionIds
     */
    public function updatePermissions(AccessRole $role, array $actionIds): void
    {
        DB::transaction(function () use ($role, $actionIds) {
            $role->updated_by = auth()->id();
            $role->save();

            $this->syncActions($role, $actionIds);
        });
    }

    public function restore(int $id): void
    {
        $role = AccessRole::onlyTrashed()
            ->where('entity_id', $this->entityId())
            ->findOrFail($id);

        $taken = AccessRole::query()
            ->where('entity_id', $role->entity_id)
            ->where('name', $role->name)
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages([
                'access_role' => "Sudah ada role aktif bernama {$role->name}. Ganti dulu namanya.",
            ]);
        }

        $role->restore();
    }

    public function delete(AccessRole $role): void
    {
        if ($role->is_system) {
            throw ValidationException::withMessages(['access_role' => 'Hak Akses bawaan tidak bisa dihapus.']);
        }

        $used = Employee::query()->where('access_role_id', $role->id)->count();

        if ($used > 0) {
            throw ValidationException::withMessages([
                'access_role' => "Hak Akses ini masih dipakai {$used} karyawan. Ganti dulu Hak Akses mereka.",
            ]);
        }

        $role->updated_by = auth()->id();
        $role->save();
        $role->delete();
    }

    public function find(int $id): AccessRole
    {
        return AccessRole::query()
            ->where('entity_id', $this->entityId())
            ->findOrFail($id);
    }

    /**
     * @param  array<int, int>  $actionIds
     */
    private function syncActions(AccessRole $role, array $actionIds): void
    {
        $role->actions()->syncWithPivotValues(
            array_values(array_unique(array_map('intval', $actionIds))),
            ['created_by' => auth()->id(), 'created_at' => now()],
        );
    }

    private function entityId(): ?int
    {
        return auth()->user()?->entity?->id;
    }
}
