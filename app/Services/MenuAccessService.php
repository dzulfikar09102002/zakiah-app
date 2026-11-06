<?php

namespace App\Services;

use App\Models\AccessRole;
use App\Models\Employee;
use App\Models\MenuHeader;
use App\Models\MenuHeaderDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Menu dinamis dan Hak Akses backoffice.
 *
 * - Tabel dibuat manual (database/sql/2026_11_06_create_menu_access_tables.sql). Selama
 *   tabelnya belum ada, semua fitur ini mati dan aplikasi berjalan seperti sebelumnya.
 * - Pengecekan route hanya aktif bila ACCESS_CONTROL_ENFORCED=true, supaya centangan bisa
 *   dirapikan dulu tanpa ada yang terkunci.
 * - Route yang tidak terdaftar di menu_action_routes (profil, logout, dropdown bersama)
 *   bebas untuk semua yang login.
 * - Pemilik Usaha (role template Entity Owner) selalu lolos.
 * - Hak Akses karyawan = employees.access_role_id, atau Hak Akses bawaan entity untuk
 *   role utamanya (access_roles.default_for_role_id) bila kosong. Akun lama yang punya
 *   beberapa baris karyawan mendapat gabungan semuanya.
 */
class MenuAccessService
{
    public function __construct(
        private EmployeeRoleCatalog $roles,
    ) {}

    public function available(): bool
    {
        return once(function () {
            try {
                return Schema::hasTable('menu_action_routes')
                    && Schema::hasTable('access_roles')
                    && Schema::hasColumn('employees', 'access_role_id');
            } catch (Throwable) {
                return false;
            }
        });
    }

    public function enforced(): bool
    {
        return (bool) config('access.enforced') && $this->available();
    }

    public function canRoute(?string $routeName): bool
    {
        if (! $this->enforced() || $routeName === null || $this->isOwner()) {
            return true;
        }

        if (! $this->protectedRoutes()->has($routeName)) {
            return true;
        }

        return $this->allowedRoutes()->has($routeName);
    }

    /**
     * Kode aksi yang dimiliki user, mis. "employees.update". Pemilik Usaha dan saat
     * pengecekan belum aktif: ['*'].
     *
     * @return array<int, string>
     */
    public function permissions(): array
    {
        if (! $this->enforced() || $this->isOwner()) {
            return ['*'];
        }

        return once(fn () => DB::table('access_role_menu_actions as ram')
            ->join('menu_actions as a', 'a.id', '=', 'ram.menu_action_id')
            ->join('menu_submenus as s', 's.id', '=', 'a.menu_submenu_id')
            ->whereIn('ram.access_role_id', $this->accessRoleIds())
            ->get(['s.code as submenu', 'a.code as action'])
            ->map(fn (object $row) => $row->submenu.'.'.$row->action)
            ->unique()
            ->values()
            ->all());
    }

    /**
     * Sidebar dari database, sudah disaring sesuai Hak Akses.
     *
     * @return array{main: array<int, array<string, mixed>>, setting: array<int, array<string, mixed>>}|null
     */
    public function navigation(): ?array
    {
        if (! $this->available()) {
            return null;
        }

        $headers = MenuHeader::query()
            ->where('is_active', true)
            ->orderBy('seq')
            ->get();

        $details = MenuHeaderDetail::query()
            ->with(['submenu', 'group'])
            ->whereIn('menu_header_id', $headers->pluck('id'))
            ->orderBy('seq')
            ->get()
            ->filter(fn (MenuHeaderDetail $detail) => $detail->submenu?->is_active
                && ($detail->group === null || $detail->group->is_active))
            ->groupBy('menu_header_id');

        $sections = ['main' => [], 'setting' => []];

        foreach ($headers as $header) {
            $item = $this->headerItem($header, $details->get($header->id, collect()));

            if ($item !== null) {
                $sections[$header->section === 'setting' ? 'setting' : 'main'][] = $item;
            }
        }

        return $sections;
    }

