<?php

use App\Http\Controllers\api\Inventory\InvController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Middleware\DynamicDatabase;
use App\Http\Controllers\api\DBConsManager;
use App\Http\Controllers\api\OtpController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\Orders\POController;

use App\Http\Controllers\Helpers\DynamicSQLHelper;
use App\Http\Controllers\api\PDFUploaderController;
use App\Http\Controllers\api\Product\ProdController;
use App\Http\Controllers\api\Customer\CustController;
use App\Http\Controllers\api\ProductPricesController;
use App\Http\Controllers\api\Salesman\SManController;

// use App\Http\Controllers\api\Orders\InvoiceController;
// use App\Http\Controllers\api\Orders\InvoiceItemsController;

// use App\Http\Controllers\api\Orders\RRController;
// use App\Http\Controllers\api\Orders\RRItemsController;

use App\Http\Controllers\api\Orders\POItemsController;
use App\Http\Controllers\api\SupplierShipToController;



use App\Http\Controllers\api\Inventory\CountController;
use App\Http\Controllers\api\MasterData\ProductController;
use App\Http\Controllers\api\MasterData\CustomerController;



use App\Http\Controllers\api\MasterData\PATargetController;
use App\Http\Controllers\api\MasterData\PickListController;
use App\Http\Controllers\api\MasterData\SalesmanController;
use App\Http\Controllers\api\MasterData\SupplierController;
use App\Http\Controllers\api\ReceivingReports\RRController;
use App\Http\Controllers\api\SalesOrder\SODetailController;
use App\Http\Controllers\api\SalesOrder\SOMasterController;
use App\Http\Controllers\api\MasterData\InventoryController;
use App\Http\Controllers\api\Salesman\SalespersonController;
use App\Http\Controllers\api\MasterData\PAMasterListController;
use App\Http\Controllers\api\Warehouse\WHTaggingController;
use App\Http\Controllers\api\ActivityLog\ActivityLogController;
use App\Http\Controllers\api\ThemeController;
use App\Http\Controllers\api\BankController;
use App\Http\Controllers\api\GcashController;
use App\Http\Controllers\api\SupplierCreditController;


Route::middleware(['auth:sanctum', 'check.user.status', DynamicDatabase::class])->group(function () {

    Route::post('/upload-po-pdf', [PDFUploaderController::class, 'store']);



    Route::apiResource('/salesman', SalesmanController::class);
    Route::post('/salesman/bulk', [SalesmanController::class, 'storebulk']);



    Route::get('/customerGetNames', [CustomerController::class, 'getCustomersNames']);

    Route::apiResource('/inventory', InventoryController::class);
    Route::apiResource('/picklist', PickListController::class);
    Route::apiResource('/pamasterlist', PAMasterListController::class);
    Route::apiResource('/patarget', PATargetController::class);




    // Route::prefix('orders')->group(function () {

    //     // Invoices
    //     Route::apiResource('/invoices', InvoiceController::class);
    //     Route::apiResource('/invoices-items', InvoiceItemsController::class);
    //     Route::get('/invoices-items/search-invoice/{invoice}', [InvoiceItemsController::class, 'searchByInvoiceNumber']);

    //     // Receiving Reports (RR)
    //     Route::apiResource('/rr', RRController::class);
    //     Route::apiResource('/rr-items', RRItemsController::class);
    //     Route::get('/rr-items/search-rr/{rr}', [RRItemsController::class, 'searchByRRNumber']);

    //     // Purchase Orders (PO)
    //     Route::apiResource('/po-items', POItemsController::class);
    //     Route::apiResource('/po-deliveries', PoDeliveriesController::class);
    //     Route::get('/po-items/search-items/{po}', [POItemsController::class, 'searchByPO']);
    //     Route::get('/po-deliveries/search-deliveries/{po}', [PoDeliveriesController::class, 'getDeliveries']);
    // });
});

