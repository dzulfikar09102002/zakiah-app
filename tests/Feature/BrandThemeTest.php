<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Testing\AssertableInertia as Assert;

/**
 * Tema warna mengikuti entity user (id 1 = Secaca, id 3 = Zakiah), juga di halaman 404:
 * URL yang tidak terdaftar tetap melewati middleware web sehingga user yang login dikenali.
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
        $table->softDeletes();
        $table->timestamps();
    });

    DB::table('entities')->insert([
        ['id' => 1, 'name' => 'Secaca'],
        ['id' => 3, 'name' => 'Zakiah'],
    ]);
});

function userOfEntity(int $entityId): User
{
    $user = User::factory()->create();
    DB::table('employees')->insert(['user_id' => $user->id, 'entity_id' => $entityId]);

    return $user;
}

it('memakai tema entity di halaman 404 untuk user yang login', function (int $entityId, string $theme, string $name) {
    $this->actingAs(userOfEntity($entityId))
        ->get('/halaman-yang-tidak-ada')
        ->assertNotFound()
        ->assertSee('data-brand="'.$theme.'"', false)
        ->assertInertia(fn (Assert $page) => $page
            ->component('errors/index')
            ->where('status', 404)
            ->where('branding.theme', $theme)
            ->where('branding.name', $name));
})->with([
    'secaca' => [1, 'secaca', 'Secaca'],
    'zakiah' => [3, 'zakiah', 'Zakiah'],
]);

it('memakai tema perpaduan di halaman 404 untuk tamu', function () {
    $this->get('/halaman-yang-tidak-ada')
        ->assertNotFound()
        ->assertInertia(fn (Assert $page) => $page
            ->component('errors/index')
            ->where('branding.theme', 'duo'));
});
