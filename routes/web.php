<?php

use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AuthenticateWithCookie;

use App\Http\Controllers\api\Inventory\CountController;
use App\Http\Controllers\ScanAndMoveFilesController;
use Laravel\Sanctum\Http\Middleware\CheckForAnyToken;
use App\Http\Controllers\api\ReceivingReports\RRController;

//helper xD
function page_view($view)
{
    return view('Pages.' . $view);
}


// Route::get('/', function () {
//     return view('welcome');
// });


//Route::middleware(['auth:sanctum', 'redirectTo:/login'])->group(function () {});

Route::get('/pamasterlist', function () {
    return page_view('pamasterlist_page');
})->name('pamasterlist')->middleware('check.user.status');







Route::get('/', function () {
    // return page_view('login');
    return redirect()->intended('/login');
})->name('index');

Route::get('/settings/dbconfig', function () {
    return page_view('dbconfig_page');
})->name('dbconfig')->middleware('check.user.status');

Route::get('/picklist', function () {
    return page_view('picklist_page');
})->name('picklist')->middleware('check.user.status');

Route::get('/testpage', function () {
    return page_view('inventory_test');
})->name('dbconfigtest')->middleware('check.user.status');


Route::get('/patarget', function () {
    return page_view('patarget_page');
})->name('patarget')->middleware('check.user.status');



Route::get('/invoices', function () {
    return page_view('invoices_page');
})->name('invoices')->middleware('check.user.status');






// LAYOUT
Route::get('/layout', function () {
    return page_view('layout');
})->name('layout')->middleware('check.user.status');


// CUSTOMER MAINTENANCE MODULE
Route::get('/master-data/customer', function () {
    // return page_view('customer_page');
    return page_view('customer-maintenance/cust_page');
})->name('customer')->middleware('check.user.status');


// INVENTORY MAINTENANCE MODULE
Route::get('/inventory/stock-count', function () {
    return page_view('inventory-maintenance/invCount_page');
})->name('stockcount')->middleware('check.user.status');

Route::get('/countsheet', function () {
    return page_view('invCount_page');
})->name('countsheet')->middleware('check.user.status');

Route::get('/print/countsheet/manual', [CountController::class, 'printManualPage'])->name('printManualPage')->middleware('check.user.status');

Route::get('/print/countsheet/testing', function () {
    $data = [
        'distributor' => 'Fast Distribution Corporation',
        'branch' => 'CEBU Branch',
        'warehouse'=> 'M1',
        'date'=> now()->format('Y-m-d'),
        'counted'=> 'Jhunrey Lucero', 
        'confirmed'=> 'Jhun Woogie Arrabis', 
        'items' => []
    ];

    // Generate 40 items dynamically
    for ($i = 1; $i <=50; $i++) {
        $data['items'][] = [
            'stockCode' => rand(10000000, 99999999),
            'itemDesc' => 'Sample Item Description' . $i,
            'cases' => rand(10, 500),
            'ib' => rand(10, 999),
            'piece'=> rand(1, 999),
        ];
    }

     // Convert array to object
     $report = json_decode(json_encode($data));

    return view('Pages.Printing.CountSheet_printing', compact('report'));
})->name('printcountSheet');

// INVENTORY WAREHOUSE MODULE
Route::get('/inventory/warehouse', function () {
    return page_view('inventory/invWarehouse_page');
})->name('invWarehouse')->middleware('check.user.status');
Route::get('/inventory/movement/product', function () {
    return page_view('inventory/invMovements_page');
})->name('invMovements')->middleware('check.user.status');
Route::get('/inventory/movement/warehouse', function () {
    return page_view('inventory/invWarehouseMovements_page');
})->name('invWarehouseMovements')->middleware('check.user.status');
Route::get('/inventory/stock-transfer', function () {
    return page_view('inventory/invTransfer_page');
})->name('invStockTransfer')->middleware('check.user.status');
Route::get('/inventory/stock-adjustment', function () {
    return page_view('inventory/invAdj_page');
})->name('stockadjustment')->middleware('check.user.status');




