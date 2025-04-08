<?php

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

Route::middleware(['auth:sanctum', DynamicDatabase::class])->group(function () {

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
Route::post('/setCNTHeader', [CountController::class, 'setCNTHeader']);
Route::get('/remCNTHeader', [CountController::class, 'remCNTHeader']);

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('orders')->group(function () {
        Route::apiResource('/po', POController::class);
        Route::post('/po-confirm/{poid}', [POController::class, 'POConfirm']);

        Route::apiResource('/po-items', POItemsController::class);
        Route::get('/po-items/search-items/{po}', [POItemsController::class, 'searchByPO']);
    });

    Route::prefix('prod')->group(function () {
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

        Route::apiResource('/v2/countsheet', CountController::class);
    });

    Route::apiResource('/product', ProductController::class);
    Route::post('/product/bulk', [ProductController::class, 'storebulk']);
    Route::get('/productGetItems', [ProductController::class, 'getProductList']);

    Route::get('/getProductPrice', [ProductPricesController::class, 'getProductPricev2']);
    Route::get('/getProductPriceCodes', [ProductPricesController::class, 'getProductPriceCodes']);

    Route::apiResource('/customer', CustomerController::class);
    Route::apiResource('/vendors', SupplierController::class);
    Route::apiResource('/supplier-shipped-to', SupplierShipToController::class);

    Route::post('/testcon', [DynamicSQLHelper::class, 'testConnection']);
    Route::post('/registerConn', [DBConsManager::class, 'saveDbconPassword']);


});

Route::get('/print/po/{po}', [POController::class, 'generatePDF']);



Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/sendOTP', [OtpController::class, 'generateAndSend']);
Route::post('/verifyOTP', [OtpController::class, 'verifyOtp']);
