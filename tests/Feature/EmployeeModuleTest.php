<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia;

/**
 * Skema minimal mengikuti database asli (tototoko_zakiah_pos), karena tabel domain
 * tidak ada di database/migrations.
 */
beforeEach(function () {
    $this->withoutVite();

    // Di database asli email boleh kosong (akun karyawan tanpa login).
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
        $table->tinyInteger('tier')->default(1);
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
        $table->string('code');
        $table->string('first_name');
        $table->string('last_name');
        $table->boolean('select_all_location')->default(true);
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
        $table->string('code')->unique();
        $table->string('pin');
        $table->json('entity_permission')->nullable();
        $table->json('location_permission')->nullable();
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });

    DB::table('entities')->insert([['id' => 3, 'name' => 'ZAKIAH'], ['id' => 1, 'name' => 'Secaca']]);
    DB::table('locations')->insert([
        ['id' => 10, 'entity_id' => 3, 'name' => 'Mojokerto'],
        ['id' => 11, 'entity_id' => 3, 'name' => 'Pandaan'],
        ['id' => 20, 'entity_id' => 1, 'name' => 'Toko Secaca'],
    ]);
    // Sama dengan database asli: 1-5 template sistem, 8 role turunan milik ZAKIAH.
    DB::table('roles')->insert([
        ['id' => 1, 'name' => 'Entity Owner', 'allow_pos' => true, 'allow_backoffice' => true, 'entity_permission' => '{"role":{"index":true}}', 'location_permission' => '{}'],
        ['id' => 2, 'name' => 'Location Owner', 'allow_pos' => true, 'allow_backoffice' => true, 'entity_permission' => '{}', 'location_permission' => '{}'],
        ['id' => 3, 'name' => 'Cashier', 'allow_pos' => true, 'allow_backoffice' => false, 'entity_permission' => '{}', 'location_permission' => '{}'],
        ['id' => 4, 'name' => 'Warehouse', 'allow_pos' => false, 'allow_backoffice' => true, 'entity_permission' => '{}', 'location_permission' => '{}'],
        ['id' => 5, 'name' => 'Sales', 'allow_pos' => false, 'allow_backoffice' => false, 'entity_permission' => '{}', 'location_permission' => '{}'],
    ]);
    DB::table('roles')->insert(['id' => 8, 'entity_id' => 3, 'parent_id' => 5, 'name' => 'Sales', 'level' => 2, 'entity_permission' => '{}', 'location_permission' => '{}']);

    $this->admin = User::factory()->create();
    $this->adminEmployee = DB::table('employees')->insertGetId([
        'entity_id' => 3, 'user_id' => $this->admin->id, 'role_id' => 1, 'code' => 'admin',
        'first_name' => 'Admin', 'last_name' => 'Toko',
    ]);

    $this->employeeOf = fn (string $first) => DB::table('employees')->where('first_name', $first)->first();
    $this->rowsOf = fn (int $employeeId) => DB::table('employee_locations')
        ->where('employee_id', $employeeId)->whereNull('deleted_at')->orderBy('id')->get();

    // Salinan query daftar sales NewZakicaPOS (Models/Kasir/Katalog.cs GetSalesAsync) dan
    // pos-secaca lama (KasirEmployeeController): karyawan dengan role template Sales di toko itu.
    $this->posSales = function (int $locationId, int $entityId = 3) {
        $salesRole = DB::table('roles')->whereNull('entity_id')->whereNull('parent_id')->where('level', 1)->where('name', 'Sales')->value('id');

        return DB::table('employees as e')
            ->join('employee_locations as el', 'e.id', '=', 'el.employee_id')
            ->where('e.entity_id', $entityId)->whereNull('e.deleted_at')
            ->where('el.location_id', $locationId)->where('el.role_id', $salesRole)->whereNull('el.deleted_at')
            ->distinct()->orderBy('e.first_name')
            ->pluck('e.first_name')->all();
    };

    $this->tia = fn () => $this->actingAs($this->admin)->post(route('employees.store'), [
        'first_name' => 'Tia',
        'last_name' => 'Rahma',
        'assignments' => [
            ['location_id' => 10, 'role_id' => 3],
            ['location_id' => 10, 'role_id' => 5],
            ['location_id' => 11, 'role_id' => 5],
        ],
    ]);
});

