<?php

namespace App\Services;

use App\Helpers\UniqueCodeGenerator;
use App\Models\Employee;
use App\Models\EmployeeLocation;
use App\Models\Location;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Karyawan = data kerja (nama, toko, role). Akun login (email, password) diurus terpisah
 * di modul Akun Login, tapi tiap karyawan tetap punya satu baris users karena kolom
 * employees.user_id wajib diisi.
 *
 * Satu orang = satu karyawan per entity, dengan banyak penugasan (toko + role) di
 * employee_locations. Data lama yang terlanjur dobel (satu akun, dua karyawan) dibiarkan
 * apa adanya; POS tetap membacanya seperti sebelumnya.
 */
class EmployeeService
{
    public function __construct(
        private EmployeeRoleCatalog $roles,
        private MenuAccessService $access,
    ) {}

    public function getEmployees(bool $onlyTrashed = false): LengthAwarePaginator
    {
        $entityId = $this->entityId();

        $pagination = Employee::query()
            ->with([
                'user:id,name,email',
                'employeeLocations' => fn ($query) => $query
                    ->select('id', 'employee_id', 'location_id', 'role_id')
                    ->whereHas('location', fn ($location) => $location->where('entity_id', $entityId)),
            ])
            ->when($onlyTrashed, fn (Builder $query) => $query->onlyTrashed())
            ->where('entity_id', $entityId)
            ->when(request('search'), fn (Builder $query, $search) => $query->where(function (Builder $query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            }))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(request('per_page', 10))
            ->withQueryString();

        $sharedUsers = $this->usersWithManyEmployees($pagination->getCollection()->pluck('user_id')->all());
        $locations = $this->getLocations()->pluck('name', 'id');
        $accessRoles = $this->access->accessRoleOptions()?->pluck('label', 'value');

        $pagination->setCollection($pagination->getCollection()->map(fn (Employee $employee) => [
            'id' => $employee->id,
            'user_id' => $employee->user_id,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'name' => trim($employee->first_name.' '.$employee->last_name),
            'role_id' => $employee->role_id,
            'role_label' => $this->roles->label($this->roleName($employee->role_id)),
            'access_role_id' => $employee->access_role_id ? (int) $employee->access_role_id : null,
            'access_role_label' => $accessRoles?->get($employee->access_role_id),
            'deleted_at' => $employee->deleted_at,
            'has_login' => filled($employee->user?->email),
            'email' => $employee->user?->email,
            'shares_account' => in_array((int) $employee->user_id, $sharedUsers, true),
            'assignments' => $this->assignments($employee->employeeLocations, $locations),
        ]));

        return $pagination;
    }