// PRODUCT MAINTENANCE MODULE
Route::get('/master-data/product', function () {
    return page_view('product-maintenance/product_page');
})->name('product')->middleware('check.user.status');


// PURCHASE ORDER
Route::get('/transactions/purchase-order', function () {
    return page_view('purchase-order/purchase_order_page');
})->name('purchase-order')->middleware('check.user.status');


// RECEIVING REPORT MODULE
Route::get('/transactions/receiving-report', function () {
    return page_view('receiving-report/receiving_report_page');
})->name('receiving-report')->middleware('check.user.status');

Route::get('/print/rr/', [RRController::class, 'printPage'])->name('web.print')->middleware('check.user.status');
Route::get('/print/ap/', [\App\Http\Controllers\api\AccountsPayable\AccountsPayableController::class, 'printPage'])->name('web.print.ap')->middleware('check.user.status');
Route::get('/print/ar/', [\App\Http\Controllers\api\AccountsReceivable\AccountsReceivableController::class, 'printPage'])->name('web.print.ar')->middleware('check.user.status');

Route::get('/print/rr/testing', function () {
    $data = [
        'title' => 'RR Printing',
        'date' => now()->format('Y-m-d'),
        'distName'=> 'FUI Shell',
        'supCode'=> 'VE-P0002',
        'supName'=> 'Shell Pilipinas Corporation',
        'supAdd'=> 'Fort Bonifacio 1635 Taguig City NCR, Fourth District Philippines Fort Bonifacio 1635 Taguig City NCR, Fourth District Philippines',
        'supTIN'=> '000-164-757-00000',
        'rrNo'=> '1600000711',
        'rrDate'=> 'Nov. 18, 2024',
        'rrRef'=> 'DN-512545212',
        'rrStat1'=> 'Closed',
        'rrStat2'=> 'Original', 
        'prepared'=> 'Marvin Navarro', 
        'checked'=> 'Jhunrey Lucero', 
        'approved'=> 'Jhun Woogie Arrabis', 
        'items' => []
    ];

    // Generate 40 items dynamically
    for ($i = 1; $i <=19; $i++) {
        $data['items'][] = [
            'itemCode' => rand(100000000, 999999999),
            'itemDesc' => 'Sample Item Description' . $i,
            'itemQty' => rand(10, 500),
            'itemOum' => ['CS', 'PC', 'IB'][array_rand(['CS', 'PC', 'IB'])],
            'itemWhsCode'=> 'V' . rand(100, 999) . 'M' . rand(0, 9),
            'itemUnitPrice' => round(rand(1000, 5000) + (rand(0, 99) / 100), 2),
            'netVat' => round(rand(5000, 500000) + (rand(0, 99) / 100), 2),
            'vat' => round(rand(500, 50000) + (rand(0, 99) / 100), 2),
            'gross' => round(rand(10000, 600000) + (rand(0, 99) / 100), 2)
        ];
    }

     // Convert array to object
     $report = json_decode(json_encode($data));

    return view('Pages.Printing.RR_printing_test', compact('report'));
})->name('printrrtest');


// SALESMAN MAINTENANCE MODULE
Route::get('/master-data/salesman', function () {
    // return page_view('salesman_page');
    return page_view('salesman/salesperson_page');
})->name('salesman')->middleware('check.user.status');


// SALES ORDER MODULE
Route::get('/transactions/sales-order', function () {
    return page_view('sales-order/so_page');
})->name('sales-order')->middleware('check.user.status');

// ACCOUNTS PAYABLE MODULE
Route::get('/transactions/accounts-payable', function () {
    return page_view('accounts-payable/accounts_payable_page');
})->name('accounts-payable')->middleware('check.user.status');

// ACCOUNTS RECEIVABLE MODULE
Route::get('/transactions/accounts-receivable', function () {
    return page_view('accounts-receivable/accounts_receivable_page');
})->name('accounts-receivable')->middleware('check.user.status');

// PAYMENT HISTORY MODULE
Route::get('/transactions/payment-history', function () {
    return page_view('payment-history/payment_history_page');
})->name('payment-history')->middleware('check.user.status');


