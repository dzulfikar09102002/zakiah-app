<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Data milik entity lain tidak boleh bisa diubah, dihapus, atau dipulihkan
 * hanya dengan mengganti ID di URL.
 */
beforeEach(function () {
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
        $table->softDeletes();
        $table->timestamps();
    });

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

    DB::table('entities')->insert([
        ['id' => 1, 'name' => 'Entity Satu'],
        ['id' => 2, 'name' => 'Entity Dua'],
    ]);

    $this->user = User::factory()->create();
    DB::table('employees')->insert(['user_id' => $this->user->id, 'entity_id' => 1]);

    foreach (['product_units', 'product_categories'] as $name) {
        DB::table($name)->insert([
            ['id' => 1, 'entity_id' => 1, 'name' => 'Milik Sendiri', 'deleted_at' => null],
            ['id' => 2, 'entity_id' => 2, 'name' => 'Milik Orang Lain', 'deleted_at' => null],
            ['id' => 3, 'entity_id' => 2, 'name' => 'Terhapus Orang Lain', 'deleted_at' => now()],
        ]);
    }
});

dataset('modules', [
    'unit produk' => ['product_units', 'product-units'],
    'kategori produk' => ['product_categories', 'product-categories'],
]);

it('menolak ubah dan hapus data entity lain', function (string $table, string $route) {
    $this->actingAs($this->user)->put(route("{$route}.update", 2), ['name' => 'Dibajak', 'entity_id' => 1])->assertNotFound();
    $this->actingAs($this->user)->delete(route("{$route}.destroy", 2))->assertNotFound();

    $row = DB::table($table)->find(2);
    expect($row->name)->toBe('Milik Orang Lain');
    expect($row->deleted_at)->toBeNull();
})->with('modules');

it('menolak memulihkan data entity lain', function (string $table, string $route) {
    $this->actingAs($this->user)->post(route("{$route}.restore", 3))->assertNotFound();

    expect(DB::table($table)->find(3)->deleted_at)->not->toBeNull();
})->with('modules');

it('tetap mengizinkan ubah, hapus, dan pulihkan data sendiri', function (string $table, string $route) {
    $this->actingAs($this->user)->put(route("{$route}.update", 1), ['name' => 'Nama Baru', 'entity_id' => 1])->assertRedirect();
    expect(DB::table($table)->find(1)->name)->toBe('Nama Baru');

    $this->actingAs($this->user)->delete(route("{$route}.destroy", 1))->assertRedirect();
    expect(DB::table($table)->find(1)->deleted_at)->not->toBeNull();

    $this->actingAs($this->user)->post(route("{$route}.restore", 1))->assertRedirect();
    expect(DB::table($table)->find(1)->deleted_at)->toBeNull();
})->with('modules');