    /**
     * @return Collection<int, object{id: int, name: string}>
     */
    public function getLocations(): Collection
    {
        return Location::query()
            ->select('id', 'name')
            ->where('entity_id', $this->entityId())
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getRoleOptions(): Collection
    {
        return $this->roles->options();
    }

    /**
     * @return Collection<int, array{value: int, label: string}>|null
     */
    public function getAccessRoleOptions(): ?Collection
    {
        return $this->access->accessRoleOptions();
    }

    /**
     * @param  array{first_name: string, last_name?: string|null, assignments: array<int, array{location_id: int, role_id: int}>}  $data
     */
    public function store(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            $name = $this->fullName($data);

            // Akun tanpa email: tidak bisa dipakai login sampai diaktifkan di modul Akun Login.
            $user = User::query()->create([
                'name' => $name,
                'email' => null,
                'password' => Str::random(40),
            ]);

            $employee = new Employee;
            $employee->entity_id = $this->entityId();
            $employee->user_id = $user->id;
            $employee->code = UniqueCodeGenerator::generateCode();
            $employee->select_all_location = false;
            $employee->created_by = auth()->id();

            $this->fillWorkData($employee, $data);
            $employee->save();

            $this->syncAssignments($employee, $data['assignments']);

            return $employee;
        });
    }

    /**
     * Akun login tidak disentuh di sini; nama, toko, dan role saja.
     *
     * @param  array{first_name: string, last_name?: string|null, assignments: array<int, array{location_id: int, role_id: int}>}  $data
     */
    public function update(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $this->fillWorkData($employee, $data);
            $employee->save();

            $this->syncAssignments($employee, $data['assignments']);

            return $employee;
        });
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }

    public function restore(int $id): void
    {
        Employee::withTrashed()
            ->where('entity_id', $this->entityId())
            ->findOrFail($id)
            ->restore();
    }

    /**
     * Penugasan disamakan dengan isian form tanpa membuat ulang baris yang sudah ada:
     * kode dan id baris lama tetap, yang dihapus di-soft-delete, yang baru ditambahkan.
     * Baris lama yang terlanjur dobel (toko + role sama) tidak diubah.
     *
     * @param  array<int, array{location_id: int, role_id: int}>  $assignments
     */
    private function syncAssignments(Employee $employee, array $assignments): void
    {
        $wanted = collect($assignments)
            ->map(fn (array $row) => ['location_id' => (int) $row['location_id'], 'role_id' => (int) $row['role_id']])
            ->unique(fn (array $row) => $row['location_id'].'-'.$row['role_id'])
            ->keyBy(fn (array $row) => $row['location_id'].'-'.$row['role_id']);

        $existing = $employee->employeeLocations()
            ->whereHas('location', fn ($query) => $query->where('entity_id', $this->entityId()))
            ->get();

        foreach ($existing as $row) {
            if (! $wanted->has($row->location_id.'-'.$row->role_id)) {
                $row->updated_by = auth()->id();
                $row->save();
                $row->delete();
            }
        }

        $present = $existing->map(fn (EmployeeLocation $row) => $row->location_id.'-'.$row->role_id)->all();

        foreach ($wanted->except($present) as $row) {
            $role = $this->roles->find($row['role_id']);

            $employee->employeeLocations()->create([
                'location_id' => $row['location_id'],
                'role_id' => $row['role_id'],
                'code' => UniqueCodeGenerator::generateCode(),
                // PIN tidak dipakai NewZakicaPOS; nilai bawaan aplikasi lama tetap diisi.
                'pin' => '1234',
                'entity_permission' => $role?->entity_permission ?? [],
                'location_permission' => $role?->location_permission ?? [],
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Role utama dan salinan permission diisi dari penugasan, karena POS membaca
     * employees.role_id untuk izin kasir/backoffice.
     *
     * @param  array{first_name: string, last_name?: string|null, assignments: array<int, array{location_id: int, role_id: int}>}  $data
     */
    private function fillWorkData(Employee $employee, array $data): void
    {
        $primary = $this->roles->primary(array_map(fn (array $row) => (int) $row['role_id'], $data['assignments']));

        $employee->first_name = trim($data['first_name']);
        $employee->last_name = trim((string) ($data['last_name'] ?? ''));
        $employee->role_id = $primary->id;
        $employee->entity_permission = $primary->entity_permission ?? [];
        $employee->location_permission = $primary->location_permission ?? [];
        $employee->updated_by = auth()->id();

        // Kolom access_role_id baru ada setelah SQL menu dijalankan.
        if ($this->access->available() && array_key_exists('access_role_id', $data)) {
            $employee->access_role_id = $data['access_role_id'];
        }
    }

    /**
     * @param  Collection<int, EmployeeLocation>  $rows
     * @param  Collection<int, string>  $locations
     * @return array<int, array{location_id: int, location: string, roles: array<int, array{role_id: int, label: string}>}>
     */
    private function assignments(Collection $rows, Collection $locations): array
    {
        return $rows
            ->groupBy('location_id')
            ->map(fn (Collection $group, $locationId) => [
                'location_id' => (int) $locationId,
                'location' => (string) ($locations[$locationId] ?? '-'),
                'roles' => $group->unique('role_id')->sortBy('role_id')->map(fn (EmployeeLocation $row) => [
                    'role_id' => (int) $row->role_id,
                    'label' => $this->roles->label($this->roleName($row->role_id)),
                ])->values()->all(),
            ])
            ->sortBy('location')
            ->values()
            ->all();
    }

    private function roleName(?int $roleId): ?string
    {
        return once(fn () => Role::query()->pluck('name', 'id'))[$roleId] ?? null;
    }

    /**
     * Akun yang dipakai lebih dari satu karyawan aktif di entity ini (data lama).
     *
     * @param  array<int, int>  $userIds
     * @return array<int, int>
     */
    private function usersWithManyEmployees(array $userIds): array
    {
        if ($userIds === []) {
            return [];
        }

        return Employee::query()
            ->where('entity_id', $this->entityId())
            ->whereIn('user_id', $userIds)
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * @param  array{first_name: string, last_name?: string|null}  $data
     */
    private function fullName(array $data): string
    {
        return trim(trim($data['first_name']).' '.trim((string) ($data['last_name'] ?? '')));
    }

    private function entityId(): ?int
    {
        return auth()->user()?->entity?->id;
    }
}