Route::post('/redirect', [RRController::class, 'setRRNum']);
Route::post('/redirect-ap', [\App\Http\Controllers\api\AccountsPayable\AccountsPayableController::class, 'setAPNum'])->middleware(['auth:sanctum', 'check.user.status']);
Route::post('/setCNTHeader', [CountController::class, 'setCNTHeader']);
Route::get('/remCNTHeader', [CountController::class, 'remCNTHeader']);

Route::middleware(['auth:sanctum', 'check.user.status'])->group(function () {
    Route::prefix('orders')->group(function () {
        Route::apiResource('/po', POController::class);
        Route::post('/po-filter', [POController::class, 'filterPOByStatus']);
        Route::post('/po-confirm/{poid}', [POController::class, 'POConfirm']);

        Route::apiResource('/po-items', POItemsController::class);
        Route::get('/po-items/search-items/{po}', [POItemsController::class, 'searchByPO']);
        Route::get('/print/po/{poid}', [POController::class, 'generatePDF']);
        // Route::get('/print/po/{po}', [POController::class, 'generatePDF']);

    });

    Route::prefix('prod')->group(function () {
        Route::get('/v2/available/products', [ProdController::class, 'getAllProducts']);
        Route::post('/v2/product/upload', [ProdController::class, 'storeBulk']);
        Route::apiResource('/v2/product', ProdController::class);

    });

    Route::prefix('maintenance')->group(function () {
        Route::apiResource('/v2/customer', CustController::class);
        Route::post('/v2/customer/upload', [CustController::class, 'storeBulk']);
        Route::apiResource('/v2/salesman', SManController::class);
        Route::apiResource('/v2/salesperson', SalespersonController::class);
        Route::get('/v2/activesalesperson', [SalespersonController::class, 'getActiveSalesperson']);
        Route::post('/v2/salesperson/upload', [SalespersonController::class, 'storeBulk']);
    });

    Route::prefix('sales-order')->group(function () {
        // Route::post('/v2/so/upload', [ProdController::class, 'storeBulk']);
        Route::apiResource('/header', SOMasterController::class);
        Route::post('/orderstatus/filtered', [SOMasterController::class, 'Showfiltered']);
        Route::post('/filtered-sales-order', [SOMasterController::class, 'fetch_SalesOrder_Filtered']);
        Route::post('/orderstatus/delete', [SOMasterController::class, 'SOStatus_Delete']);
        Route::post('/orderstatus/open-back-order', [SOMasterController::class, 'SOStatus_NotAvailable']);
        Route::post('/orderstatus/release-back-order', [SOMasterController::class, 'SOStatus_InAvailable']);
        Route::post('/orderstatus/in-warehouse', [SOMasterController::class, 'SOStatus_Available']);
        Route::post('/orderstatus/to-invoice', [SOMasterController::class, 'SOStatus_ToInvoice']);
        Route::post('/orderstatus/in-suspense', [SOMasterController::class, 'SOStatus_InSuspense']);
        Route::post('/orderstatus/complete', [SOMasterController::class, 'SOStatus_Complete']);
        Route::apiResource('/detail', SODetailController::class);
    });
    
    Route::prefix('report')->group(function () {
        Route::apiResource('/v2/rr', RRController::class);
        Route::post('/v2/confirm-rr', [RRController::class, 'approveRR']);
        Route::post('/v2/create-missing-ap', [RRController::class, 'createMissingAccountsPayable']);

        Route::apiResource('/v2/countsheet', CountController::class);
        Route::post('/v2/countsheet/confirm/{headerID}', [CountController::class, 'confirm']);
    });

    Route::prefix('inv')->group(function () {
        // Route::apiResource('/', InvController::class);
        Route::get('/latest-movement', [InvController::class, 'latestInvMovement']);
        Route::get('/', [InvController::class, 'getInventory']);
        Route::get('/product-movement/{stockCode}/{warehouse}', [InvController::class, 'getSKUMovement']);
        Route::get('/product-movement-chart/{stockCode}/{warehouse}', [InvController::class, 'StockInOut']); 
        Route::get('/available/products', [InvController::class, 'getProducts']);
        Route::get('/available/product-warehouse/{StockCode}', [InvController::class, 'getProdWarehouse']);
        Route::get('/warehouse-movement/{warehouse}/{startdate}/{enddate}', [InvController::class, 'getAllSKUMovementPerWarehouse']);
        Route::get('/warehouse-movement-chart/{warehouse}/{startdate}/{enddate}', [InvController::class, 'WarehouseStockInOut']);
        Route::get('/warehouse-top-products/{warehouse}/{startdate}/{enddate}', [InvController::class, 'getTopMovingProducts']);
        Route::get('/available/warehouse', [InvController::class, 'getAllWarehouse']);
        Route::get('/transfer/stocks', [InvController::class, 'getAllTransfer']);
        Route::get('/transfer/warehouse/inventory/{warehouse}', [InvController::class, 'getWarehouseInv']);
        Route::post('/warehouse-movement-transfer', [InvController::class, 'InvWarehouseStockTransfer']);
        Route::get('/adjust/stocks', [InvController::class, 'getAllAdjustments']);
        Route::post('/warehouse-movement-adjust', [InvController::class, 'InvWarehouseStockAdjustment']);
        
    });

    Route::prefix('supp')->group(function(){
        Route::apiResource('/vendors', SupplierController::class);
    });

    Route::prefix('wh')->group(function(){
        Route::apiResource('/warehouse', WHTaggingController::class);
        Route::get('/all-warehouse', [WHTaggingController::class, 'getAllWarehouse']);

    });

    Route::apiResource('/product', ProductController::class);
    Route::post('/product/bulk', [ProductController::class, 'storebulk']);
    Route::get('/productGetItems', [ProductController::class, 'getProductList']);

    Route::get('/getProductPrice', [ProductPricesController::class, 'getProductPricev2']);
    Route::get('/getProductPriceCodes', [ProductPricesController::class, 'getProductPriceCodes']);

    Route::apiResource('/customer', CustomerController::class);
    Route::apiResource('/vendors', SupplierController::class);
    Route::apiResource('/supplier-shipped-to', SupplierShipToController::class);
    Route::apiResource('/bank', BankController::class);
    Route::apiResource('/gcash', GcashController::class);

    // Activity Log routes
    Route::prefix('activity-log')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index']);
        Route::get('/statistics', [ActivityLogController::class, 'statistics']);
        Route::get('/export', [ActivityLogController::class, 'export']);
        Route::get('/{id}', [ActivityLogController::class, 'show']);
        Route::delete('/cleanup', [ActivityLogController::class, 'cleanup']);
        Route::get('/test-ip', [ActivityLogController::class, 'testIpCapture']); // Test route
    });



    Route::post('/testcon', [DynamicSQLHelper::class, 'testConnection']);
    Route::post('/registerConn', [DBConsManager::class, 'saveDbconPassword']);

    // Theme Management routes - Restricted to developer, super_admin, and admin only
    Route::prefix('themes')->middleware('role:developer,super_admin,admin')->group(function () {
        Route::get('/', [ThemeController::class, 'index']);
        Route::post('/', [ThemeController::class, 'store']);
        Route::get('/{id}', [ThemeController::class, 'show']);
        Route::put('/{id}', [ThemeController::class, 'update']);
        Route::delete('/{id}', [ThemeController::class, 'destroy']);
        Route::post('/{id}/activate', [ThemeController::class, 'activate']);
        Route::get('/active', [ThemeController::class, 'getActive']);
        Route::get('/{id}/css', [ThemeController::class, 'getCss']);
    });

    // User Management routes - Restricted to developer, super_admin, and admin only
    Route::prefix('users')->middleware('role:developer,super_admin,admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\api\UserController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\api\UserController::class, 'store']);
        Route::get('/statistics', [\App\Http\Controllers\api\UserController::class, 'statistics']);
        Route::get('/{id}', [\App\Http\Controllers\api\UserController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\api\UserController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\api\UserController::class, 'destroy']);
        Route::post('/{id}/activate', [\App\Http\Controllers\api\UserController::class, 'activate']);
        Route::post('/{id}/deactivate', [\App\Http\Controllers\api\UserController::class, 'deactivate']);
    });

});

