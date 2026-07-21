<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\ComingSoonController;
use App\Http\Controllers\CustomerCategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPointMovementController;
use App\Http\Controllers\DailySaleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeReportDetailController;
use App\Http\Controllers\EmployeeReportSummaryController;
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
use App\Http\Controllers\SalesReportByLocationController;
use App\Http\Controllers\SalesReportByProductController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\SaleTransactionController;
use App\Http\Controllers\SellingController;
use App\Http\Controllers\StockRemainingController;
use App\Http\Controllers\TaxController;
use App\Http\Middleware\EntityCheckingMiddleware;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('coming-soon', [ComingSoonController::class, 'index'])->name('comingsoon.index');
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    Route::get(
    '/dashboard/latest-transaction-id',
    [DashboardController::class, 'latestTransactionId']
);

    Route::resource('roles', RoleController::class);

    Route::resource('employees', EmployeeController::class)->except('show');
    Route::get('employees/deleted', [EmployeeController::class, 'deleted'])
        ->name('employees.deleted');
    Route::post('employees/{id}/restore', [EmployeeController::class, 'restore'])
        ->name('employees.restore');

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('products.index');
        Route::post('/', [ProductController::class, 'store'])->name('products.store')->middleware(EntityCheckingMiddleware::class);
        Route::post('/import', [ProductController::class, 'import'])->name('products.import')->middleware(EntityCheckingMiddleware::class);
        Route::get('/import-page', [ProductController::class, 'importPage'])->name('products.importPage');
        Route::put('/{product}', [ProductController::class, 'update'])->name('products.update')->middleware(EntityCheckingMiddleware::class);
        Route::post('/import/stock-lookup', [ProductController::class, 'importStockLookup'])
    ->name('products.import.stock-lookup');
    });
    Route::resource('product-categories', ProductCategoryController::class)->except(['show']);

    Route::resource('product-units', ProductUnitController::class)->except(['show']);
    Route::get('product-units/deleted', [ProductUnitController::class, 'deleted'])
        ->name('product-units.deleted');
    Route::post('product-units/{id}/restore', [ProductUnitController::class, 'restore'])
        ->name('product-units.restore');

    Route::resource('locations', LocationController::class)->except('show');
    Route::get('locations/deleted', [LocationController::class, 'deleted'])
        ->name('locations.deleted');
    Route::post('locations/{id}/restore', [LocationController::class, 'restore'])
        ->name('locations.restore');
    // Payment Methods
    Route::get('payment-methods/deleted', [PaymentMethodController::class, 'deleted'])
        ->name('payment-methods.deleted');
    Route::resource('payment-methods', PaymentMethodController::class);

    Route::resource('order-types', OrderTypeController::class)->except(['show']);
    Route::get('order-types/deleted', [OrderTypeController::class, 'deleted'])
        ->name('order-types.deleted');
    Route::post('order-types/{id}/restore', [OrderTypeController::class, 'restore'])
        ->name('order-types.restore');

    // Selling Summary
    Route::get('sellings/summary', [SellingController::class, 'summary'])
        ->name('sellings.summary');

    // Stock Remaining
    Route::get('stock-remaining', [StockRemainingController::class, 'chooseLocation'])
        ->name('stock-remaining.choose-location');

    Route::get('stock-remaining/{location}', [StockRemainingController::class, 'report'])
        ->name('stock-remaining.report');
    Route::get('/stock-remaining/{location}/export', [StockRemainingController::class, 'export'])
        ->name('stock-remaining.export');
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
    Route::resource('customers', CustomerController::class)
        ->except(['show']);

    Route::post('customers/{id}/restore', [CustomerController::class, 'restore'])
        ->name('customers.restore');

    Route::get('customers/deleted', [CustomerController::class, 'deleted'])
        ->name('customers.deleted');

    Route::resource('customer-categories', CustomerCategoryController::class)
        ->except(['show']);

    Route::post('customer-categories/{id}/restore', [CustomerCategoryController::class, 'restore'])
        ->name('customer-categories.restore');

    Route::get('customer-categories/deleted', [CustomerCategoryController::class, 'deleted'])
        ->name('customer-categories.deleted');

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
    |-----------------------------------------------------------------------p---
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


    Route::patch('customers/{id}/activate', [CustomerController::class, 'activate'])
        ->name('customers.activate');

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

    Route::resource('report-by-products', SalesReportByProductController::class)->only(['index']);
    Route::resource('report-sales', SalesReportController::class)->only(['index']);
    Route::resource('report-by-locations', SalesReportByLocationController::class)->only(['index']);
    Route::resource('report-stock-movement', ReportStockMovementController::class)->only(['index']);
    Route::resource('report-stock-card', ReportStockCardController::class)->only(['index']);
    Route::resource('report-employee-summary', EmployeeReportSummaryController::class)->only(['index']);
    Route::resource('report-employee-detail', EmployeeReportDetailController::class)->only(['index']);
});

require __DIR__ . '/settings.php';