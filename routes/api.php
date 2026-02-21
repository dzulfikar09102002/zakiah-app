<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CustomerCategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPointMovementController;
use App\Http\Controllers\DailySaleController;
use App\Http\Controllers\Dashboard\PotensiLabaController;
use App\Http\Controllers\Dashboard\SalesAnnualSummaryController;
use App\Http\Controllers\Dashboard\SalesByDateController;
use App\Http\Controllers\Dashboard\SalesRefundSummaryController;
use App\Http\Controllers\Dashboard\SalesSummaryController;
use App\Http\Controllers\Dashboard\Top5LocationController;
use App\Http\Controllers\Dashboard\Top5ProductCategoryController;
use App\Http\Controllers\Dashboard\Top5ProductController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\Kasir\KasirAuthController;
use App\Http\Controllers\Kasir\KasirCatalogueController;
use App\Http\Controllers\Kasir\KasirCustomerController;
use App\Http\Controllers\Kasir\KasirCustomerOrderController;
use App\Http\Controllers\Kasir\KasirEmployeeController;
use App\Http\Controllers\Kasir\KasirEmployeeLocationController;
use App\Http\Controllers\Kasir\KasirPaymentMethodController;
use App\Http\Controllers\Kasir\KasirProductLocationStockController;
use App\Http\Controllers\Kasir\KasirSaleTransactionController;
use App\Http\Controllers\Kasir\KasirTakingController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\LoyaltyController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\OrderTypeController;
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
use App\Http\Middleware\DeviceCheckingMiddleware;
use App\Http\Middleware\EntityCheckingMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health-check', function () {
    return 'Welcome';
});

Route::post('/login', [AuthController::class, 'login']);
Route::get('/sale_transactions/{sale_transaction}/pdf', [SaleTransactionController::class, 'showPdf']);
Route::get('/daily_sales/{id}/pdf', [DailySaleController::class, 'showPdf']);
Route::get('/product_transfer_services/{id}/pdf', [ProductTransferServiceController::class, 'showPdf']);
// Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum', EntityCheckingMiddleware::class);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/info', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::get('/profile', function (Request $request) {
    return [
        "user" => $request->user(),
    ];
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', EntityCheckingMiddleware::class])->group(function() {
    Route::prefix("backoffice")->group(function() {
        Route::get('/profile', function (Request $request) {
            return [
                "user" => $request->user(),
            ];
        });

        Route::apiResource('/entities', EntityController::class)->only(['show', 'update']);
        Route::apiResource('/locations', LocationController::class)->except(['destroy']);
        Route::get('/locations-dropdown', [LocationController::class, 'dropdown']);
        Route::apiResource('/brands', BrandController::class)->except(['destroy']);
        Route::apiResource('/payment_methods', PaymentMethodController::class)->except(['destroy']);
        Route::apiResource('/taxes', TaxController::class)->except(['destroy']);
        Route::apiResource('/order_types', OrderTypeController::class)->except(['destroy']);
        Route::apiResource('/product_units', ProductUnitController::class)->except(['destroy']);
        Route::apiResource('/product_categories', ProductCategoryController::class)->except(['destroy']);
        Route::get('/product_categories-dropdown', [ProductCategoryController::class, 'dropdown']);
        Route::apiResource('/customer_categories', CustomerCategoryController::class)->except(['destroy']);
        Route::patch('/customer_categories/{id}/activate', [CustomerCategoryController::class, 'activate']);
        Route::patch('/customer_categories/{id}/archive', [CustomerCategoryController::class, 'archive']);
        Route::get('/customer_categories-dropdown', [CustomerCategoryController::class, 'dropdown']);
        Route::apiResource('/products', ProductController::class)->except(['destroy']);
        Route::get('/products-dropdown', [ProductController::class, 'dropdown']);
        Route::get('/product-stocks', [ProductController::class, 'stock']);
        Route::get('/product-export', [ProductController::class, 'export']);
        
        Route::apiResource('/product_import_services', ProductImportServiceController::class)->except(['destroy']);
        Route::post('/product_import_services/upload', [ProductImportServiceController::class, 'upload']);

        Route::apiResource('/product_transfer_services', ProductTransferServiceController::class)->except(['destroy']);
        Route::post('/product_transfer_services/{product_transfer_service}/approve', [ProductTransferServiceController::class, 'approve']);
        Route::post('/product_transfer_services/{product_transfer_service}/reject', [ProductTransferServiceController::class, 'reject']);
        Route::post('/product_transfer_services/{product_transfer_service}/cancel', [ProductTransferServiceController::class, 'cancel']);
        Route::apiResource('/promos', PromoController::class)->except(['destroy']);
        Route::apiResource('/roles', RoleController::class);
        Route::get('/parent_roles', [RoleController::class, 'parent']);
        Route::apiResource('/employees', EmployeeController::class);
        Route::get('/employees-dropdown', [EmployeeController::class, 'dropdown']);
        Route::apiResource('/daily_sales', DailySaleController::class)->only(['index', 'show']);
        Route::apiResource('/product_opname_services', ProductOpnameServiceController::class);
        Route::get('/product_opname_services/{id}/preview', [ProductOpnameServiceController::class, 'preview']);
        // Route::post('/product_opname_services/{id}/approve', [ProductOpnameServiceController::class, 'approve']);
        // Route::post('/product_opname_services/{id}/reject', [ProductOpnameServiceController::class, 'reject']);
        Route::apiResource('/product_adjustment_stocks', ProductAdjustmentStockController::class);
        Route::post('/product_adjustment_stocks/{id}/approve', [ProductAdjustmentStockController::class, 'approve']);
        Route::post('/product_adjustment_stocks/{id}/reject', [ProductAdjustmentStockController::class, 'reject']);
        Route::apiResource('/loyalties', LoyaltyController::class)->except(['destroy']);
        Route::patch('/loyalties/{id}/activate', [LoyaltyController::class, 'activate']);
        Route::patch('/loyalties/{id}/deactivate', [LoyaltyController::class, 'deactivate']);
        Route::patch('/loyalties/{id}/archive', [LoyaltyController::class, 'archive']);
        Route::apiResource('/sale_transactions', SaleTransactionController::class)->only(['index', 'show']);
        Route::patch('/sale_transactions/{id}/void', [SaleTransactionController::class, 'void']);
        Route::apiResource('/customers', CustomerController::class)->only(['index', 'show']);
        Route::patch('/customers/{id}/activate', [CustomerController::class, 'activate']);
        Route::patch('/customers/{id}/archive', [CustomerController::class, 'archive']);
        Route::apiResource('/customer_point_movements', CustomerPointMovementController::class)->only(['index']);

        Route::prefix("report")->group(function() {
            Route::apiResource('/report_by_products', ReportByProductController::class)->only(['index']);
            Route::apiResource('/report_sales', ReportSalesController::class)->only(['index']);
            Route::apiResource('/report_sales_by_location', ReportSalesByLocationController::class)->only(['index']);
            Route::apiResource('/report_stock_movement', ReportStockMovementController::class)->only(['index']);
            Route::apiResource('/report_stock_card', ReportStockCardController::class)->only(['index']);
            Route::apiResource('/report_employee_summary', ReportEmployeeSummaryController::class)->only(['index']);
            Route::apiResource('/report_employee_detail', ReportEmployeeDetailController::class)->only(['index']);
        });

        Route::prefix("dashboard")->group(function() {
            Route::apiResource('/top_5_products', Top5ProductController::class)->only(['index']);
            Route::apiResource('/top_5_product_categories', Top5ProductCategoryController::class)->only(['index']);
            Route::apiResource('/potensi_labas', PotensiLabaController::class)->only(['index']);
            Route::apiResource('/annual_sales', SalesAnnualSummaryController::class)->only(['index']);
            Route::apiResource('/top_5_locations', Top5LocationController::class)->only(['index']);
            Route::apiResource('/sales_by_dates', SalesByDateController::class)->only(['index']);
            Route::apiResource('/sales_summaries', SalesSummaryController::class)->only(['index']);
            Route::apiResource('/sales_refund_summaries', SalesRefundSummaryController::class)->only(['index']);
        });
    });
});