// Accounts Payable API Routes
Route::get('accounts-payable/summary', [\App\Http\Controllers\api\AccountsPayable\AccountsPayableController::class, 'summary'])->middleware(['auth:sanctum', 'check.user.status']);
Route::get('accounts-payable/suppliers', [\App\Http\Controllers\api\AccountsPayable\AccountsPayableController::class, 'getSuppliers'])->middleware(['auth:sanctum', 'check.user.status']);
Route::get('accounts-payable/banks', [\App\Http\Controllers\api\AccountsPayable\AccountsPayableController::class, 'getBanks'])->middleware(['auth:sanctum', 'check.user.status']);
Route::post('accounts-payable/{id}/payment', [\App\Http\Controllers\api\AccountsPayable\AccountsPayableController::class, 'processPayment'])->middleware(['auth:sanctum', 'check.user.status']);
Route::get('accounts-payable/{id}/payments', [\App\Http\Controllers\api\AccountsPayable\AccountsPayableController::class, 'getPaymentHistory'])->middleware(['auth:sanctum', 'check.user.status']);
Route::post('accounts-payable/apply-auto-credit-memos', [\App\Http\Controllers\api\AccountsPayable\AccountsPayableController::class, 'applyAutoCreditMemos'])->middleware(['auth:sanctum', 'check.user.status']);
Route::apiResource('accounts-payable', \App\Http\Controllers\api\AccountsPayable\AccountsPayableController::class)->middleware(['auth:sanctum', 'check.user.status']);