it('membuat satu karyawan dengan banyak penugasan, dan satu akun tanpa login', function () {
    ($this->tia)()->assertSessionHasNoErrors()->assertRedirect();

    expect(DB::table('employees')->where('first_name', 'Tia')->count())->toBe(1);

    $tia = ($this->employeeOf)('Tia');
    $rows = ($this->rowsOf)($tia->id);

    expect($rows->map(fn ($row) => "{$row->location_id}-{$row->role_id}")->all())->toBe(['10-3', '10-5', '11-5']);
    expect($rows->pluck('pin')->unique()->all())->toBe(['1234']);

    // Role utama = Kasir, supaya POS mengizinkan masuk kasir (Sales tidak punya akses POS).
    expect((int) $tia->role_id)->toBe(3);

    $user = User::query()->find($tia->user_id);
    expect($user->email)->toBeNull();
    expect($user->name)->toBe('Tia Rahma');
    expect(DB::table('employees')->where('user_id', $tia->user_id)->count())->toBe(1);
});

it('tetap muncul di daftar sales POS untuk tiap toko tempat ia jadi Sales', function () {
    ($this->tia)()->assertSessionHasNoErrors();

    expect(($this->posSales)(10))->toBe(['Tia']);
    expect(($this->posSales)(11))->toBe(['Tia']);
});

it('memperbarui penugasan tanpa membuat ulang baris yang tetap', function () {
    ($this->tia)();
    $tia = ($this->employeeOf)('Tia');
    $before = ($this->rowsOf)($tia->id)->keyBy(fn ($row) => "{$row->location_id}-{$row->role_id}");

    $this->actingAs($this->admin)->put(route('employees.update', $tia->id), [
        'first_name' => 'Tia',
        'last_name' => 'Rahma',
        'assignments' => [
            ['location_id' => 10, 'role_id' => 5],
            ['location_id' => 11, 'role_id' => 5],
            ['location_id' => 11, 'role_id' => 3],
        ],
    ])->assertSessionHasNoErrors();

    $after = ($this->rowsOf)($tia->id)->keyBy(fn ($row) => "{$row->location_id}-{$row->role_id}");

    expect($after->keys()->sort()->values()->all())->toBe(['10-5', '11-3', '11-5']);
    // Baris yang tetap dipakai tidak berganti id maupun kode.
    expect($after['10-5']->id)->toBe($before['10-5']->id);
    expect($after['10-5']->code)->toBe($before['10-5']->code);
    // Baris yang dilepas di-soft-delete, bukan dihapus permanen.
    expect(DB::table('employee_locations')->where('id', $before['10-3']->id)->value('deleted_at'))->not->toBeNull();

    expect(($this->posSales)(10))->toBe(['Tia']);
});

it('mengisi role utama Sales bila semua penugasannya Sales', function () {
    $this->actingAs($this->admin)->post(route('employees.store'), [
        'first_name' => 'Rina',
        'assignments' => [['location_id' => 11, 'role_id' => 5]],
    ])->assertSessionHasNoErrors();

    expect((int) ($this->employeeOf)('Rina')->role_id)->toBe(5);
});

it('menolak role turunan, toko entity lain, penugasan dobel, dan tanpa penugasan', function () {
    $post = fn (array $assignments) => $this->actingAs($this->admin)->post(route('employees.store'), [
        'first_name' => 'Uji',
        'assignments' => $assignments,
    ]);

    $post([['location_id' => 10, 'role_id' => 8]])->assertSessionHasErrors('assignments.0.role_id');
    $post([['location_id' => 20, 'role_id' => 5]])->assertSessionHasErrors('assignments.0.location_id');
    $post([['location_id' => 10, 'role_id' => 5], ['location_id' => 10, 'role_id' => 5]])->assertSessionHasErrors('assignments');
    $post([])->assertSessionHasErrors('assignments');

    expect(DB::table('employees')->where('first_name', 'Uji')->exists())->toBeFalse();
});

it('mewajibkan nama unik per entity, bukan di semua entity', function () {
    $other = User::factory()->create();
    DB::table('employees')->insert([
        'entity_id' => 1, 'user_id' => $other->id, 'role_id' => 5, 'code' => 'x',
        'first_name' => 'Tia', 'last_name' => 'Rahma',
    ]);

    ($this->tia)()->assertSessionHasNoErrors();
    ($this->tia)()->assertSessionHasErrors('first_name');
});

