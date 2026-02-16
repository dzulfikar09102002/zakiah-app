<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SellingController;
use App\Http\Controllers\StockRemainingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('/roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/store', [RoleController::class, 'store'])->name('roles.store');
        Route::post('/{role}/update', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/{role}/delete', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
    Route::prefix('/employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/store', [EmployeeController::class, 'store'])->name('employees.store');
    });
    Route::prefix('sellings')->group(function () {
        Route::get('/', [SellingController::class, 'summary'])->name('sellings.summary');
    });

    Route::prefix('/products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('products.index');
        Route::get('/create', [ProductController::class, 'index'])->name('products.create');
    });

    Route::prefix('/categories')->group(function () {
        Route::get('/', [ProductCategoryController::class, 'index'])->name('categories.index');
        Route::post('/store', [ProductCategoryController::class, 'store'])->name('categories.store');
        Route::patch('/{productCategory}', [ProductCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/{productCategory}', [ProductCategoryController::class, 'destroy'])->name('categories.delete');
        Route::post('/{id}/restore', [ProductCategoryController::class, 'restore'])->name('categories.restore');
        Route::get('/deleted', [ProductCategoryController::class, 'deleted'])->name('categories.deleted');
    });

    Route::prefix('/units')->group(function () {
        Route::get('/', [ProductUnitController::class, 'index'])->name('units.index');
        Route::get('/create', [ProductUnitController::class, 'index'])->name('units.create');
    });

    Route::prefix('/locations')->group(function () {
        Route::get('/', [LocationController::class, 'index'])->name('locations.index');
        Route::post('/store', [LocationController::class, 'store'])->name('locations.store');
    });
    Route::prefix('/stockreports')->group(function () {
        Route::get('/remaining', [StockRemainingController::class, 'index'])->name('stockreports.remaining');
    });
});

require __DIR__.'/settings.php';
