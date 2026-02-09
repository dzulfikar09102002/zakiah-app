<?php

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use PHPUnit\Framework\Attributes\Group;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('administrasi/role') ->group(function (){
    Route::get('/', [RoleController::class, 'index'])->middleware(['auth', 'verified'])->name('roles');
    Route::post('/store', [RoleController::class, 'store'])->middleware(['auth', 'verified'])->name('roles.store');
    Route::post('/{role}/update', [RoleController::class, 'update'])->middleware(['auth', 'verified'])->name('roles.update');
    Route::delete('/{role}/delete', [RoleController::class, 'destroy'])->middleware(['auth', 'verified'])->name('roles.destroy');
});

Route::prefix('administrasi/karyawan') ->group(function (){
    Route::get('/', [EmployeeController::class, 'index'])->middleware(['auth', 'verified'])->name('employee');
    Route::post('/store', [EmployeeController::class, 'index'])->middleware(['auth', 'verified'])->name('employee.store');
    Route::get('/create', [EmployeeController::class, 'create'])->middleware(['auth', 'verified'])
    ->name('employees.create');
        
});

Route::prefix('laporan/penjualan') ->group(function (){
    Route::get('/', function(){
return Inertia::render('report/sale/summary');
    })->middleware(['auth', 'verified'])->name('summary');
});
require __DIR__.'/settings.php';
