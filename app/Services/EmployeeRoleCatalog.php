<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Collection;

/**
 * Role template sistem (entity_id dan parent_id kosong, level 1).
 *
 * Penugasan karyawan per toko hanya memakai role ini, karena POS (pos-secaca lama dan
 * NewZakicaPOS) mencari Sales lewat id template Sales dan menentukan role utama dari
 * urutan id template. Role turunan per entity tidak dikenali POS.
 */
class EmployeeRoleCatalog
{
    /** @var array<string, string> */
    private const LABELS = [
        'entity owner' => 'Pemilik Usaha',
        'location owner' => 'Kepala Toko',
        'cashier' => 'Kasir',
        'warehouse' => 'Gudang',
        'sales' => 'Sales',
    ];

    /**
     * @return Collection<int, Role>
     */
    public function templates(): Collection
    {
        return once(fn () => Role::query()
            ->whereNull('entity_id')
            ->whereNull('parent_id')
            ->where('level', 1)
            ->orderBy('id')
            ->get(['id', 'name', 'allow_pos', 'allow_backoffice', 'entity_permission', 'location_permission']));
    }

    /**
     * @return array<int, int>
     */
    public function templateIds(): array
    {
        return $this->templates()->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * @return Collection<int, array{value: int, label: string, allow_pos: bool, allow_backoffice: bool}>
     */
    public function options(): Collection
    {
        return $this->templates()->map(fn (Role $role) => [
            'value' => (int) $role->id,
            'label' => $this->label($role->name),
            'allow_pos' => (bool) $role->allow_pos,
            'allow_backoffice' => (bool) $role->allow_backoffice,
        ])->values();
    }

    public function label(?string $name): string
    {
        return self::LABELS[strtolower(trim((string) $name))] ?? (string) $name;
    }

    /**
     * Role utama karyawan: id template terkecil, sama dengan urutan NewZakicaPOS saat login
     * (Pemilik Usaha, Kepala Toko, Kasir, Gudang, Sales). Kolom ini yang dibaca POS untuk
     * menentukan boleh masuk kasir atau backoffice.
     *
     * @param  array<int, int>  $roleIds
     */
    public function primary(array $roleIds): Role
    {
        return $this->templates()
            ->whereIn('id', $roleIds)
            ->sortBy('id')
            ->firstOrFail();
    }

    public function find(int $roleId): ?Role
    {
        return $this->templates()->firstWhere('id', $roleId);
    }
}