it('tidak menyentuh akun login saat data karyawan diubah', function () {
    ($this->tia)();
    $tia = ($this->employeeOf)('Tia');
    DB::table('users')->where('id', $tia->user_id)->update(['name' => 'Akun Tia', 'email' => 'tia@zakiah.com']);

    $this->actingAs($this->admin)->put(route('employees.update', $tia->id), [
        'first_name' => 'Tia',
        'last_name' => 'Ganti',
        'email' => 'dibajak@zakiah.com',
        'assignments' => [['location_id' => 10, 'role_id' => 3]],
    ])->assertSessionHasNoErrors();

    $user = DB::table('users')->find($tia->user_id);
    expect($user->name)->toBe('Akun Tia');
    expect($user->email)->toBe('tia@zakiah.com');
});

it('membiarkan data lama satu akun dua karyawan, dan menandainya', function () {
    $balqis = User::factory()->create(['name' => 'balqis']);
    $kasir = DB::table('employees')->insertGetId(['entity_id' => 3, 'user_id' => $balqis->id, 'role_id' => 3, 'code' => 'cashier-balqis', 'first_name' => 'balqis', 'last_name' => 'Kasir']);
    $sales = DB::table('employees')->insertGetId(['entity_id' => 3, 'user_id' => $balqis->id, 'role_id' => 5, 'code' => 'sales-balqis', 'first_name' => 'balqis', 'last_name' => 'Sales']);
    DB::table('employee_locations')->insert([
        ['employee_id' => $kasir, 'location_id' => 11, 'role_id' => 3, 'code' => 'a', 'pin' => '1234'],
        ['employee_id' => $sales, 'location_id' => 11, 'role_id' => 5, 'code' => 'b', 'pin' => '1234'],
    ]);

    $this->actingAs($this->admin)
        ->get(route('employees.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('pagination.data', fn ($rows) => collect($rows)->where('user_id', $balqis->id)->count() === 2
                && collect($rows)->where('user_id', $balqis->id)->every(fn ($row) => $row['shares_account'] === true)));

    // Mengubah profil Kasir tidak menyentuh profil Sales, jadi POS tetap menampilkannya.
    $this->actingAs($this->admin)->put(route('employees.update', $kasir), [
        'first_name' => 'balqis',
        'last_name' => 'Kasir',
        'assignments' => [['location_id' => 10, 'role_id' => 3]],
    ])->assertSessionHasNoErrors();

    expect(($this->rowsOf)($sales)->pluck('code')->all())->toBe(['b']);
    expect(($this->posSales)(11))->toBe(['balqis']);
});

it('menolak mengubah karyawan entity lain', function () {
    $other = User::factory()->create();
    $id = DB::table('employees')->insertGetId(['entity_id' => 1, 'user_id' => $other->id, 'role_id' => 5, 'code' => 'y', 'first_name' => 'Lain', 'last_name' => '']);

    $this->actingAs($this->admin)->put(route('employees.update', $id), [
        'first_name' => 'Lain',
        'assignments' => [['location_id' => 10, 'role_id' => 5]],
    ])->assertNotFound();
});

