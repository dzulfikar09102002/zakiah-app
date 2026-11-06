<?php

namespace App\Services;

use App\Models\MenuAction;
use App\Models\MenuActionRoute;
use App\Models\MenuGroup;
use App\Models\MenuHeader;
use App\Models\MenuHeaderDetail;
use App\Models\MenuSubmenu;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Kelola menu sidebar: header, sub menu, dan group (pola SurabayaTaxWeb).
 * Menu berlaku untuk semua entity. Aksi dan route per sub menu dikelola lewat SQL,
 * karena menentukan pengecekan Hak Akses di server.
 */
class MenuAdminService
{
    /**
     * @return array<string, mixed>
     */
    public function pageData(): array
    {
        $details = MenuHeaderDetail::query()->orderBy('seq')->get();

        return [
            'headers' => MenuHeader::query()
                ->orderByRaw("CASE WHEN section = 'setting' THEN 1 ELSE 0 END")
                ->orderBy('seq')
                ->get()
                ->map(fn (MenuHeader $header) => [
                    'id' => $header->id,
                    'code' => $header->code,
                    'section' => $header->section,
                    'name' => $header->name,
                    'icon' => $header->icon,
                    'route_name' => $header->route_name,
                    'seq' => $header->seq,
                    'is_active' => $header->is_active,
                    'details' => $details->where('menu_header_id', $header->id)->map(fn (MenuHeaderDetail $detail) => [
                        'menu_submenu_id' => (int) $detail->menu_submenu_id,
                        'menu_group_id' => $detail->menu_group_id ? (int) $detail->menu_group_id : null,
                        'seq' => $detail->seq,
                    ])->values(),
                ]),
            'submenus' => MenuSubmenu::query()
                ->with('actions:id,menu_submenu_id,name')
                ->orderBy('name')
                ->get()
                ->map(fn (MenuSubmenu $submenu) => [
                    'id' => $submenu->id,
                    'code' => $submenu->code,
                    'name' => $submenu->name,
                    'icon' => $submenu->icon,
                    'route_name' => $submenu->route_name,
                    'is_active' => $submenu->is_active,
                    'actions' => $submenu->actions->pluck('name')->values(),
                    'used' => $details->where('menu_submenu_id', $submenu->id)->count(),
                ]),
            'groups' => MenuGroup::query()
                ->orderBy('name')
                ->get()
                ->map(fn (MenuGroup $group) => [
                    'id' => $group->id,
                    'code' => $group->code,
                    'name' => $group->name,
                    'icon' => $group->icon,
                    'is_active' => $group->is_active,
                    'used' => $details->where('menu_group_id', $group->id)->count(),
                ]),
            'routeOptions' => $this->routeOptions(),
        ];
    }

    /**
     * Halaman yang bisa dibuka dari menu: route GET yang terdaftar di Hak Akses.
     *
     * @return Collection<int, string>
     */
    public function routeOptions(): Collection
    {
        return MenuActionRoute::query()
            ->pluck('route_name')
            ->filter(fn (string $name) => Route::has($name)
                && in_array('GET', Route::getRoutes()->getByName($name)?->methods() ?? [], true))
            ->sort()
            ->values();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveHeader(?MenuHeader $header, array $data): void
    {
        DB::transaction(function () use ($header, $data) {
            $header ??= new MenuHeader(['code' => $this->uniqueCode(MenuHeader::class, $data['name']), 'created_by' => auth()->id()]);

            $header->fill([
                'section' => $data['section'],
                'name' => trim($data['name']),
                'icon' => $data['icon'] ?? null,
                'route_name' => $data['route_name'] ?? null,
                'seq' => (int) $data['seq'],
                'is_active' => (bool) ($data['is_active'] ?? true),
                'updated_by' => auth()->id(),
            ])->save();

            // Header yang langsung membuka halaman tidak punya isi sub menu.
            $details = filled($header->route_name) ? [] : ($data['details'] ?? []);

            MenuHeaderDetail::query()->where('menu_header_id', $header->id)->delete();

            foreach (array_values($details) as $index => $detail) {
                MenuHeaderDetail::query()->create([
                    'menu_header_id' => $header->id,
                    'menu_submenu_id' => (int) $detail['menu_submenu_id'],
                    'menu_group_id' => $detail['menu_group_id'] ?? null,
                    'seq' => (int) ($detail['seq'] ?? ($index + 1) * 10),
                    'created_by' => auth()->id(),
                ]);
            }
        });
    }

    public function deleteHeader(MenuHeader $header): void
    {
        // Sub menu di dalamnya tetap ada, hanya tidak tampil di header ini lagi.
        $header->delete();
    }

    /**
     * Sub menu baru mendapat aksi "Lihat" untuk route halamannya, bila route itu belum
     * dimiliki aksi lain. Aksi lain ditambahkan lewat SQL.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveSubmenu(?MenuSubmenu $submenu, array $data): void
    {
        DB::transaction(function () use ($submenu, $data) {
            $isNew = $submenu === null;
            $submenu ??= new MenuSubmenu(['code' => $this->uniqueCode(MenuSubmenu::class, $data['name']), 'created_by' => auth()->id()]);

            $submenu->fill([
                'name' => trim($data['name']),
                'icon' => $data['icon'] ?? null,
                'route_name' => $data['route_name'],
                'is_active' => (bool) ($data['is_active'] ?? true),
                'updated_by' => auth()->id(),
            ])->save();

            if ($isNew && ! MenuActionRoute::query()->where('route_name', $submenu->route_name)->exists()) {
                $action = MenuAction::query()->create([
                    'menu_submenu_id' => $submenu->id,
                    'code' => 'view',
                    'name' => 'Lihat',
                    'seq' => 10,
                ]);

                MenuActionRoute::query()->create(['menu_action_id' => $action->id, 'route_name' => $submenu->route_name]);
            }
        });
    }

    public function deleteSubmenu(MenuSubmenu $submenu): void
    {
        $hasRoutes = MenuActionRoute::query()
            ->whereIn('menu_action_id', $submenu->actions()->pluck('id'))
            ->exists();

        // Menghapus route dari daftar membuat route itu bebas diakses semua yang login.
        if ($hasRoutes) {
            throw ValidationException::withMessages([
                'menu' => 'Sub menu ini punya route yang dilindungi Hak Akses. Nonaktifkan saja supaya tidak tampil di sidebar.',
            ]);
        }

        $submenu->delete();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function saveGroup(?MenuGroup $group, array $data): void
    {
        $group ??= new MenuGroup(['code' => $this->uniqueCode(MenuGroup::class, $data['name']), 'created_by' => auth()->id()]);

        $group->fill([
            'name' => trim($data['name']),
            'icon' => $data['icon'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'updated_by' => auth()->id(),
        ])->save();
    }

    public function deleteGroup(MenuGroup $group): void
    {
        if ($group->details()->exists()) {
            throw ValidationException::withMessages([
                'menu' => 'Group ini masih dipakai di header. Keluarkan dulu sub menunya dari group ini.',
            ]);
        }

        $group->delete();
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function uniqueCode(string $model, string $name): string
    {
        $base = Str::slug($name) ?: 'menu';
        $code = $base;
        $suffix = 2;

        while ($model::query()->where('code', $code)->exists()) {
            $code = $base.'-'.$suffix++;
        }

        return $code;
    }
}