// SUPPLIER MAINTENANCE MODULE
Route::get('/master-data/supplier', function () {
    return page_view('supplier-maintenance/supplier_page');
})->name('supplier')->middleware('check.user.status');

// SUPPLIER CREDIT MODULE
Route::get('/transactions/supplier-credit', function () {
    return page_view('supplier-credit/supplier_credit_page');
})->name('supplier-credit')->middleware('check.user.status');

// CUSTOMER CREDIT MODULE
Route::get('/transactions/customer-credit', function () {
    return page_view('customer-credit/customer_credit_page');
})->name('customer-credit')->middleware('check.user.status');


// WAREHOUSE MAINTENANCE MODULE
Route::get('/master-data/warehouse', function () {
    return page_view('warehouse/warehouse_page');
})->name('warehouse')->middleware('check.user.status');

// BANK MAINTENANCE MODULE
Route::get('/master-data/bank', function () {
    return page_view('bank/bank_page');
})->name('bank')->middleware('check.user.status');

// BANK RECONCILIATION MODULE
Route::get('/master-data/bank-reconciliation', function () {
    return page_view('bank_reconciliation/bank_reconciliation_page');
})->name('bank-reconciliation')->middleware('check.user.status');

// GCASH RECONCILIATION MODULE
Route::get('/master-data/gcash-reconciliation', function () {
    return page_view('gcash_reconciliation/gcash_reconciliation_page');
})->name('gcash-reconciliation')->middleware('check.user.status');

// GCASH MAINTENANCE MODULE
Route::get('/master-data/gcash', function () {
    return page_view('gcash/gcash_page');
})->name('gcash')->middleware('check.user.status');

// ACTIVITY LOGS MODULE - Restricted to developers only
Route::get('/reports/activity-logs', function () {
    return view('Pages.activity-log.activityLog_page');
})->name('activity-logs')->middleware(['check.user.status', 'role:developer']);

// THEME MANAGEMENT MODULE - Restricted to developer, super_admin, and admin only
Route::get('/settings/themes', [\App\Http\Controllers\ThemeController::class, 'index'])->name('themes.index')->middleware(['check.user.status', 'role:developer,super_admin,admin']);
Route::get('/settings/themes/create', [\App\Http\Controllers\ThemeController::class, 'create'])->name('themes.create')->middleware(['check.user.status', 'role:developer,super_admin,admin']);
Route::get('/settings/themes/{id}/edit', [\App\Http\Controllers\ThemeController::class, 'edit'])->name('themes.edit')->middleware(['check.user.status', 'role:developer,super_admin,admin']);

// USER MANAGEMENT MODULE - Restricted to developer, super_admin, and admin only
Route::get('/settings/users', function () {
    return page_view('user-management/users_page');
})->name('users')->middleware(['check.user.status', 'role:developer,super_admin,admin']);
// Note: POST, PUT, DELETE operations are now handled by API endpoints in /api/themes

Route::get('/register', function () {
    return redirect()->route('login')->with('message', 'Registration is now handled by administrators. Please contact your admin for account creation.');
})->name('register');

Route::get('/uploader', function () {
    return page_view('uploader_page');
})->name('uploader')->middleware('check.user.status');

Route::get('/login', function () {
    return page_view('login');
})->name('login');

Route::get('/account-deactivated', function () {
    return page_view('account-deactivated');
})->name('account.deactivated');

Route::get('/transactions/print', function () {
    return page_view('PurchaseOrder-PDF');
})->name('print')->middleware('check.user.status');

// PO Print Route - Web based for session authentication
Route::get('/transactions/print/po/{poid}', [\App\Http\Controllers\api\Orders\POController::class, 'generatePDF'])->name('web.print.po')->middleware('check.user.status');





// Route::get('/job/start', [ScanAndMoveFilesController::class, 'startJob'])->name('job.start');
// Route::get('/job/stop', [ScanAndMoveFilesController::class, 'stopJob'])->name('job.stop');
// Route::get('/job/status', [ScanAndMoveFilesController::class, 'getJobStatus'])->name('job.status');
