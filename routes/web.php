<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CustomerCategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPointMovementController;
use App\Http\Controllers\DailySaleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\OrderTypeController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductAdjustmentStockController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImportServiceController;
use App\Http\Controllers\ProductOpnameServiceController;
use App\Http\Controllers\ProductTransferServiceController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\Report\ReportByProductController;
use App\Http\Controllers\Report\ReportEmployeeDetailController;
use App\Http\Controllers\Report\ReportEmployeeSummaryController;
use App\Http\Controllers\Report\ReportSalesByLocationController;
use App\Http\Controllers\Report\ReportSalesController;
use App\Http\Controllers\Report\ReportStockCardController;
use App\Http\Controllers\Report\ReportStockMovementController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleTransactionController;
use App\Http\Controllers\SellingController;
use App\Http\Controllers\StockRemainingController;
use App\Http\Controllers\TaxController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');
    
    Route::resource('roles', RoleController::class);
    Route::resource('employees', EmployeeController::class);
    Route::resource('products', ProductController::class);
    Route::resource('product-categories', ProductCategoryController::class)->except(['show']);
    Route::resource('product-units', ProductUnitController::class);
    Route::get('product-units/deleted', [ProductUnitController::class, 'deleted'])
    ->name('product-units.deleted');
    Route::resource('locations', LocationController::class);
    // Payment Methods
    Route::get('payment-methods/deleted', [PaymentMethodController::class, 'deleted'])
        ->name('payment-methods.deleted');
    Route::resource('payment-methods', PaymentMethodController::class);
    Route::resource('order-types', OrderTypeController::class);

    // Selling Summary
    Route::get('sellings/summary', [SellingController::class, 'summary'])
        ->name('sellings.summary');

    // Stock Remaining
    Route::get('stock-remaining', [StockRemainingController::class, 'chooseLocation'])
        ->name('stock-remaining.choose-location');

    Route::get('stock-remaining/{location}', [StockRemainingController::class, 'report'])
        ->name('stock-remaining.report');

    // Categories
    Route::get('product-categories/deleted', [ProductCategoryController::class, 'deleted'])
        ->name('product-categories.deleted');

    Route::post('product-categories/{id}/restore', [ProductCategoryController::class, 'restore'])
        ->name('product-categories.restore');


    Route::post('payment-methods/{id}/restore', [PaymentMethodController::class, 'restore'])
        ->name('payment-methods.restore');

    // Order Types
    Route::get('order-types/deleted', [OrderTypeController::class, 'deleted'])
        ->name('order-types.deleted');

    Route::post('order-types/{id}/restore', [OrderTypeController::class, 'restore'])
        ->name('order-types.restore');

                /*
        |--------------------------------------------------------------------------
        | ENTITY
        |--------------------------------------------------------------------------
        */
        Route::resource('entities', EntityController::class)
        ->only(['show', 'update']);

        /*
        |--------------------------------------------------------------------------
        | MASTER TAMBAHAN
        |--------------------------------------------------------------------------
        */
        Route::resource('brands', BrandController::class)->except(['destroy']);
        Route::resource('taxes', TaxController::class)->except(['destroy']);

        /*
        |--------------------------------------------------------------------------
        | DROPDOWN TAMBAHAN
        |--------------------------------------------------------------------------
        */
        Route::get('locations-dropdown', [LocationController::class, 'dropdown'])
        ->name('locations.dropdown');

        Route::get('products-dropdown', [ProductController::class, 'dropdown'])
        ->name('products.dropdown');

        Route::get('products-stock', [ProductController::class, 'stock'])
        ->name('products.stock');

        Route::get('products-export', [ProductController::class, 'export'])
        ->name('products.export');

        /*
        |--------------------------------------------------------------------------
        | CUSTOMER CATEGORY
        |--------------------------------------------------------------------------
        */
        Route::resource('customer-categories', CustomerCategoryController::class)
        ->except(['destroy']);

        Route::patch('customer-categories/{id}/activate', [CustomerCategoryController::class, 'activate'])
        ->name('customer-categories.activate');

        Route::patch('customer-categories/{id}/archive', [CustomerCategoryController::class, 'archive'])
        ->name('customer-categories.archive');

        Route::get('customer-categories-dropdown', [CustomerCategoryController::class, 'dropdown'])
        ->name('customer-categories.dropdown');

        /*
        |--------------------------------------------------------------------------
        | PRODUCT SERVICES
        |--------------------------------------------------------------------------
        */
        Route::resource('product-import-services', ProductImportServiceController::class)
        ->except(['destroy']);

        Route::post('product-import-services/upload', [ProductImportServiceController::class, 'upload'])
        ->name('product-import-services.upload');

        Route::resource('product-transfer-services', ProductTransferServiceController::class)
        ->except(['destroy']);

        Route::post('product-transfer-services/{product_transfer_service}/approve', [ProductTransferServiceController::class, 'approve'])
        ->name('product-transfer-services.approve');

        Route::post('product-transfer-services/{product_transfer_service}/reject', [ProductTransferServiceController::class, 'reject'])
        ->name('product-transfer-services.reject');

        Route::post('product-transfer-services/{product_transfer_service}/cancel', [ProductTransferServiceController::class, 'cancel'])
        ->name('product-transfer-services.cancel');

        Route::resource('product-opname-services', ProductOpnameServiceController::class);

        Route::get('product-opname-services/{id}/preview', [ProductOpnameServiceController::class, 'preview'])
        ->name('product-opname-services.preview');

        Route::resource('product-adjustment-stocks', ProductAdjustmentStockController::class);

        Route::post('product-adjustment-stocks/{id}/approve', [ProductAdjustmentStockController::class, 'approve'])
        ->name('product-adjustment-stocks.approve');

        Route::post('product-adjustment-stocks/{id}/reject', [ProductAdjustmentStockController::class, 'reject'])
        ->name('product-adjustment-stocks.reject');

        /*
        |--------------------------------------------------------------------------
        | PROMO
        |--------------------------------------------------------------------------
        */
        Route::resource('promos', PromoController::class)->except(['destroy']);

        /*
        |--------------------------------------------------------------------------
        | EMPLOYEE TAMBAHAN
        |--------------------------------------------------------------------------
        */
        Route::get('employees-dropdown', [EmployeeController::class, 'dropdown'])
        ->name('employees.dropdown');

        /*
        |--------------------------------------------------------------------------
        | SALES & CUSTOMER
        |--------------------------------------------------------------------------
        */
        Route::resource('daily-sales', DailySaleController::class)
        ->only(['index', 'show']);

        Route::resource('sale-transactions', SaleTransactionController::class)
        ->only(['index', 'show']);

        Route::patch('sale-transactions/{id}/void', [SaleTransactionController::class, 'void'])
        ->name('sale-transactions.void');

        Route::resource('customers', CustomerController::class)
        ->only(['index', 'show']);

        Route::patch('customers/{id}/activate', [CustomerController::class, 'activate'])
        ->name('customers.activate');

        Route::patch('customers/{id}/archive', [CustomerController::class, 'archive'])
        ->name('customers.archive');

        Route::resource('customer-point-movements', CustomerPointMovementController::class)
        ->only(['index']);

        /*
        |--------------------------------------------------------------------------
        | LOYALTY
        |--------------------------------------------------------------------------
        */
        Route::resource('loyalties', LoyaltyController::class)->except(['destroy']);

        Route::patch('loyalties/{id}/activate', [LoyaltyController::class, 'activate'])
        ->name('loyalties.activate');

        Route::patch('loyalties/{id}/deactivate', [LoyaltyController::class, 'deactivate'])
        ->name('loyalties.deactivate');

        Route::patch('loyalties/{id}/archive', [LoyaltyController::class, 'archive'])
        ->name('loyalties.archive');

        /*
        |--------------------------------------------------------------------------
        | REPORT
        |--------------------------------------------------------------------------
        */
        Route::prefix('report')->group(function () {

        Route::resource('report-by-products', ReportByProductController::class)->only(['index']);
        Route::resource('report-sales', ReportSalesController::class)->only(['index']);
        Route::resource('report-sales-by-location', ReportSalesByLocationController::class)->only(['index']);
        Route::resource('report-stock-movement', ReportStockMovementController::class)->only(['index']);
        Route::resource('report-stock-card', ReportStockCardController::class)->only(['index']);
        Route::resource('report-employee-summary', ReportEmployeeSummaryController::class)->only(['index']);
        Route::resource('report-employee-detail', ReportEmployeeDetailController::class)->only(['index']);
        });
});

require __DIR__.'/settings.php';