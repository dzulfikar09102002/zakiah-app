<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Data tabel (pagination) dikirim belakangan lewat Inertia::defer: kunjungan pertama hanya
 * kerangka halaman, lalu Inertia otomatis meminta prop "pagination" pada request kedua.
 */
beforeEach(function () {
    createEntitySchema();

    foreach (['product_units', 'product_categories'] as $name) {
        Schema::create($name, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('entity_id');
            $table->string('name');
            $table->string('search_name')->nullable();
            $table->string('status')->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    DB::table('entities')->insert(['id' => 1, 'name' => 'Zakiah']);
    $this->user = User::factory()->create();
    DB::table('employees')->insert(['user_id' => $this->user->id, 'entity_id' => 1]);

    foreach (['product_units', 'product_categories'] as $name) {
        DB::table($name)->insert([
            ['entity_id' => 1, 'name' => 'Aktif', 'deleted_at' => null],
            ['entity_id' => 1, 'name' => 'Terhapus', 'deleted_at' => now()],
        ]);
    }
});

it('mengirim data tabel belakangan (deferred)', function (string $url, string $component, string $expectedName) {
    $this->actingAs($this->user)
        ->get($url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component($component)
            ->missing('pagination')
            ->loadDeferredProps(fn (Assert $reload) => $reload
                ->has('pagination.data', 1)
                ->where('pagination.data.0.name', $expectedName)));
})->with([
    'satuan produk' => ['/product-units', 'units/index', 'Aktif'],
    'satuan produk terhapus' => ['/product-units/deleted', 'units/index', 'Terhapus'],
    'kategori produk' => ['/product-categories', 'categories/index', 'Aktif'],
    'kategori produk terhapus' => ['/product-categories/deleted', 'categories/index', 'Terhapus'],
]);

it('tetap mengirim data tabel saat reload parsial only=pagination (setelah simpan/hapus)', function () {
    $this->actingAs($this->user)
        ->get('/product-units', [
            'X-Inertia' => 'true',
            'X-Inertia-Partial-Component' => 'units/index',
            'X-Inertia-Partial-Data' => 'pagination',
            'X-Inertia-Version' => app(HandleInertiaRequests::class)->version(request()),
        ])
        ->assertOk()
        ->assertJsonPath('props.pagination.data.0.name', 'Aktif');
});
