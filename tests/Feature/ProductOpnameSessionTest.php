<?php

use App\Models\ProductOpnameSession;
use App\Models\User;
use App\Services\DocumentSignatureService;
use App\Services\ProductOpnameReviewService;
use App\Services\ProductOpnameScanService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Once;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia;

/**
 * Tabel domain tidak ada di database/migrations, jadi skema minimal dibuat di sini.
 */
beforeEach(function () {
    $this->withoutVite();

    Schema::create('entities', function (Blueprint $table) {
        $table->id();
        $table->string('name')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('employees', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('entity_id');
        $table->unsignedBigInteger('role_id')->nullable();
        $table->string('first_name')->default('Karyawan');
        $table->string('last_name')->default('Satu');
        $table->boolean('select_all_location')->default(true);
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('roles', function (Blueprint $table) {
        $table->id();
        $table->string('name')->default('Cashier');
        $table->boolean('allow_pos')->default(true);
        $table->boolean('allow_backoffice')->default(true);
        $table->timestamps();
    });
    Schema::create('employee_locations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('employee_id');
        $table->unsignedBigInteger('location_id');
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('locations', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('entity_id');
        $table->string('name');
        $table->string('status')->default('active');
        $table->string('timezone')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('product_units', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('product_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('entity_id');
        $table->string('name');
        $table->string('code')->nullable();
        $table->string('sku')->nullable();
        $table->string('barcode')->nullable();
        $table->text('description')->nullable();
        $table->string('status')->default('active');
        $table->boolean('service')->default(false);
        $table->boolean('modifier')->default(false);
        $table->unsignedBigInteger('product_category_id')->nullable();
        $table->unsignedBigInteger('product_unit_id')->nullable();
        $table->integer('sell_price')->default(0);
        $table->integer('cost_of_goods_sold')->default(0);
        $table->integer('last_buying_price')->default(0);
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('product_location_stocks', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('product_id');
        $table->unsignedBigInteger('location_id');
        $table->unsignedBigInteger('product_unit_id');
        $table->integer('stock')->default(0);
        $table->integer('last_in_stock')->default(0);
        $table->integer('last_out_stock')->default(0);
        $table->integer('last_buy_price')->default(0);
        $table->integer('average_buy_price')->default(0);
        $table->integer('lowest_buy_price')->default(0);
        $table->integer('highest_buy_price')->default(0);
        $table->string('checksum')->nullable();
        $table->softDeletes();
        $table->timestamps();
    });
    Schema::create('product_stock_movements', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('product_id');
        $table->unsignedBigInteger('location_id');
        $table->unsignedBigInteger('product_unit_id');
        $table->unsignedBigInteger('original_product_unit_id')->nullable();
        $table->string('resource_type');
        $table->unsignedBigInteger('resource_id');
        $table->unsignedInteger('original_stock_in')->default(0);
        $table->unsignedInteger('original_stock_out')->default(0);
        $table->unsignedInteger('original_buying_price')->default(0);
        $table->unsignedInteger('conversion_stock')->default(0);
        $table->unsignedInteger('stock_in');
        $table->unsignedInteger('stock_out');
        $table->unsignedInteger('buying_price');
        $table->timestamps();
    });

    foreach (['product_opname_services', 'product_adjustment_stocks'] as $name) {
        Schema::create($name, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('employee_requested_by');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('local_requested_at')->nullable();
            $table->unsignedBigInteger('employee_approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('local_approved_at')->nullable();
            $table->string('code');
            $table->string('status');
            $table->unsignedInteger('recorded_product_count');
            $table->unsignedInteger('counted_product_count');
            $table->unsignedInteger('difference_product_count');
            $table->integer('recorded_stock');
            $table->integer('counted_stock');
            $table->integer('difference_stock');
            $table->text('note')->nullable();
            $table->boolean('auto_approve')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    foreach ([
        'product_opname_service_details' => 'product_opname_service_id',
        'product_adjustment_stock_details' => 'product_adjustment_stock_id',
    ] as $name => $foreignKey) {
        Schema::create($name, function (Blueprint $table) use ($foreignKey) {
            $table->id();
            $table->unsignedBigInteger($foreignKey);
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_category_id')->nullable();
            $table->unsignedBigInteger('product_unit_id');
            $table->text('product_name');
            $table->text('product_sku');
            $table->text('product_code');
            $table->text('product_description');
            $table->text('product_category_name')->nullable();
            $table->text('product_unit_name')->nullable();
            $table->integer('recorded_stock');
            $table->integer('counted_stock');
            $table->integer('difference_stock');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    Schema::create('product_opname_sessions', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('entity_id');
        $table->unsignedBigInteger('location_id');
        $table->unsignedTinyInteger('type')->default(1);
        $table->unsignedBigInteger('parent_session_id')->nullable();
        $table->string('code');
        $table->date('opname_date');
        $table->unsignedBigInteger('pic_employee_id');
        $table->dateTime('start_at')->nullable();
        $table->dateTime('review_at')->nullable();
        $table->dateTime('end_at')->nullable();
        $table->unsignedBigInteger('product_opname_service_id')->nullable();
        $table->unsignedBigInteger('product_adjustment_stock_id')->nullable();
        $table->text('note')->nullable();
        $table->dateTime('ins_date');
        $table->unsignedBigInteger('ins_by');
        $table->dateTime('upd_date')->nullable();
        $table->unsignedBigInteger('upd_by')->nullable();
    });
    Schema::create('product_opname_session_employees', function (Blueprint $table) {
        $table->unsignedBigInteger('session_id');
        $table->unsignedBigInteger('employee_id');
        $table->dateTime('ins_date');
        $table->unsignedBigInteger('ins_by');
        $table->primary(['session_id', 'employee_id']);
    });
    Schema::create('product_opname_session_scans', function (Blueprint $table) {
        $table->id();
        $table->char('uuid', 36)->unique();
        $table->unsignedBigInteger('session_id');
        $table->unsignedBigInteger('product_id');
        $table->string('barcode');
        $table->integer('qty')->default(1);
        $table->integer('recorded_stock');
        $table->unsignedBigInteger('cancelled_scan_id')->nullable()->unique();
        $table->string('reason')->nullable();
        $table->dateTime('ins_date');
        $table->unsignedBigInteger('ins_by');
    });
    Schema::create('product_opname_session_reviews', function (Blueprint $table) {
        $table->unsignedBigInteger('session_id');
        $table->unsignedBigInteger('product_id');
        $table->integer('recorded_stock');
        $table->integer('counted_stock');
        $table->integer('difference_stock');
        $table->unsignedTinyInteger('decision');
        $table->unsignedBigInteger('product_opname_service_detail_id')->nullable();
        $table->text('note')->nullable();
        $table->dateTime('ins_date');
        $table->unsignedBigInteger('ins_by');
        $table->primary(['session_id', 'product_id']);
    });
    Schema::create('product_opname_session_revisions', function (Blueprint $table) {
        $table->unsignedBigInteger('session_id');
        $table->unsignedBigInteger('product_id');
        $table->unsignedInteger('seq');
        $table->integer('adjustment_qty');
        $table->unsignedTinyInteger('reason');
        $table->unsignedBigInteger('product_adjustment_stock_detail_id')->nullable();
        $table->text('note')->nullable();
        $table->dateTime('ins_date');
        $table->unsignedBigInteger('ins_by');
        $table->primary(['session_id', 'product_id', 'seq']);
    });

    DB::table('entities')->insert(['id' => 1, 'name' => 'Toko Uji']);
    DB::table('locations')->insert([
        ['id' => 2, 'entity_id' => 1, 'name' => 'Tulangan'],
        ['id' => 3, 'entity_id' => 1, 'name' => 'ZAKIAH OFFICE'],
        ['id' => 4, 'entity_id' => 1, 'name' => 'Mojosari'],
    ]);
    DB::table('roles')->insert([
        ['id' => 1, 'name' => 'Cashier', 'allow_pos' => true, 'allow_backoffice' => false],
        ['id' => 2, 'name' => 'Sales', 'allow_pos' => false, 'allow_backoffice' => false],
        ['id' => 3, 'name' => 'Office', 'allow_pos' => true, 'allow_backoffice' => true],
        ['id' => 4, 'name' => 'Entity Owner', 'allow_pos' => true, 'allow_backoffice' => true],
        ['id' => 5, 'name' => 'CEO', 'allow_pos' => true, 'allow_backoffice' => true],
    ]);
    DB::table('product_units')->insert(['id' => 1, 'name' => 'PCS']);
    DB::table('product_categories')->insert(['id' => 1, 'name' => 'Kemasan']);

    $this->pic = User::factory()->create();
    $this->member = User::factory()->create();
    $this->outsider = User::factory()->create();

    $this->picEmployee = DB::table('employees')->insertGetId(['user_id' => $this->pic->id, 'entity_id' => 1, 'role_id' => 3, 'first_name' => 'Budi', 'last_name' => 'Santoso']);
    $this->memberEmployee = DB::table('employees')->insertGetId(['user_id' => $this->member->id, 'entity_id' => 1, 'role_id' => 3]);
    DB::table('employees')->insert(['user_id' => $this->outsider->id, 'entity_id' => 1]);
    DB::table('employee_locations')->insert([
        ['employee_id' => $this->picEmployee, 'location_id' => 2],
        ['employee_id' => $this->memberEmployee, 'location_id' => 2],
    ]);

    $product = function (int $id, string $name, string $barcode, int $stock, array $extra = []) {
        DB::table('products')->insert([
            'id' => $id,
            'entity_id' => 1,
            'name' => $name,
            'code' => "C{$id}",
            'sku' => "S{$id}",
            'barcode' => $barcode,
            'product_unit_id' => 1,
            'product_category_id' => 1,
            ...$extra,
        ]);
        DB::table('product_location_stocks')->insert([
            'product_id' => $id,
            'location_id' => 2,
            'product_unit_id' => 1,
            'stock' => $stock,
        ]);
    };

    $product(10, 'Scan Kurang', '111', 5);
    $product(11, 'Hilang Total', '222', 7);
    $product(12, 'Cocok', '333', 1);
    $product(13, 'Jasa Bungkus', '444', 3, ['service' => true]);

    $this->stock = fn (int $productId): int => (int) DB::table('product_location_stocks')
        ->where('product_id', $productId)
        ->value('stock');

    $this->startSession = function (): ProductOpnameSession {
        $this->actingAs($this->pic)->post(route('product-opname-sessions.store'), [
            'location_id' => 2,
            'opname_date' => now()->toDateString(),
            'pic_employee_id' => $this->picEmployee,
            'employee_ids' => [$this->memberEmployee],
            'start_now' => true,
        ])->assertRedirect();

        return ProductOpnameSession::query()->latest('id')->firstOrFail();
    };

    $this->scan = fn (ProductOpnameSession $session, string $barcode, ?User $user = null, ?string $uuid = null) => $this
        ->actingAs($user ?? $this->member)
        ->postJson(route('product-opname-sessions.store-scan', $session->id), [
            'uuid' => $uuid ?? (string) Str::uuid(),
            'barcode' => $barcode,
        ]);

    /** Sesi siap review: 111 di-scan 3 dari 5, 333 di-scan 1 dari 1, 222 tidak ter-scan. */
    $this->sessionInReview = function (): ProductOpnameSession {
        $session = ($this->startSession)();

        foreach (['111', '111', '111', '333'] as $barcode) {
            ($this->scan)($session, $barcode)->assertOk();
        }

        $this->actingAs($this->pic)->post(route('product-opname-sessions.close-scan', $session->id))->assertRedirect();

        return $session->refresh();
    };

    $this->decide = fn (ProductOpnameSession $session, int $productId, int $decision) => $this
        ->actingAs($this->pic)
        ->post(route('product-opname-sessions.decide', $session->id), [
            'product_id' => $productId,
            'decision' => $decision,
        ]);

    $this->finishedSession = function (): ProductOpnameSession {
        $session = ($this->sessionInReview)();

        ($this->decide)($session, 10, 1)->assertSessionHasNoErrors();
        ($this->decide)($session, 11, 1)->assertSessionHasNoErrors();

        $this->actingAs($this->pic)
            ->post(route('product-opname-sessions.finish', $session->id), ['agreement' => true])
            ->assertSessionHasNoErrors();

        return $session->refresh();
    };
});

it('menentukan status dari start_at, review_at, dan end_at', function () {
    $session = new ProductOpnameSession;
    expect($session->status)->toBe('scheduled');

    $session->start_at = now();
    expect($session->status)->toBe('started');

    $session->review_at = now();
    expect($session->status)->toBe('review');

    $session->end_at = now();
    expect($session->status)->toBe('finished');
});

it('menjadwalkan sesi bila tidak langsung dimulai, dan hanya PJ yang bisa memulai', function () {
    $this->actingAs($this->pic)->post(route('product-opname-sessions.store'), [
        'location_id' => 2,
        'opname_date' => now()->addDay()->toDateString(),
        'pic_employee_id' => $this->picEmployee,
        'employee_ids' => [$this->memberEmployee],
        'start_now' => false,
    ])->assertSessionHasNoErrors();

    $session = ProductOpnameSession::query()->firstOrFail();

    expect($session->start_at)->toBeNull();

    $this->actingAs($this->member)->post(route('product-opname-sessions.start', $session->id))->assertForbidden();
    ($this->scan)($session, '111')->assertUnprocessable();

    $this->actingAs($this->pic)->post(route('product-opname-sessions.start', $session->id))->assertRedirect();

    expect($session->refresh()->start_at)->not->toBeNull();
});

it('menolak karyawan di luar daftar', function () {
    $session = ($this->startSession)();

    $this->actingAs($this->outsider)->get(route('product-opname-sessions.show', $session->id))->assertForbidden();
    ($this->scan)($session, '111', $this->outsider)->assertForbidden();
    $this->actingAs($this->member)->get(route('product-opname-sessions.show', $session->id))->assertOk();
});

it('mengizinkan akun dengan dua baris karyawan masuk lewat baris mana pun', function () {
    $user = User::factory()->create();
    $first = DB::table('employees')->insertGetId(['user_id' => $user->id, 'entity_id' => 1]);
    $second = DB::table('employees')->insertGetId(['user_id' => $user->id, 'entity_id' => 1]);

    expect($user->employee->id)->toBe($first);

    $session = ($this->startSession)();
    DB::table('product_opname_session_employees')->insert([
        'session_id' => $session->id,
        'employee_id' => $second,
        'ins_date' => now(),
        'ins_by' => $this->picEmployee,
    ]);

    $this->actingAs($user)->get(route('product-opname-sessions.show', $session->id))->assertOk();
    ($this->scan)($session, '111', $user)->assertOk();

    // Baris yang terdaftar sebagai PJ juga dikenali.
    $session->update(['pic_employee_id' => $second]);
    $this->actingAs($user)->post(route('product-opname-sessions.close-scan', $session->id))->assertRedirect();

    expect($session->refresh()->status)->toBe('review');
});

it('melarang dua sesi berjalan di toko yang sama', function () {
    ($this->startSession)();

    $this->actingAs($this->pic)->post(route('product-opname-sessions.store'), [
        'location_id' => 2,
        'opname_date' => now()->toDateString(),
        'pic_employee_id' => $this->picEmployee,
        'employee_ids' => [$this->memberEmployee],
        'start_now' => true,
    ])->assertSessionHasErrors('session');
});

it('mencatat setiap scan sebagai satu baris dan menjumlahkannya di tampilan', function () {
    $session = ($this->startSession)();

    ($this->scan)($session, '111')->assertOk()->assertJsonPath('counted', 1);
    ($this->scan)($session, '111', $this->pic)->assertOk()->assertJsonPath('counted', 2);

    expect(DB::table('product_opname_session_scans')->where('product_id', 10)->count())->toBe(2);
    expect(($this->stock)(10))->toBe(5);

    $live = $this->actingAs($this->member)
        ->getJson(route('product-opname-sessions.live', ['id' => $session->id, 'since' => 0]))
        ->assertOk();

    $live->assertJsonCount(1, 'items')->assertJsonPath('items.0.counted', 2);

    // Petugas menghitung tanpa melihat stok sistem.
    expect($live->json('items.0'))->not->toHaveKey('recorded_stock');
});

it('tidak menghitung dua kali scan yang dikirim ulang', function () {
    $session = ($this->startSession)();
    $uuid = (string) Str::uuid();

    ($this->scan)($session, '111', null, $uuid)->assertOk();
    ($this->scan)($session, '111', null, $uuid)->assertOk()->assertJsonPath('counted', 1);

    expect(DB::table('product_opname_session_scans')->count())->toBe(1);
});

it('hanya menerima barcode yang cocok persis dan menolak jasa', function () {
    $session = ($this->startSession)();

    ($this->scan)($session, '11')->assertUnprocessable();
    ($this->scan)($session, '999')->assertUnprocessable();
    ($this->scan)($session, '444')->assertUnprocessable();
    ($this->scan)($session, 'S10')->assertOk();

    expect(DB::table('product_opname_session_scans')->count())->toBe(1);
});

it('hanya mengizinkan PJ memasukkan produk secara manual', function () {
    $session = ($this->startSession)();
    $url = route('product-opname-sessions.store-scan', $session->id);

    $this->actingAs($this->member)
        ->postJson($url, ['uuid' => (string) Str::uuid(), 'product_id' => 10])
        ->assertForbidden();

    expect(DB::table('product_opname_session_scans')->count())->toBe(0);

    $this->actingAs($this->pic)
        ->postJson($url, ['uuid' => (string) Str::uuid(), 'product_id' => 10])
        ->assertOk()
        ->assertJsonPath('counted', 1);

    // Petugas tetap bisa memakai barcode.
    ($this->scan)($session, '111')->assertOk()->assertJsonPath('counted', 2);
});

it('selalu mencatat satu barang per scan dan menolak jumlah dari perangkat', function () {
    $session = ($this->startSession)();
    $url = route('product-opname-sessions.store-scan', $session->id);

    ($this->scan)($session, '111')->assertOk()->assertJsonPath('counted', 1);

    foreach ([24, 1, -1] as $qty) {
        $this->postJson($url, ['uuid' => (string) Str::uuid(), 'barcode' => '111', 'qty' => $qty])
            ->assertUnprocessable();
    }

    expect(DB::table('product_opname_session_scans')->count())->toBe(1);
    expect(DB::table('product_opname_session_scans')->value('qty'))->toEqual(1);
});

it('menampilkan barang tak ter-scan sebagai selisih, tanpa jasa, dengan filter default selisih', function () {
    $session = ($this->sessionInReview)();

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.show', $session->id))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('stock-opname-sessions/show', false)
            ->where('review.filter', 'difference')
            ->where('review.summary.difference', 2)
            ->where('review.summary.matched', 1)
            ->where('review.summary.all', 3)
            ->where('review.summary.pending', 2)
            ->where('review.summary.unscanned', 1)
            ->has('review.pagination.data', 2)
            ->where('review.pagination.data', function ($rows) {
                $unscanned = collect($rows)->firstWhere('product_id', 11);

                return $unscanned['recorded_stock'] === 7
                    && $unscanned['counted_stock'] === 0
                    && $unscanned['difference_stock'] === -7
                    && $unscanned['scanned'] === 0;
            }));

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.show', ['id' => $session->id, 'filter' => 'matched']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('review.pagination.data', 1)
            ->where('review.pagination.data.0.product_id', 12));
});

it('mengubah stok saat PJ menerima, dan menolak keputusan ganda', function () {
    $session = ($this->sessionInReview)();

    ($this->decide)($session, 10, 1)->assertSessionHasNoErrors();

    expect(($this->stock)(10))->toBe(3);
    expect(DB::table('product_stock_movements')->where('product_id', 10)->count())->toBe(1);
    expect(DB::table('product_opname_services')->value('status'))->toBe('approved');

    ($this->decide)($session, 10, 1)->assertSessionHasErrors('review');

    expect(($this->stock)(10))->toBe(3);
    expect(DB::table('product_stock_movements')->where('product_id', 10)->count())->toBe(1);

    // Barang tak ter-scan: stok menjadi 0.
    ($this->decide)($session, 11, 1)->assertSessionHasNoErrors();
    expect(($this->stock)(11))->toBe(0);

    $document = DB::table('product_opname_services')->first();
    expect((int) $document->difference_product_count)->toBe(2);
    expect((int) $document->difference_stock)->toBe(-9);
    expect(DB::table('product_opname_services')->count())->toBe(1);
});

it('tidak mengubah stok saat ditolak, menyembunyikan baris, tapi tetap mencatatnya', function () {
    $session = ($this->sessionInReview)();

    ($this->decide)($session, 11, 2)->assertSessionHasNoErrors();

    expect(($this->stock)(11))->toBe(7);
    expect(DB::table('product_stock_movements')->count())->toBe(0);
    expect(DB::table('product_opname_session_reviews')->where('product_id', 11)->value('decision'))->toEqual(2);

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.show', ['id' => $session->id, 'filter' => 'all']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('review.pagination.data', 2)
            ->where('review.summary.rejected', 1)
            ->where('review.summary.pending', 1));
});

it('tidak butuh aksi untuk baris cocok, dan hanya PJ yang bisa memutuskan', function () {
    $session = ($this->sessionInReview)();

    ($this->decide)($session, 12, 1)->assertSessionHasErrors('review');

    $this->actingAs($this->member)
        ->post(route('product-opname-sessions.decide', $session->id), ['product_id' => 10, 'decision' => 1])
        ->assertForbidden();

    expect(($this->stock)(10))->toBe(5);
});

it('mewajibkan persetujuan dan semua selisih diputuskan sebelum sesi diakhiri', function () {
    $session = ($this->sessionInReview)();
    $url = route('product-opname-sessions.finish', $session->id);

    $this->actingAs($this->pic)->post($url, ['agreement' => true])->assertSessionHasErrors('session');

    ($this->decide)($session, 10, 1);
    ($this->decide)($session, 11, 2);

    $this->actingAs($this->pic)->post($url, [])->assertSessionHasErrors('agreement');
    $this->actingAs($this->pic)->post($url, ['agreement' => false])->assertSessionHasErrors('agreement');

    expect($session->refresh()->end_at)->toBeNull();

    $this->actingAs($this->pic)->post($url, ['agreement' => true])->assertSessionHasNoErrors();

    expect($session->refresh()->status)->toBe('finished');
});

it('menolak scan produk yang sudah diputuskan setelah scan dibuka lagi', function () {
    $session = ($this->sessionInReview)();

    ($this->decide)($session, 10, 1);
    $this->actingAs($this->pic)->post(route('product-opname-sessions.reopen-scan', $session->id))->assertRedirect();

    ($this->scan)($session, '111')->assertUnprocessable();
    ($this->scan)($session, '222')->assertOk();
});

it('membuat laporan PDF setelah selesai', function () {
    $session = ($this->finishedSession)();

    $this->actingAs($this->member)
        ->get(route('product-opname-sessions.report', ['id' => $session->id, 'raw' => 1]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

it('tidak menyediakan laporan sebelum sesi selesai', function () {
    $session = ($this->sessionInReview)();

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.report', ['id' => $session->id, 'raw' => 1]))
        ->assertNotFound();
});

it('memvalidasi QR tanpa login dan menolak key yang diubah', function () {
    $session = ($this->finishedSession)();
    $key = app(DocumentSignatureService::class)->makeKey($session);

    // Tamu: pengguna dilepas tanpa event logout, karena listener SSO memakai fungsi khusus MySQL.
    $this->app['auth']->forgetGuards();

    $this->get(route('validasi-dokumen', ['k' => $key]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('validasi-dokumen/index', false)
            ->where('document.code', $session->code)
            ->where('document.signer', 'Budi Santoso'));

    $tampered = substr($key, 0, -2).(str_ends_with($key, 'AA') ? 'BB' : 'AA');

    $this->get(route('validasi-dokumen', ['k' => $tampered]))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('document', null));
    $this->get(route('validasi-dokumen'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('document', null));
});

it('menggerakkan stok lewat Revisi SO hanya untuk produk lampiran', function () {
    $parent = ($this->finishedSession)();

    $this->actingAs($this->pic)
        ->post(route('product-opname-sessions.store-revision-session', $parent->id))
        ->assertSessionHasNoErrors();

    $revision = ProductOpnameSession::query()->where('parent_session_id', $parent->id)->firstOrFail();
    $url = route('product-opname-sessions.store-revision', $revision->id);

    expect($revision->type)->toBe(2);
    expect(($this->stock)(11))->toBe(0);

    // Ketemu 2: stok bertambah.
    $this->actingAs($this->pic)
        ->post($url, ['product_id' => 11, 'adjustment_qty' => 2, 'reason' => 1])
        ->assertSessionHasNoErrors();
    expect(($this->stock)(11))->toBe(2);
    expect(DB::table('product_adjustment_stocks')->value('status'))->toBe('approved');

    // Hilang tidak mengubah stok.
    $this->actingAs($this->pic)
        ->post($url, ['product_id' => 11, 'adjustment_qty' => 0, 'reason' => 3])
        ->assertSessionHasNoErrors();
    expect(($this->stock)(11))->toBe(2);
    expect(DB::table('product_opname_session_revisions')->where('product_id', 11)->max('seq'))->toEqual(2);

    $this->actingAs($this->pic)
        ->post($url, ['product_id' => 11, 'adjustment_qty' => 1, 'reason' => 3])
        ->assertSessionHasErrors('revision');
    $this->actingAs($this->pic)
        ->post($url, ['product_id' => 11, 'adjustment_qty' => -5, 'reason' => 2])
        ->assertSessionHasErrors('revision');

    // Produk cocok tidak ada di lampiran.
    $this->actingAs($this->pic)
        ->post($url, ['product_id' => 12, 'adjustment_qty' => 1, 'reason' => 1])
        ->assertSessionHasErrors('revision');

    $this->actingAs($this->pic)
        ->post(route('product-opname-sessions.finish', $revision->id), ['agreement' => true])
        ->assertSessionHasNoErrors();

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.report', ['id' => $revision->id, 'raw' => 1]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

it('mengganti halaman SO lama dengan sesi SO', function () {
    $this->actingAs($this->pic)
        ->get('/product-opname-services')
        ->assertRedirect(route('product-opname-sessions.index'));

    $this->actingAs($this->pic)->get('/product-opname-services/create')->assertNotFound();

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.index'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('stock-opname-sessions/index', false));
});

it('menyiapkan akun yang login sebagai PJ awal di form', function () {
    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.create'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('stock-opname-sessions/form', false)
            ->where('currentEmployeeId', $this->picEmployee));
});

it('mengizinkan PJ membatalkan sesi yang belum punya data, lalu toko bisa dipakai lagi', function () {
    $session = ($this->startSession)();
    $url = route('product-opname-sessions.cancel', $session->id);

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.show', $session->id))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('viewer.can_cancel', true));

    $this->actingAs($this->member)->post($url)->assertForbidden();
    expect(ProductOpnameSession::query()->count())->toBe(1);

    $this->actingAs($this->pic)->post($url)->assertRedirect(route('product-opname-sessions.index'));

    expect(ProductOpnameSession::query()->count())->toBe(0);
    expect(DB::table('product_opname_session_employees')->count())->toBe(0);

    // Petugas yang masih membuka sesi mendapat 404, dan toko bisa dibuatkan sesi baru.
    $this->actingAs($this->member)
        ->getJson(route('product-opname-sessions.live', ['id' => $session->id, 'since' => 0]))
        ->assertNotFound();
    ($this->scan)($session, '111')->assertNotFound();

    expect(($this->startSession)()->status)->toBe('started');
});

it('menolak pembatalan sesi yang sudah punya data atau sudah selesai', function () {
    $session = ($this->startSession)();
    ($this->scan)($session, '111')->assertOk();

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.show', $session->id))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('viewer.can_cancel', false));

    $this->actingAs($this->pic)
        ->post(route('product-opname-sessions.cancel', $session->id))
        ->assertSessionHasErrors('session');

    expect(ProductOpnameSession::query()->count())->toBe(1);
    expect(DB::table('product_opname_session_scans')->count())->toBe(1);

    $session->update(['end_at' => now()]);

    $this->actingAs($this->pic)
        ->post(route('product-opname-sessions.cancel', $session->id))
        ->assertSessionHasErrors('session');
});

it('hanya menerima role Office dan CEO sebagai PJ dan karyawan', function () {
    $employeeWithRole = fn (int $roleId, string $name = 'Orang') => DB::table('employees')->insertGetId([
        'user_id' => User::factory()->create()->id,
        'entity_id' => 1,
        'role_id' => $roleId,
        'first_name' => $name,
    ]);

    $cashier = $employeeWithRole(1);
    $sales = $employeeWithRole(2);
    $owner = $employeeWithRole(4);
    $ceo = $employeeWithRole(5);

    $store = fn (array $employees, ?int $pic = null) => $this->actingAs($this->pic)
        ->post(route('product-opname-sessions.store'), [
            'location_id' => 2,
            'opname_date' => now()->toDateString(),
            'pic_employee_id' => $pic ?? $this->picEmployee,
            'employee_ids' => $employees,
        ]);

    $store([$cashier])->assertSessionHasErrors('employee_ids');
    $store([$sales])->assertSessionHasErrors('employee_ids');
    $store([$this->memberEmployee], $cashier)->assertSessionHasErrors('employee_ids');

    expect(ProductOpnameSession::query()->count())->toBe(0);

    // Office tidak dibatasi toko, dan Entity Owner dihitung sebagai CEO.
    $store([$this->memberEmployee, $owner], $ceo)->assertSessionHasNoErrors();

    expect(DB::table('product_opname_session_employees')->count())->toBe(3);
});

it('menampilkan satu baris per akun, hanya dari role yang boleh', function () {
    $user = User::factory()->create();
    $cashier = DB::table('employees')->insertGetId(['user_id' => $user->id, 'entity_id' => 1, 'role_id' => 1, 'first_name' => 'Balqis', 'last_name' => 'Kasir']);
    $office = DB::table('employees')->insertGetId(['user_id' => $user->id, 'entity_id' => 1, 'role_id' => 3, 'first_name' => 'Balqis', 'last_name' => 'Office']);
    $officeTwin = DB::table('employees')->insertGetId(['user_id' => $user->id, 'entity_id' => 1, 'role_id' => 3, 'first_name' => 'Balqis', 'last_name' => 'Office Dua']);

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.create'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('employeeOptions', function ($options) use ($cashier, $office, $officeTwin) {
                $values = collect($options)->pluck('value');

                return $values->contains($office)
                    && ! $values->contains($cashier)
                    && ! $values->contains($officeTwin);
            }));
});

it('mengizinkan PJ membatalkan scan dengan alasan, tanpa mengubah baris lama', function () {
    $session = ($this->startSession)();

    ($this->scan)($session, '111')->assertOk();
    ($this->scan)($session, '111')->assertOk();

    $scanId = (int) DB::table('product_opname_session_scans')->min('id');
    $url = route('product-opname-sessions.cancel-scan', ['id' => $session->id, 'scanId' => $scanId]);

    $this->actingAs($this->outsider)->postJson($url, ['reason' => 'salah scan'])->assertForbidden();
    $this->actingAs($this->pic)->postJson($url, [])->assertUnprocessable();
    $this->actingAs($this->pic)->postJson($url, ['reason' => 'x'])->assertUnprocessable();

    expect(DB::table('product_opname_session_scans')->count())->toBe(2);

    $this->actingAs($this->pic)->postJson($url, ['reason' => 'ter-scan dua kali'])->assertOk();

    $original = DB::table('product_opname_session_scans')->find($scanId);
    $correction = DB::table('product_opname_session_scans')->where('cancelled_scan_id', $scanId)->first();

    expect((int) $original->qty)->toBe(1);
    expect((int) $correction->qty)->toBe(-1);
    expect($correction->reason)->toBe('ter-scan dua kali');
    expect((int) $correction->ins_by)->toBe($this->picEmployee);

    // Tidak bisa dibatalkan dua kali, dan baris koreksi tidak bisa dibatalkan.
    $this->actingAs($this->pic)->postJson($url, ['reason' => 'ter-scan dua kali'])->assertUnprocessable();
    $this->actingAs($this->pic)
        ->postJson(route('product-opname-sessions.cancel-scan', ['id' => $session->id, 'scanId' => $correction->id]), ['reason' => 'balik lagi'])
        ->assertUnprocessable();

    $live = $this->actingAs($this->member)
        ->getJson(route('product-opname-sessions.live', ['id' => $session->id, 'since' => 0]))
        ->assertOk();

    $live->assertJsonPath('items.0.counted', 1);
    // Hitungan per orang tidak memasukkan scan batal, dan PJ tidak mendapat angka minus.
    expect(collect($live->json('employees'))->sum('qty'))->toBe(1);
    expect(collect($live->json('employees'))->pluck('employee_id')->all())->toBe([$this->memberEmployee]);

    $this->actingAs($this->pic)
        ->getJson(route('product-opname-sessions.product-scans', ['id' => $session->id, 'productId' => 10]))
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonPath('1.cancelled', true)
        ->assertJsonPath('1.reason', 'ter-scan dua kali')
        ->assertJsonPath('0.cancelled', false);

    // Selama scan dibuka anggota sesi juga boleh melihat rincian, karyawan lain tidak.
    $this->actingAs($this->member)
        ->getJson(route('product-opname-sessions.product-scans', ['id' => $session->id, 'productId' => 10]))
        ->assertOk()
        ->assertJsonCount(2);

    $this->actingAs($this->outsider)
        ->getJson(route('product-opname-sessions.product-scans', ['id' => $session->id, 'productId' => 10]))
        ->assertForbidden();
});

it('mengizinkan anggota sesi membatalkan scan selama scan dibuka, dan hanya PJ setelah ditutup', function () {
    $session = ($this->startSession)();

    ($this->scan)($session, '111')->assertOk();
    ($this->scan)($session, '111')->assertOk();

    [$first, $second] = DB::table('product_opname_session_scans')->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all();
    $url = fn (int $scanId) => route('product-opname-sessions.cancel-scan', ['id' => $session->id, 'scanId' => $scanId]);

    $this->actingAs($this->member)
        ->get(route('product-opname-sessions.show', $session->id))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('viewer.can_correct', true));

    $this->actingAs($this->member)->postJson($url($first), ['reason' => 'ter-scan dua kali'])->assertOk();

    $correction = DB::table('product_opname_session_scans')->where('cancelled_scan_id', $first)->first();
    expect((int) $correction->ins_by)->toBe($this->memberEmployee);

    $this->actingAs($this->pic)->post(route('product-opname-sessions.close-scan', $session->id))->assertRedirect();

    $this->actingAs($this->member)
        ->get(route('product-opname-sessions.show', $session->id))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('viewer.can_correct', false));

    $this->actingAs($this->member)->postJson($url($second), ['reason' => 'salah barang'])->assertForbidden();
    $this->actingAs($this->member)
        ->getJson(route('product-opname-sessions.product-scans', ['id' => $session->id, 'productId' => 10]))
        ->assertForbidden();

    // Kalau PJ menutup scan tepat sebelum pembatalan anggota diproses, service tetap menolak.
    expect(fn () => app(ProductOpnameScanService::class)->cancelScan($session->fresh(), $second, 'salah barang', false))
        ->toThrow(ValidationException::class);

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.show', $session->id))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('viewer.can_correct', true));

    $this->actingAs($this->pic)->postJson($url($second), ['reason' => 'salah barang'])->assertOk();

    expect((int) DB::table('product_opname_session_scans')->where('product_id', 10)->sum('qty'))->toBe(0);
});

it('mengubah angka review setelah koreksi, dan menolak koreksi produk yang sudah diputuskan', function () {
    $session = ($this->sessionInReview)();
    $first = (int) DB::table('product_opname_session_scans')->where('product_id', 10)->min('id');

    $this->actingAs($this->pic)
        ->postJson(route('product-opname-sessions.cancel-scan', ['id' => $session->id, 'scanId' => $first]), ['reason' => 'dobel'])
        ->assertOk();

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.show', ['id' => $session->id, 'search' => 'Scan Kurang']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('review.pagination.data.0.counted_stock', 2)
            ->where('review.pagination.data.0.difference_stock', -3)
            ->where('review.pagination.data.0.cancelled_scans', 1));

    ($this->decide)($session, 10, 1)->assertSessionHasNoErrors();
    expect(($this->stock)(10))->toBe(2);

    $second = (int) DB::table('product_opname_session_scans')->where('product_id', 10)->where('qty', 1)->max('id');

    $this->actingAs($this->pic)
        ->postJson(route('product-opname-sessions.cancel-scan', ['id' => $session->id, 'scanId' => $second]), ['reason' => 'telat'])
        ->assertUnprocessable();

    expect(($this->stock)(10))->toBe(2);
});

it('menandai scan berdekatan, melebihi stok, dan rincian per petugas', function () {
    $session = ($this->startSession)();
    $insert = fn (int $productId, int $employee, string $at) => DB::table('product_opname_session_scans')->insert([
        'uuid' => (string) Str::uuid(),
        'session_id' => $session->id,
        'product_id' => $productId,
        'barcode' => 'X',
        'qty' => 1,
        'recorded_stock' => 1,
        'ins_date' => $at,
        'ins_by' => $employee,
    ]);

    // Produk 12: orang sama berjarak 1 detik (dobel), lalu 10 detik (wajar), lalu orang lain.
    $insert(12, $this->memberEmployee, '2026-11-04 10:00:00');
    $insert(12, $this->memberEmployee, '2026-11-04 10:00:01');
    $insert(12, $this->memberEmployee, '2026-11-04 10:00:11');
    $insert(12, $this->picEmployee, '2026-11-04 10:00:12');
    // Produk 10: jarak wajar, tidak ditandai.
    $insert(10, $this->memberEmployee, '2026-11-04 10:01:00');
    $insert(10, $this->memberEmployee, '2026-11-04 10:01:30');

    $this->actingAs($this->pic)->post(route('product-opname-sessions.close-scan', $session->id));

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.show', ['id' => $session->id, 'filter' => 'all']))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('review.suspect_count', 1)
            ->where('review.pagination.data', function ($rows) {
                $rows = collect($rows)->keyBy('product_id');

                return $rows[12]['rapid_repeats'] === 1
                    && $rows[12]['counted_stock'] === 4
                    && $rows[12]['difference_stock'] === 3
                    && count($rows[12]['scanners']) === 2
                    && $rows[10]['rapid_repeats'] === 0
                    && $rows[11]['scanners'] === [];
            })
            // Yang mungkin dobel naik ke atas walau selisihnya lebih kecil dari produk 11.
            ->where('review.pagination.data.0.product_id', 12));
});

it('mencatat jumlah koreksi di laporan tanpa error', function () {
    $session = ($this->sessionInReview)();
    $first = (int) DB::table('product_opname_session_scans')->where('product_id', 12)->min('id');

    $this->actingAs($this->pic)
        ->postJson(route('product-opname-sessions.cancel-scan', ['id' => $session->id, 'scanId' => $first]), ['reason' => 'salah barang'])
        ->assertOk();

    ($this->decide)($session, 10, 1);
    ($this->decide)($session, 11, 2);
    ($this->decide)($session, 12, 2);

    $this->actingAs($this->pic)
        ->post(route('product-opname-sessions.finish', $session->id), ['agreement' => true])
        ->assertSessionHasNoErrors();

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.report', ['id' => $session->id, 'raw' => 1]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

it('tetap bisa scan, melihat hasil, dan review walau SQL koreksi belum dijalankan', function () {
    Schema::table('product_opname_session_scans', function (Blueprint $table) {
        $table->dropUnique(['cancelled_scan_id']);
        $table->dropColumn(['cancelled_scan_id', 'reason']);
    });
    Once::flush();

    $session = ($this->startSession)();

    ($this->scan)($session, '111')->assertOk()->assertJsonPath('counted', 1);

    $this->actingAs($this->member)
        ->getJson(route('product-opname-sessions.live', ['id' => $session->id, 'since' => 0]))
        ->assertOk()
        ->assertJsonPath('items.0.counted', 1)
        ->assertJsonPath('employees.0.qty', 1);

    $this->actingAs($this->pic)->post(route('product-opname-sessions.close-scan', $session->id))->assertRedirect();

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.show', $session->id))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('viewer.can_correct', false)
            ->where('review.summary.pending', 3));

    $scanId = (int) DB::table('product_opname_session_scans')->value('id');

    $this->actingAs($this->pic)
        ->postJson(route('product-opname-sessions.cancel-scan', ['id' => $session->id, 'scanId' => $scanId]), ['reason' => 'salah scan'])
        ->assertStatus(409);
});

it('memakai 10 baris per halaman di review, dan menolak nilai di luar pilihan', function () {
    $session = ($this->sessionInReview)();

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.show', $session->id))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('review.pagination.per_page', 10));

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.show', ['id' => $session->id, 'per_page' => 25]))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('review.pagination.per_page', 25));

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.show', ['id' => $session->id, 'per_page' => 15]))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('review.pagination.per_page', 10));
});

it('menampilkan QR sesi hanya untuk PJ, berisi alamat sesi', function () {
    $session = ($this->startSession)();
    $url = route('product-opname-sessions.share', $session->id);

    $this->actingAs($this->member)->get($url)->assertForbidden();

    $this->actingAs($this->pic)
        ->get($url)
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('stock-opname-sessions/share', false)
            ->where('session.code', $session->code)
            ->where('url', route('product-opname-sessions.show', $session->id))
            ->where('qr.size', fn ($size) => $size > 20)
            ->where('qr.path', fn ($path) => str_starts_with($path, 'M')));

    $session->update(['end_at' => now()]);

    $this->actingAs($this->pic)->get($url)->assertRedirect(route('product-opname-sessions.show', $session->id));
});

it('melempar tamu ke login lalu kembali ke sesi setelah login', function () {
    $session = ($this->startSession)();
    $target = route('product-opname-sessions.show', $session->id);

    auth()->guard('web')->forgetUser();
    $this->app['auth']->forgetGuards();

    $this->get($target)->assertRedirect(route('login'));
    expect(session('url.intended'))->toBe($target);

    $this->post(route('login.store'), ['email' => $this->member->email, 'password' => 'salah'])
        ->assertSessionHasErrors('email');
    $this->assertGuest();

    $this->post(route('login.store'), ['email' => $this->member->email, 'password' => 'password'])
        ->assertRedirect($target);
    $this->assertAuthenticatedAs($this->member);
});

it('menampilkan halaman akses ditolak untuk akun di luar daftar dan usaha lain', function () {
    $session = ($this->startSession)();

    $this->actingAs($this->outsider)
        ->get(route('product-opname-sessions.show', $session->id))
        ->assertForbidden()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('errors/index', false)
            ->where('status', 403)
            ->where('message', 'Anda tidak terdaftar pada sesi SO ini.'));

    $other = User::factory()->create();
    DB::table('entities')->insert(['id' => 2, 'name' => 'Usaha Lain']);
    DB::table('employees')->insert(['user_id' => $other->id, 'entity_id' => 2, 'role_id' => 3]);

    $this->actingAs($other)
        ->get(route('product-opname-sessions.show', $session->id))
        ->assertForbidden()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('errors/index', false));
});

it('menampilkan halaman error aplikasi, sedangkan JSON tetap JSON', function () {
    Route::middleware('web')->get('/__uji-500', fn () => throw new RuntimeException('rahasia database'));

    $this->actingAs($this->pic)
        ->get('/alamat-yang-tidak-ada')
        ->assertNotFound()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('errors/index', false)
            ->where('status', 404)
            ->where('message', null));

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.show', 999999))
        ->assertNotFound()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('errors/index', false)->where('message', null));

    config(['app.debug' => false]);

    $this->get('/__uji-500')
        ->assertStatus(500)
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('errors/index', false)
            ->where('status', 500)
            ->where('message', null))
        ->assertDontSee('rahasia database');

    $this->actingAs($this->pic)
        ->getJson(route('product-opname-sessions.live', ['id' => 999999, 'since' => 0]))
        ->assertNotFound()
        ->assertJsonMissingPath('component');
});

it('merangkum sesuai, diterima, dan ditolak, lalu memisahkan lampiran yang ditolak', function () {
    $session = ($this->sessionInReview)();

    ($this->decide)($session, 10, 1)->assertSessionHasNoErrors();
    ($this->decide)($session, 11, 2)->assertSessionHasNoErrors();

    $reviews = app(ProductOpnameReviewService::class);

    expect($reviews->reportTotals($session))->toBe([
        'matched' => ['products' => 1, 'recorded' => 1, 'counted' => 1, 'difference' => 0],
        'accepted' => ['products' => 1, 'recorded' => 5, 'counted' => 3, 'difference' => -2],
        'rejected' => ['products' => 1, 'recorded' => 7, 'counted' => 0, 'difference' => -7],
    ]);

    expect($reviews->attachmentRows($session)->pluck('product_id')->all())->toBe([10]);
    expect($reviews->rejectedRows($session)->pluck('product_id')->all())->toBe([11]);

    // Yang ditolak tidak mengubah stok, walau tercatat di laporan.
    expect(($this->stock)(11))->toBe(7);

    $this->actingAs($this->pic)
        ->post(route('product-opname-sessions.finish', $session->id), ['agreement' => true])
        ->assertSessionHasNoErrors();

    $this->actingAs($this->pic)
        ->get(route('product-opname-sessions.report', ['id' => $session->id, 'raw' => 1]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});