it('membuat akun login untuk karyawan yang belum punya akun, dengan password bcrypt', function () {
    ($this->tia)();
    $tia = ($this->employeeOf)('Tia');

    $this->actingAs($this->admin)
        ->get(route('user-accounts.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('user-accounts/index')
            ->where('employeeOptions', fn ($options) => collect($options)->pluck('value')->all() === [$tia->id]));

    $this->actingAs($this->admin)->post(route('user-accounts.store'), [
        'employee_id' => $tia->id,
        'email' => 'Tia@Zakiah.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertSessionHasNoErrors();

    $user = DB::table('users')->find($tia->user_id);
    expect($user->email)->toBe('tia@zakiah.com');
    // NewZakicaPOS memverifikasi hash bcrypt "$2y$".
    expect(str_starts_with($user->password, '$2y$'))->toBeTrue();
    expect(Hash::check('rahasia123', $user->password))->toBeTrue();
    expect(($this->employeeOf)('Tia')->last_name)->toBe('Rahma');

    // Karyawan yang sudah punya akun tidak bisa dibuatkan akun kedua.
    $this->actingAs($this->admin)->post(route('user-accounts.store'), [
        'employee_id' => $tia->id,
        'email' => 'lain@zakiah.com',
        'password' => 'rahasia123',
        'password_confirmation' => 'rahasia123',
    ])->assertSessionHasErrors('employee_id');
});

it('mengubah email dan password akun tanpa menyentuh data karyawan', function () {
    ($this->tia)();
    $tia = ($this->employeeOf)('Tia');
    DB::table('users')->where('id', $tia->user_id)->update(['email' => 'tia@zakiah.com']);

    $this->actingAs($this->admin)->put(route('user-accounts.update', $tia->user_id), [
        'email' => 'tia.baru@zakiah.com',
        'password' => 'passwordbaru',
        'password_confirmation' => 'passwordbaru',
    ])->assertSessionHasNoErrors();

    $user = DB::table('users')->find($tia->user_id);
    expect($user->email)->toBe('tia.baru@zakiah.com');
    expect(Hash::check('passwordbaru', $user->password))->toBeTrue();

    // Email milik akun lain ditolak.
    $this->actingAs($this->admin)->put(route('user-accounts.update', $tia->user_id), [
        'email' => $this->admin->email,
    ])->assertSessionHasErrors('email');
});

it('menghapus akun sehingga email kosong (tidak bisa login di POS), lalu memulihkannya', function () {
    ($this->tia)();
    $tia = ($this->employeeOf)('Tia');
    DB::table('users')->where('id', $tia->user_id)->update(['email' => 'tia@zakiah.com', 'password' => Hash::make('rahasia123')]);

    $this->actingAs($this->admin)->delete(route('user-accounts.destroy', $tia->user_id))->assertSessionHasNoErrors();

    $user = DB::table('users')->find($tia->user_id);
    // NewZakicaPOS dan pos-secaca login lewat email saja, jadi email harus kosong.
    expect($user->email)->toBeNull();
    expect($user->deleted_email)->toBe('tia@zakiah.com');
    expect($user->deleted_at)->not->toBeNull();
    // Data karyawan dan penugasannya tetap ada (POS tetap bisa memilihnya sebagai Sales).
    expect(($this->employeeOf)('Tia')->deleted_at)->toBeNull();
    expect(($this->posSales)(10))->toBe(['Tia']);

    $this->actingAs($this->admin)
        ->get(route('user-accounts.index', ['status' => 'deleted']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('pagination.data.0.deleted_email', 'tia@zakiah.com'));

    // Tidak muncul sebagai pilihan "Akun Baru"; dipulihkan lewat tab Terhapus.
    $this->actingAs($this->admin)
        ->get(route('user-accounts.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('employeeOptions', []));

    $this->actingAs($this->admin)->post(route('user-accounts.restore', $tia->user_id))->assertSessionHasNoErrors();

    $user = DB::table('users')->find($tia->user_id);
    expect($user->email)->toBe('tia@zakiah.com');
    expect($user->deleted_email)->toBeNull();
    expect($user->deleted_at)->toBeNull();
    expect(Hash::check('rahasia123', $user->password))->toBeTrue();
});

it('meminta email baru saat memulihkan bila email lama sudah dipakai', function () {
    ($this->tia)();
    $tia = ($this->employeeOf)('Tia');
    DB::table('users')->where('id', $tia->user_id)->update(['email' => 'tia@zakiah.com']);

    $this->actingAs($this->admin)->delete(route('user-accounts.destroy', $tia->user_id));
    User::factory()->create(['email' => 'tia@zakiah.com']);

    $this->actingAs($this->admin)->post(route('user-accounts.restore', $tia->user_id))->assertSessionHasErrors('email');
    $this->actingAs($this->admin)->post(route('user-accounts.restore', $tia->user_id), ['email' => 'tia2@zakiah.com'])
        ->assertSessionHasNoErrors();

    expect(DB::table('users')->find($tia->user_id)->email)->toBe('tia2@zakiah.com');
});

it('hanya menampilkan dan mengubah akun milik karyawan entity ini', function () {
    $customer = User::factory()->create(['name' => 'Pelanggan']);
    $other = User::factory()->create(['name' => 'Karyawan Secaca']);
    DB::table('employees')->insert(['entity_id' => 1, 'user_id' => $other->id, 'role_id' => 5, 'code' => 'z', 'first_name' => 'Lain', 'last_name' => '']);

    $this->actingAs($this->admin)
        ->get(route('user-accounts.index'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('pagination.data', fn ($rows) => collect($rows)->pluck('id')->all() === [$this->admin->id]));

    foreach ([$customer, $other] as $user) {
        $this->actingAs($this->admin)->put(route('user-accounts.update', $user->id), ['email' => 'dibajak@zakiah.com'])->assertNotFound();
        $this->actingAs($this->admin)->delete(route('user-accounts.destroy', $user->id))->assertNotFound();
    }

    expect(DB::table('users')->find($customer->id)->deleted_at)->toBeNull();
});

it('tidak mengizinkan menghapus akun sendiri', function () {
    $this->actingAs($this->admin)
        ->delete(route('user-accounts.destroy', $this->admin->id))
        ->assertSessionHasErrors('account');

    expect(DB::table('users')->find($this->admin->id)->email)->not->toBeNull();
});