    /**
     * Pilihan Hak Akses untuk form karyawan.
     *
     * @return Collection<int, array{value: int, label: string}>|null
     */
    public function accessRoleOptions(): ?Collection
    {
        if (! $this->available()) {
            return null;
        }

        return AccessRole::query()
            ->where('entity_id', $this->entityId())
            ->where('is_active', true)
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (AccessRole $role) => ['value' => (int) $role->id, 'label' => $role->name]);
    }

    public function isOwner(): bool
    {
        return once(function () {
            $owner = $this->roles->templates()
                ->first(fn ($role) => strtolower((string) $role->name) === 'entity owner');

            return $owner !== null && $this->employeeRows()->contains('role_id', $owner->id);
        });
    }

    /**
     * @return array<int, int>
     */
    public function accessRoleIds(): array
    {
        return once(function () {
            $rows = $this->employeeRows();

            if ($rows->isEmpty()) {
                return [];
            }

            $roles = AccessRole::query()
                ->where('entity_id', $this->entityId())
                ->where('is_active', true)
                ->where(fn ($query) => $query
                    ->whereIn('id', $rows->pluck('access_role_id')->filter())
                    ->orWhereIn('default_for_role_id', $rows->pluck('role_id')))
                ->get(['id', 'default_for_role_id']);

            return $rows
                ->map(fn (Employee $row) => $roles->firstWhere('id', $row->access_role_id)?->id
                    ?? $roles->firstWhere('default_for_role_id', $row->role_id)?->id)
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        });
    }

    /**
     * @param  Collection<int, MenuHeaderDetail>  $details
     * @return array<string, mixed>|null
     */
    private function headerItem(MenuHeader $header, Collection $details): ?array
    {
        // Header yang langsung membuka satu halaman (mis. Dashboard).
        if ($header->route_name) {
            return $this->visible($header->route_name)
                ? ['title' => $header->name, 'href' => $this->url($header->route_name), 'icon' => $header->icon, 'items' => []]
                : null;
        }

        $items = [];
        $groups = [];

        foreach ($details as $detail) {
            $route = $detail->submenu->route_name;

            if (! $this->visible($route)) {
                continue;
            }

            $link = ['title' => $detail->submenu->name, 'href' => $this->url($route), 'icon' => $detail->submenu->icon];

            if ($detail->group) {
                // Group ditempatkan di posisi sub menu pertamanya.
                if (! isset($groups[$detail->group->id])) {
                    $groups[$detail->group->id] = count($items);
                    $items[] = ['title' => $detail->group->name, 'href' => '#', 'icon' => $detail->group->icon, 'items' => []];
                }

                $items[$groups[$detail->group->id]]['items'][] = $link;
            } else {
                $items[] = $link;
            }
        }

        return $items === []
            ? null
            : ['title' => $header->name, 'href' => '#', 'icon' => $header->icon, 'items' => $items];
    }

    private function visible(string $routeName): bool
    {
        return Route::has($routeName) && $this->canRoute($routeName);
    }

    private function url(string $routeName): string
    {
        return route($routeName, [], false);
    }

    /**
     * @return Collection<string, bool>
     */
    private function protectedRoutes(): Collection
    {
        return once(fn () => DB::table('menu_action_routes')->pluck('route_name')->flip()->map(fn () => true));
    }

    /**
     * @return Collection<string, bool>
     */
    private function allowedRoutes(): Collection
    {
        return once(fn () => DB::table('access_role_menu_actions as ram')
            ->join('menu_action_routes as r', 'r.menu_action_id', '=', 'ram.menu_action_id')
            ->whereIn('ram.access_role_id', $this->accessRoleIds())
            ->pluck('r.route_name')
            ->flip()
            ->map(fn () => true));
    }

    /**
     * Semua baris karyawan aktif milik akun ini di entity yang sedang dipakai.
     *
     * @return Collection<int, Employee>
     */
    private function employeeRows(): Collection
    {
        return once(fn () => auth()->id() === null ? collect() : Employee::query()
            ->where('user_id', auth()->id())
            ->where('entity_id', $this->entityId())
            ->get(['id', 'role_id', 'access_role_id']));
    }

    private function entityId(): ?int
    {
        return auth()->user()?->entity?->id;
    }
}