// Payment History API Routes
Route::get('payment-history', [\App\Http\Controllers\api\PaymentHistory\PaymentHistoryController::class, 'index'])->middleware(['auth:sanctum', 'check.user.status']);
Route::get('payment-history/suppliers', [\App\Http\Controllers\api\PaymentHistory\PaymentHistoryController::class, 'getSuppliers'])->middleware(['auth:sanctum', 'check.user.status']);
Route::get('payment-history/statistics', [\App\Http\Controllers\api\PaymentHistory\PaymentHistoryController::class, 'getStatistics'])->middleware(['auth:sanctum', 'check.user.status']);
Route::get('payment-history/{id}', [\App\Http\Controllers\api\PaymentHistory\PaymentHistoryController::class, 'show'])->middleware(['auth:sanctum', 'check.user.status']);

// Supplier Credit API Routes
Route::get('supplier-credit', [\App\Http\Controllers\api\SupplierCreditController::class, 'getSuppliersWithCredits'])->middleware(['auth:sanctum', 'check.user.status']);
Route::get('supplier-credit/{supplierCode}/transactions', [\App\Http\Controllers\api\SupplierCreditController::class, 'getSupplierTransactions'])->middleware(['auth:sanctum', 'check.user.status']);
Route::get('supplier-credit/{supplierCode}/print-statement', [\App\Http\Controllers\api\SupplierCreditController::class, 'printStatement'])->middleware(['auth:sanctum', 'check.user.status']);
Route::get('supplier-credit/{supplierCode}/print-counter-receipt', [\App\Http\Controllers\api\SupplierCreditController::class, 'printCounterReceipt'])->middleware(['auth:sanctum', 'check.user.status']);

// Route::post('/auth/register', [AuthController::class, 'register']); // Disabled - Registration handled by admin through user management
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum', 'check.user.status']);
Route::post('/auth/force-logout', [AuthController::class, 'forceLogout']); // No auth required
Route::post('/sendOTP', [OtpController::class, 'generateAndSend']);
Route::post('/verifyOTP', [OtpController::class, 'verifyOtp']);
Route::get('transactions/api/print/po/{poid}', [POController::class, 'generatePDF'])->middleware(['auth:sanctum', 'check.user.status']);
