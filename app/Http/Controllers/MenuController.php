<?php

namespace App\Http\Controllers;

use App\Models\MenuGroup;
use App\Models\MenuHeader;
use App\Models\MenuSubmenu;
use App\Services\MenuAccessService;
use App\Services\MenuAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Konfigurasi > Menu: header, sub menu, dan group sidebar.
 * Tiga route index (menu-headers, menu-submenus, menu-groups) membuka halaman yang sama
 * dengan tab berbeda, supaya cocok dengan pemetaan route Hak Akses.
 */
class MenuController extends Controller
{
    public function __construct(
        private MenuAdminService $service,
        private MenuAccessService $access,
    ) {}

    public function headers(): Response
    {
        return $this->page('headers');
    }

    public function submenus(): Response
    {
        return $this->page('submenus');
    }

    public function groups(): Response
    {
        return $this->page('groups');
    }

    public function storeHeader(Request $request): RedirectResponse
    {
        $this->ensureAvailable();
        $this->service->saveHeader(null, $this->validateHeader($request));

        return back()->with('success', 'Header menu ditambahkan');
    }

    public function updateHeader(Request $request, int $id): RedirectResponse
    {
        $this->ensureAvailable();
        $this->service->saveHeader(MenuHeader::query()->findOrFail($id), $this->validateHeader($request));

        return back()->with('success', 'Header menu diperbarui');
    }

    public function destroyHeader(int $id): RedirectResponse
    {
        $this->ensureAvailable();
        $this->service->deleteHeader(MenuHeader::query()->findOrFail($id));

        return back()->with('success', 'Header menu dihapus');
    }

    public function storeSubmenu(Request $request): RedirectResponse
    {
        $this->ensureAvailable();
        $this->service->saveSubmenu(null, $this->validateSubmenu($request));

        return back()->with('success', 'Sub menu ditambahkan');
    }

    public function updateSubmenu(Request $request, int $id): RedirectResponse
    {
        $this->ensureAvailable();
        $this->service->saveSubmenu(MenuSubmenu::query()->findOrFail($id), $this->validateSubmenu($request));

        return back()->with('success', 'Sub menu diperbarui');
    }

    public function destroySubmenu(int $id): RedirectResponse
    {
        $this->ensureAvailable();
        $this->service->deleteSubmenu(MenuSubmenu::query()->findOrFail($id));

        return back()->with('success', 'Sub menu dihapus');
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $this->ensureAvailable();
        $this->service->saveGroup(null, $this->validateGroup($request));

        return back()->with('success', 'Group ditambahkan');
    }

    public function updateGroup(Request $request, int $id): RedirectResponse
    {
        $this->ensureAvailable();
        $this->service->saveGroup(MenuGroup::query()->findOrFail($id), $this->validateGroup($request));

        return back()->with('success', 'Group diperbarui');
    }

    public function destroyGroup(int $id): RedirectResponse
    {
        $this->ensureAvailable();
        $this->service->deleteGroup(MenuGroup::query()->findOrFail($id));

        return back()->with('success', 'Group dihapus');
    }

    private function page(string $tab): Response
    {
        $this->ensureAvailable();

        return Inertia::render('menus/index', [
            'tab' => $tab,
            ...$this->service->pageData(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateHeader(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'max:100'],
            'section' => ['required', Rule::in(['main', 'setting'])],
            'icon' => ['nullable', 'max:100'],
            'route_name' => ['nullable', Rule::in($this->service->routeOptions()->all())],
            'seq' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'details' => ['array'],
            'details.*.menu_submenu_id' => ['required', 'integer', 'distinct', Rule::exists('menu_submenus', 'id')],
            'details.*.menu_group_id' => ['nullable', 'integer', Rule::exists('menu_groups', 'id')],
            'details.*.seq' => ['nullable', 'integer', 'min:0'],
        ], [
            'name.required' => 'Nama header wajib diisi.',
            'details.*.menu_submenu_id.distinct' => 'Sub menu yang sama dipilih lebih dari sekali.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSubmenu(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'max:100'],
            'icon' => ['nullable', 'max:100'],
            'route_name' => ['required', Rule::in($this->service->routeOptions()->all())],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Nama sub menu wajib diisi.',
            'route_name.required' => 'Pilih halaman yang dibuka.',
            'route_name.in' => 'Halaman tidak dikenal.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateGroup(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'max:100'],
            'icon' => ['nullable', 'max:100'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Nama group wajib diisi.',
        ]);
    }

    private function ensureAvailable(): void
    {
        abort_unless($this->access->available(), 404, 'Tabel menu belum dibuat. Jalankan database/sql/2026_11_06_create_menu_access_tables.sql.');
    }
}