Route::get('/kasir/employee_locations', [KasirEmployeeLocationController::class, 'index'])->middleware('auth:sanctum')->middleware(EntityCheckingMiddleware::class);
Route::post('/kasir/auth', [KasirAuthController::class, 'store'])->middleware('auth:sanctum')->middleware(EntityCheckingMiddleware::class);

Route::middleware(['auth:sanctum', EntityCheckingMiddleware::class, DeviceCheckingMiddleware::class])->group(function() {
    Route::prefix("kasir")->group(function() {
        Route::apiResource('/customer_orders', KasirCustomerOrderController::class)->except(['destroy']);
        Route::post('/customer_orders/{customer_order}/pay', [KasirCustomerOrderController::class, 'pay']);
        Route::post('/customer_orders/calculate_promo', [KasirCustomerOrderController::class, 'calculatePromo']);
        Route::apiResource('/sale_transactions', KasirSaleTransactionController::class)->only(['index', 'show', 'store']);
        Route::post('/sale_transactions/{sale_transaction}/refund', [KasirSaleTransactionController::class, 'refund']);
        Route::get('/sale_transactions/{sale_transaction}/pdf', [KasirSaleTransactionController::class, 'showPdf']);
        Route::apiResource('/product_location_stocks', KasirProductLocationStockController::class)->only(['index', 'show']);
        Route::apiResource('/catalogues', KasirCatalogueController::class)->only(['index']);
        Route::get('/catalogues/product_search', [KasirCatalogueController::class, 'productSearch']);
        Route::apiResource('/payment_methods', KasirPaymentMethodController::class)->only(['index']);
        Route::apiResource('/takings', KasirTakingController::class)->only(['index', 'store']);
        Route::apiResource('/customers', KasirCustomerController::class)->only(['index', 'store']);
        Route::apiResource('/employees', KasirEmployeeController::class)->only(['index']);
    });
});