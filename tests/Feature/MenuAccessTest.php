<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia;

/**
 * Skema mengikuti database/sql/2026_11_06_create_menu_access_tables.sql dan tabel asli
 * yang dipakai halaman Karyawan.
 */
beforeEach(function () {
    $this->withoutVite();

    Schema::table('users', function (Blueprint $table) {
        $table->string('email')->nullable()->change();
    });

    Schema::create('entities', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('locations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('entity_id');
        $table->string('name');
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('roles', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('entity_id')->nullable();
        $table->unsignedBigInteger('parent_id')->nullable();
        $table->string('name');
        $table->tinyInteger('level')->default(1);
        $table->json('entity_permission')->nullable();
        $table->json('location_permission')->nullable();
        $table->boolean('allow_pos')->default(true);
        $table->boolean('allow_backoffice')->default(true);
        $table->timestamps();
    });
    Schema::create('employees', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('entity_id');
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('role_id');
        $table->unsignedBigInteger('access_role_id')->nullable();
        $table->string('code')->default('x');
        $table->string('first_name');
        $table->string('last_name')->default('');
        $table->boolean('select_all_location')->default(false);
        $table->json('entity_permission')->nullable();
        $table->json('location_permission')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('employee_locations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('employee_id');
        $table->unsignedBigInteger('location_id');
        $table->unsignedBigInteger('role_id');
        $table->string('code');
        $table->string('pin');
        $table->json('entity_permission')->nullable();
        $table->json('location_permission')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });

    foreach (['menu_headers', 'menu_groups', 'menu_submenus'] as $name) {
        Schema::create($name, function (Blueprint $table) use ($name) {
            $table->id();
            $table->string('code')->unique();
            if ($name === 'menu_headers') {
                $table->string('section')->default('main');
                $table->integer('seq')->default(0);
            }
            $table->string('name');
            $table->string('icon')->nullable();
            if ($name !== 'menu_groups') {
                $table->string('route_name')->nullable();
            }
            $table->boolean('is_active')->default(true);
            $table->string('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }
    Schema::create('menu_header_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('menu_header_id')->constrained('menu_headers')->cascadeOnDelete();
        $table->foreignId('menu_submenu_id')->constrained('menu_submenus')->cascadeOnDelete();
        $table->foreignId('menu_group_id')->nullable()->constrained('menu_groups')->nullOnDelete();
        $table->integer('seq')->default(0);
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamp('created_at')->nullable();
    });
    Schema::create('menu_actions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('menu_submenu_id')->constrained('menu_submenus')->cascadeOnDelete();
        $table->string('code');
        $table->string('name');
        $table->integer('seq')->default(0);
        $table->timestamp('created_at')->nullable();
    });
    Schema::create('menu_action_routes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('menu_action_id')->constrained('menu_actions')->cascadeOnDelete();
        $table->string('route_name')->unique();
        $table->timestamp('created_at')->nullable();
    });
    Schema::create('access_roles', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('entity_id');
        $table->string('name');
        $table->string('note')->nullable();
        $table->unsignedBigInteger('default_for_role_id')->nullable();
        $table->boolean('is_system')->default(false);
        $table->boolean('is_active')->default(true);
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('access_role_menu_actions', function (Blueprint $table) {
        $table->foreignId('access_role_id')->constrained('access_roles')->cascadeOnDelete();
        $table->foreignId('menu_action_id')->constrained('menu_actions')->cascadeOnDelete();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->timestamp('created_at')->nullable();
        $table->primary(['access_role_id', 'menu_action_id']);
    });

    DB::table('entities')->insert([['id' => 3, 'name' => 'ZAKIAH'], ['id' => 1, 'name' => 'Secaca']]);
    DB::table('locations')->insert(['id' => 10, 'entity_id' => 3, 'name' => 'Mojokerto']);
    DB::table('roles')->insert([
        ['id' => 1, 'name' => 'Entity Owner'],
        ['id' => 2, 'name' => 'Location Owner'],
        ['id' => 3, 'name' => 'Cashier'],
        ['id' => 4, 'name' => 'Warehouse'],
        ['id' => 5, 'name' => 'Sales'],
    ]);

    // Menu: Dashboard (link langsung) dan Administrasi > [Akun] Karyawan, Akun Login.
    DB::table('menu_headers')->insert([
        ['id' => 1, 'code' => 'dashboard', 'name' => 'Dashboard', 'icon' => 'LayoutGrid', 'route_name' => 'dashboard.index', 'seq' => 10],
        ['id' => 2, 'code' => 'administrasi', 'name' => 'Administrasi', 'icon' => 'Cog', 'route_name' => null, 'seq' => 20],
    ]);
    DB::table('menu_groups')->insert(['id' => 1, 'code' => 'akun', 'name' => 'Akun']);
    DB::table('menu_submenus')->insert([
        ['id' => 1, 'code' => 'dashboard', 'name' => 'Dashboard', 'route_name' => 'dashboard.index'],
        ['id' => 2, 'code' => 'employees', 'name' => 'Karyawan', 'route_name' => 'employees.index'],
        ['id' => 3, 'code' => 'user-accounts', 'name' => 'Akun Login', 'route_name' => 'user-accounts.index'],
        ['id' => 4, 'code' => 'access-roles', 'name' => 'Hak Akses', 'route_name' => 'access-roles.index'],
    ]);
    DB::table('menu_header_details')->insert([
        ['menu_header_id' => 1, 'menu_submenu_id' => 1, 'menu_group_id' => null, 'seq' => 10],
        ['menu_header_id' => 2, 'menu_submenu_id' => 2, 'menu_group_id' => null, 'seq' => 10],
        ['menu_header_id' => 2, 'menu_submenu_id' => 3, 'menu_group_id' => 1, 'seq' => 20],
        ['menu_header_id' => 2, 'menu_submenu_id' => 4, 'menu_group_id' => 1, 'seq' => 30],
    ]);
    DB::table('menu_actions')->insert([
        ['id' => 1, 'menu_submenu_id' => 1, 'code' => 'view', 'name' => 'Lihat'],
        ['id' => 2, 'menu_submenu_id' => 2, 'code' => 'view', 'name' => 'Lihat'],
        ['id' => 3, 'menu_submenu_id' => 2, 'code' => 'create', 'name' => 'Tambah'],
        ['id' => 4, 'menu_submenu_id' => 3, 'code' => 'view', 'name' => 'Lihat'],
        ['id' => 5, 'menu_submenu_id' => 4, 'code' => 'view', 'name' => 'Lihat'],
        ['id' => 6, 'menu_submenu_id' => 4, 'code' => 'create', 'name' => 'Tambah'],
    ]);
    DB::table('menu_action_routes')->insert([
        ['menu_action_id' => 1, 'route_name' => 'dashboard.index'],
        ['menu_action_id' => 2, 'route_name' => 'employees.index'],
        ['menu_action_id' => 2, 'route_name' => 'employees.deleted'],
        ['menu_action_id' => 3, 'route_name' => 'employees.store'],
        ['menu_action_id' => 4, 'route_name' => 'user-accounts.index'],
        ['menu_action_id' => 5, 'route_name' => 'access-roles.index'],
        ['menu_action_id' => 6, 'route_name' => 'access-roles.store'],
    ]);
    DB::table('access_roles')->insert([
        ['id' => 1, 'entity_id' => 3, 'name' => 'Pemilik Usaha', 'default_for_role_id' => 1, 'is_system' => true],
        ['id' => 2, 'entity_id' => 3, 'name' => 'Kasir', 'default_for_role_id' => 3, 'is_system' => true],
        ['id' => 3, 'entity_id' => 3, 'name' => 'Admin Karyawan', 'default_for_role_id' => null, 'is_system' => false],
        ['id' => 4, 'entity_id' => 1, 'name' => 'Lain Entity', 'default_for_role_id' => null, 'is_system' => false],
    ]);
    DB::table('access_role_menu_actions')->insert([
        ['access_role_id' => 2, 'menu_action_id' => 1],
        ['access_role_id' => 3, 'menu_action_id' => 2],
        ['access_role_id' => 3, 'menu_action_id' => 3],
    ]);

    $this->makeEmployee = function (int $roleId, ?int $accessRoleId = null): User {
        $user = User::factory()->create();
        DB::table('employees')->insert([
            'entity_id' => 3, 'user_id' => $user->id, 'role_id' => $roleId,
            'access_role_id' => $accessRoleId, 'first_name' => 'Uji '.$user->id,
        ]);

        return $user;
    };

    config(['access.enforced' => true]);
});

it('menolak route yang tidak dicentang dan mengizinkan yang dicentang', function () {
    $kasir = ($this->makeEmployee)(3);
    $admin = ($this->makeEmployee)(3, 3);

    $this->actingAs($kasir)->get(route('employees.index'))->assertForbidden();
    $this->actingAs($kasir)->get(route('user-accounts.index'))->assertForbidden();

    // Hak Akses yang dipilih manual menggantikan bawaan role utamanya.
    $this->actingAs($admin)->get(route('employees.index'))->assertOk();
    $this->actingAs($admin)->get(route('employees.deleted'))->assertOk();
    $this->actingAs($admin)->get(route('user-accounts.index'))->assertForbidden();
});

it('mengizinkan route yang tidak terdaftar di menu untuk semua yang login', function () {
    $kasir = ($this->makeEmployee)(3);

    $this->actingAs($kasir)->get(route('profile.edit'))->assertOk();
});

it('meloloskan Pemilik Usaha walau Hak Akses-nya tidak dicentang', function () {
    $owner = ($this->makeEmployee)(1);

    $this->actingAs($owner)->get(route('employees.index'))->assertOk();
    $this->actingAs($owner)->get(route('user-accounts.index'))->assertOk();
});

it('tidak menolak apa pun selama pengecekan belum diaktifkan', function () {
    config(['access.enforced' => false]);
    $kasir = ($this->makeEmployee)(3);

    $this->actingAs($kasir)->get(route('employees.index'))->assertOk();
});

it('membentuk sidebar dari tabel menu sesuai Hak Akses, dengan group di dalam header', function () {
    $admin = ($this->makeEmployee)(3, 3);

    $this->actingAs($admin)
        ->get(route('employees.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            // Tidak punya akses Dashboard, Akun Login, dan Hak Akses: hanya Karyawan.
            ->where('navigation.main', [[
                'title' => 'Administrasi',
                'href' => '#',
                'icon' => 'Cog',
                'items' => [['title' => 'Karyawan', 'href' => '/employees', 'icon' => null]],
            ]])
            ->where('access.permissions', ['employees.view', 'employees.create']));

    $owner = ($this->makeEmployee)(1);

    $this->actingAs($owner)
        ->get(route('employees.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('navigation.main.0.title', 'Dashboard')
            ->where('navigation.main.0.href', '/dashboard')
            ->where('navigation.main.1.items.1.title', 'Akun')
            ->where('navigation.main.1.items.1.items.0.title', 'Akun Login')
            ->where('navigation.main.1.items.1.items.1.title', 'Hak Akses')
            ->where('access.permissions', ['*']));
});

it('mengelola role: tambah cukup nama, Hak Akses lewat tombol sendiri, hapus dan pulihkan', function () {
    $owner = ($this->makeEmployee)(1);

    // Tambah role hanya menyimpan nama; centangan yang ikut terkirim diabaikan.
    $this->actingAs($owner)->post(route('access-roles.store'), [
        'name' => 'Supervisor',
        'action_ids' => [2, 4],
    ])->assertSessionHasNoErrors();

    $id = (int) DB::table('access_roles')->where('name', 'Supervisor')->value('id');
    expect((int) DB::table('access_roles')->find($id)->entity_id)->toBe(3);
    expect(DB::table('access_role_menu_actions')->where('access_role_id', $id)->count())->toBe(0);

    // Tombol Hak Akses menyimpan centangan.
    $this->actingAs($owner)->put(route('access-roles.permissions', $id), ['action_ids' => [2, 4]])->assertSessionHasNoErrors();
    expect(DB::table('access_role_menu_actions')->where('access_role_id', $id)->pluck('menu_action_id')->sort()->values()->all())->toBe([2, 4]);

    // Edit role mengganti nama tanpa menyentuh centangan.
    $this->actingAs($owner)->put(route('access-roles.update', $id), ['name' => 'Supervisor Toko'])->assertSessionHasNoErrors();
    expect(DB::table('access_roles')->find($id)->name)->toBe('Supervisor Toko');
    expect(DB::table('access_role_menu_actions')->where('access_role_id', $id)->count())->toBe(2);

    $this->actingAs($owner)->put(route('access-roles.permissions', $id), ['action_ids' => [5]])->assertSessionHasNoErrors();
    expect(DB::table('access_role_menu_actions')->where('access_role_id', $id)->pluck('menu_action_id')->all())->toBe([5]);
    $this->actingAs($owner)->put(route('access-roles.permissions', $id), ['action_ids' => [999]])->assertSessionHasErrors('action_ids.0');
    $this->actingAs($owner)->put(route('access-roles.update', $id), ['name' => 'Supervisor'])->assertSessionHasNoErrors();

    // Nama unik per entity, bukan di semua entity.
    $this->actingAs($owner)->post(route('access-roles.store'), ['name' => 'Lain Entity'])->assertSessionHasNoErrors();
    $this->actingAs($owner)->post(route('access-roles.store'), ['name' => 'Supervisor'])->assertSessionHasErrors('name');

    // Bawaan tidak bisa dihapus; yang masih dipakai karyawan juga tidak.
    $this->actingAs($owner)->delete(route('access-roles.destroy', 2))->assertSessionHasErrors('access_role');
    ($this->makeEmployee)(3, 3);
    $this->actingAs($owner)->delete(route('access-roles.destroy', 3))->assertSessionHasErrors('access_role');

    $this->actingAs($owner)->delete(route('access-roles.destroy', $id))->assertSessionHasNoErrors();
    expect(DB::table('access_roles')->find($id)->deleted_at)->not->toBeNull();

    $this->actingAs($owner)
        ->get(route('access-roles.index', ['status' => 'deleted']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('onlyTrashed', true)
            ->where('accessRoles', fn ($rows) => collect($rows)->pluck('id')->all() === [$id]));

    // Pulihkan ditolak bila nama itu sudah dipakai role aktif, lalu berhasil setelah bebas.
    $this->actingAs($owner)->post(route('access-roles.store'), ['name' => 'Supervisor'])->assertSessionHasNoErrors();
    $this->actingAs($owner)->post(route('access-roles.restore', $id))->assertSessionHasErrors('access_role');
    DB::table('access_roles')->where('name', 'Supervisor')->whereNull('deleted_at')->update(['deleted_at' => now()]);
    $this->actingAs($owner)->post(route('access-roles.restore', $id))->assertSessionHasNoErrors();
    expect(DB::table('access_roles')->find($id)->deleted_at)->toBeNull();
    // Centangan tetap ada setelah dipulihkan.
    expect(DB::table('access_role_menu_actions')->where('access_role_id', $id)->pluck('menu_action_id')->all())->toBe([5]);

    // Role entity lain tidak bisa diubah.
    $this->actingAs($owner)->put(route('access-roles.update', 4), ['name' => 'Dibajak'])->assertNotFound();
    $this->actingAs($owner)->put(route('access-roles.permissions', 4), ['action_ids' => [2]])->assertNotFound();
});

it('memakai Hak Akses pilihan di form karyawan, hanya dari entity sendiri', function () {
    $owner = ($this->makeEmployee)(1);

    $this->actingAs($owner)->post(route('employees.store'), [
        'first_name' => 'Rina',
        'access_role_id' => 4,
        'assignments' => [['location_id' => 10, 'role_id' => 5]],
    ])->assertSessionHasErrors('access_role_id');

    $this->actingAs($owner)->post(route('employees.store'), [
        'first_name' => 'Rina',
        'access_role_id' => 3,
        'assignments' => [['location_id' => 10, 'role_id' => 5]],
    ])->assertSessionHasNoErrors();

    expect((int) DB::table('employees')->where('first_name', 'Rina')->value('access_role_id'))->toBe(3);
});

it('tidak menghapus sub menu yang routenya dilindungi, dan mengganti isi header', function () {
    $owner = ($this->makeEmployee)(1);

    $this->actingAs($owner)->delete(route('menu-submenus.destroy', 2))->assertSessionHasErrors('menu');
    expect(DB::table('menu_action_routes')->where('route_name', 'employees.index')->exists())->toBeTrue();

    // Group yang masih dipakai juga tidak bisa dihapus.
    $this->actingAs($owner)->delete(route('menu-groups.destroy', 1))->assertSessionHasErrors('menu');

    $this->actingAs($owner)->put(route('menu-headers.update', 2), [
        'name' => 'Admin',
        'section' => 'main',
        'icon' => 'Cog',
        'seq' => 20,
        'is_active' => true,
        'details' => [
            ['menu_submenu_id' => 3, 'menu_group_id' => null, 'seq' => 10],
            ['menu_submenu_id' => 2, 'menu_group_id' => null, 'seq' => 20],
        ],
    ])->assertSessionHasNoErrors();

    expect(DB::table('menu_header_details')->where('menu_header_id', 2)->orderBy('seq')->pluck('menu_submenu_id')->all())->toBe([3, 2]);
    expect(DB::table('menu_headers')->find(2)->name)->toBe('Admin');
});
