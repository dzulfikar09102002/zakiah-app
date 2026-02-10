<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SellingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use PHPUnit\Framework\Attributes\Group;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
    Route::prefix('/roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/store', [RoleController::class, 'store'])->name('roles.store');
        Route::post('/{role}/update', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/{role}/delete', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
    Route::prefix('/employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('employee');
        Route::post('/store', [EmployeeController::class, 'index'])->name('employee.store');
        Route::get('/create', [EmployeeController::class, 'create'])->name('employees.create');
    });
    Route::prefix('sellings')->group(function () {
        Route::get('/', [SellingController::class, 'summary'])->name('sellings.summary');
    });
    Route::prefix('/products')->group(function () {});
});

require __DIR__ . '/settings.php';
