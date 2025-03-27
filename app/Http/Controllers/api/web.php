<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScanAndMoveFilesController;
use Laravel\Sanctum\Http\Middleware\CheckForAnyToken;

use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Middleware\AuthenticateWithCookie;

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
})->name('pamasterlist');

Route::get('/product', function () {
    return page_view('product_page');
})->name('product');

Route::get('/inventory', function () {
    return page_view('inventory_page');
})->name('inventory');

Route::get('/salesman', function () {
    return page_view('salesman_page');
})->name('salesman');

Route::get('/customer', function () {
    return page_view('customer_page');
})->name('customer');

Route::get('/dbconfig', function () {
    return page_view('dbconfig_page');
})->name('dbconfig');

Route::get('/picklist', function () {
    return page_view('picklist_page');
})->name('picklist');

Route::get('/testpage', function () {
    return page_view('inventory_test');
})->name('dbconfig');


Route::get('/patarget', function () {
    return page_view('patarget_page');
})->name('patarget');



Route::get('/invoices', function () {
    return page_view('invoices_page');
})->name('invoices');

Route::get('/receiving-report', function () {
    return page_view('receiving_report_page');
})->name('receiving-report');

Route::get('/purchase-order', function () {
    return page_view('purchase_order_page');
})->name('purchase-order');

Route::get('/register', function () {
    return page_view('register');
})->name('register');

Route::get('/uploader', function () {
    return page_view('uploader_page');
})->name('uploader');

Route::get('/login', function () {
    return page_view('login');
})->name('login');

Route::get('/vendors', function () {
    return page_view('vendors');
})->name('vendors');


Route::get('/print', function () {
    return page_view('PurchaseOrder-PDF');
})->name('print');

Route::get('/layout', function () {
    return page_view('layout');
})->name('layout');

// Route::get('/job/start', [ScanAndMoveFilesController::class, 'startJob'])->name('job.start');
// Route::get('/job/stop', [ScanAndMoveFilesController::class, 'stopJob'])->name('job.stop');
// Route::get('/job/status', [ScanAndMoveFilesController::class, 'getJobStatus'])->name('job.status');
