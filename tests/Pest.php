<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Tabel domain minimal yang dibaca setiap halaman login (HandleInertiaRequests memuat
 * user->entity lewat employees). Skema aslinya ada di MySQL, bukan di database/migrations,
 * jadi tes yang membuka halaman ber-login membuatnya di SQLite memori.
 */
function createEntitySchema(): void
{
    if (! Schema::hasTable('entities')) {
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('employees')) {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('entity_id');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
