<?php

namespace App\Http\Controllers\api\SalesOrder;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\Customer\Customer;
use App\Services\InventoryManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\SalesOrder\SODetail;
use App\Models\SalesOrder\SOMaster;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Event;
use App\Events\Inventory\InventoryMovement;
use App\Events\Inventory\InventoryWarehouse;
use App\Models\AccountsReceivable;
use App\Models\Payment;
use App\Models\ARCreditMemoApplication;
use App\Models\CustomerCredit;

class SOMasterController extends Controller
{
    public function index()
    {
        try {
            $today = date('Y-m-d'); // Today's date
            $oneMonthAgo = date('Y-m-d', strtotime('-2 month'));

            $data = SOMaster::select(
                'SalesOrder',
                'NextDetailLine',
                'OrderStatus',
                'DocumentType',
                'Customer',
                'CustomerName',
                'Salesperson',
                'CustomerPoNumber',
                'OrderDate',
                'EntrySystemDate',
                'ReqShipDate',
                'DateLastDocPrt',
                'InvoiceCount',
                'Branch',
                'Warehouse',
                'ShipAddress1',
                'ShipAddress2',
                'ShipAddress3',
                'ShipToGpsLat',
                'ShipToGpsLong',
                'SpecialInstruction',
            )->whereBetween('EntrySystemDate', [$oneMonthAgo, $today])->get();
            
            if (count($data) == 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'No Sales Orders Data found',
                ], 200);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Sales Orders Data retrieved successfully',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $InventoryManager = new InventoryManager();
            $data = $request->data;
            $items = Arr::pull($data, 'Items');
            
            // Validate that we have items
            if (empty($items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create sales order: No items provided.',
                ], 400);
            }
            
            // Validate each item for required fields
            foreach ($items as $index => $item) {
                if (empty($item['MStockCode']) || trim($item['MStockCode']) === '') {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot create sales order: Item #" . ($index + 1) . " has no stock code.",
                    ], 400);
                }
                
                if (!isset($item['QTYinPCS']) || $item['QTYinPCS'] === null || $item['QTYinPCS'] === '' || (float)$item['QTYinPCS'] <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot create sales order: Item #" . ($index + 1) . " (" . $item['MStockCode'] . ") has invalid quantity.",
                    ], 400);
                }
            }
            
            // Validate stock availability before creating the sales order
            $checkProdArr = array_map(function ($item) use ($data) {
                return [
                    'stockCode' => $item['MStockCode'],
                    'qty' => (float)$item['QTYinPCS'],
                    'warehouse'=> $data['Warehouse'],
                ];
            }, $items);
            
            $isEnough = $InventoryManager->isInvEnough($checkProdArr);
            if (!$isEnough) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot create sales order: Insufficient stock for one or more items in the selected warehouse.',
                ], 400);
            }
            
            $so = SOMaster::create([
                'NextDetailLine'=> count($items)+1,
                'Customer'=> $data['CustomerInfo']['Customer'],
                'Salesperson'=> $data['CustomerInfo']['Salesperson'],
                'OrderDate' => $data['OrderDate'],
                'Branch' => $data['Branch'],
                'Warehouse' => $data['Warehouse'],
                'EntrySystemDate'=> date('Y-m-d'),
                'ReqShipDate' => $data['ReqShipDate'],
                'DateLastDocPrt'=> date('Y-m-d'),
                'CustomerName'=> $data['CustomerInfo']['Contact'],
                'ShipAddress1'=> $data['CustomerInfo']['SoldToAddr1'],
                'ShipAddress2'=> $data['CustomerInfo']['SoldToAddr2'],
                'ShipAddress3'=> $data['CustomerInfo']['SoldToAddr3'],
                'ShipToGpsLat'=> $data['CustomerInfo']['SoldToGpsLat'],
                'ShipToGpsLong'=> $data['CustomerInfo']['SoldToGpsLong'],
                'LastOperator' => $data['LastOperator'],
                'SpecialInstruction' => $data['SpecialInstruction'] ?? null,
            ]);            
            // Set MWarehouse for each item from the parent SOMaster warehouse
            foreach ($items as &$item) {
                $item['MWarehouse'] = $data['Warehouse'];
            }
            $so->sodetails()->createMany($items);

            // Log the activity
            activity('sales_order')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'sales_order_number' => $so->SalesOrder,
                    'customer_name' => $data['CustomerInfo']['Contact'],
                    'total_items' => count($items),
                    'warehouse' => $data['Warehouse'],
                    'branch' => $data['Branch'],
                    'subject_type' => 'App\\Models\\SalesOrder\\SOMaster',
                    'subject_id' => $so->SalesOrder,
                    'event' => 'created'
                ])
                ->log("Created new sales order #{$so->SalesOrder} for customer {$data['CustomerInfo']['Contact']} with " . count($items) . " items");

            // Aggregated Items Added record for Sales Order creation
            try {
                $createdItems = $so->sodetails()->get(['MStockCode','MStockDes','MPrice']);
                $itemsPayload = $createdItems->map(function($it){
                    return [
                        'StockCode' => $it->MStockCode,
                        'Decription' => $it->MStockDes,
                        'TotalPrice' => (float)$it->MPrice,
                    ];
                })->values()->toArray();
                $itemsTotal = array_sum(array_map(function($it){
                    return (float)($it['TotalPrice'] ?? 0);
                }, $itemsPayload));

                activity('sales_order')
                    ->withProperties([
                        'sales_order_number' => $so->SalesOrder,
                        'subject_type' => 'App\\Models\\SalesOrder\\SOMaster',
                        'subject_id' => $so->SalesOrder,
                        'event' => 'items_added',
                        'items' => $itemsPayload,
                        'items_total' => $itemsTotal,
                    ])
                    ->event('items_added')
                    ->log("Added items to Sales Order #{$so->SalesOrder}");
            } catch (\Exception $e) {
                // Silent fail; do not block order creation
            }

            return response()->json([
                'success' => true,
                'message' => 'New Sales Order created successfully',
                'data' => $so
            ], 200);  // HTTP 200 OK
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $salesorderID)
    {
        try {
            $data = SOMaster::select('SalesOrder', 'NextDetailLine', 'OrderStatus', 'DocumentType', 'Customer', 'CustomerName', 'Salesperson', 'CustomerPoNumber', 'OrderDate', 'EntrySystemDate', 'ReqShipDate', 'DateLastDocPrt', 'InvoiceCount', 'Branch', 'Warehouse', 'ShipAddress1', 'ShipAddress2', 'ShipAddress3', 'ShipToGpsLat', 'ShipToGpsLong', 'Branch', 'SpecialInstruction')
                ->where('SalesOrder', $salesorderID)
                ->first();
            $details = SODetail::select('SalesOrder', 'SalesOrderLine', 'MStockCode', 'MStockDes', 'MWarehouse', 'MOrderQty', 'MOrderUom', 'MStockQtyToShp', 'MStockingUom', 'MconvFactOrdUm', 'MPrice', 'MPriceUom', 'MProductClass', 'MStockUnitMass', 'MStockUnitVol', 'MPriceCode', 'MConvFactAlloc', 'MConvFactUnitQ', 'MAltUomUnitQ', 'MUnitCost', 'QTYinPCS')
                ->where('SalesOrder', $salesorderID)
                ->get();
            $data->details = $details;
            $data->grandTotal = $details->sum('MPrice');

            if ($data) {

                return response()->json([
                    'success' => true,
                    'message' => 'Data Retrieved Successfully',
                    'data' => $data,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No Data Found'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function Showfiltered(Request $request)
    {
        try {
            $start = (!$request->data['filteredStartDate'])? date('Y-m-d', strtotime('-1 month')) : $request->data['filteredStartDate'];
            $end = (!$request->data['filteredEndDate'])? date('Y-m-d') : $request->data['filteredEndDate'];

            $data = SOMaster::select(
                'SalesOrder',
                'CustomerName',
            )->whereBetween('EntrySystemDate', [$start, $end])->get();
            
            if (count($data) == 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'No Sales Orders Data found',
                    'data' => [],
                ], 200);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Sales Orders Data retrieved successfully',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetch_SalesOrder_Filtered(Request $request)
    {
        try {
            $salesOrders = $request->salesOrder;

            $data = SOMaster::select(
                'SalesOrder',
                'NextDetailLine',
                'OrderStatus',
                'DocumentType',
                'Customer',
                'CustomerName',
                'Salesperson',
                'CustomerPoNumber',
                'OrderDate',
                'EntrySystemDate',
                'ReqShipDate',
                'DateLastDocPrt',
                'InvoiceCount',
                'Branch',
                'Warehouse',
                'ShipAddress1',
                'ShipAddress2',
                'ShipAddress3',
                'ShipToGpsLat',
                'ShipToGpsLong',
                'EntrySystemDate',
                'SpecialInstruction',
            )->whereIn('SalesOrder', $request->salesOrder)->get();
            
            if (count($data) == 0) {
                return response()->json([
                    'success' => 3,
                    'message' => 'No Sales Orders Data found',
                ], 200);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Sales Orders Data retrieved successfully',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $salesOrderId)
    {
        $data = $request->data['Items'];
        $customerDetails = Customer::select('Customer', 'Name', 'ShortName', 'Salesperson', 'PriceCode', 'CustomerClass', 'Telephone', 'Contact', 'SoldToAddr1', 'SoldToAddr2', 'SoldToAddr3', 'SoldToGpsLat', 'SoldToGpsLong')
                                    ->with('salesman')->where('Customer',  $request->data['shippedToName'])->first();
        SOMaster::where('SalesOrder', $salesOrderId)->update([
            'OrderDate' =>  $request->data['OrderDate'],
            'Branch' =>  $request->data['Branch'],
            'Warehouse' =>  $request->data['Warehouse'],
            'ReqShipDate' =>  $request->data['ReqShipDate'], 
            'Customer'=> $customerDetails->Customer,
            'Salesperson' => $customerDetails->Salesperson,
            'CustomerName' => $customerDetails->Contact, 
            'ShipAddress1' => $customerDetails->SoldToAddr1,
            'ShipAddress2' => $customerDetails->SoldToAddr2,
            'ShipAddress3' => $customerDetails->SoldToAddr3,
            'ShipToGpsLat' => $customerDetails->SoldToGpsLat,
            'ShipToGpsLong' => $customerDetails->SoldToGpsLong,
            'SpecialInstruction' => $request->data['SpecialInstruction'] ?? null,
        ]);
        $SOdata = SOMaster::select('SalesOrder')->with('sodetails')->where('SalesOrder', $salesOrderId)->first();
        $sodetails = $SOdata ? $SOdata->sodetails->toArray() : []; // Convert collection to array

        // Define a comparison function based on MStockCode
        $compareByStockCode = function ($a, $b) {
            return $a['MStockCode'] <=> $b['MStockCode'];
        };

        // Find objects present in both $data and $sodetails (existing items to update)
        $commonItems = array_uintersect($data, $sodetails, $compareByStockCode);

        // Find new items (present in $data but not in $sodetails)
        $newItems = array_udiff($data, $sodetails, $compareByStockCode);

        // Find deleted items (present in $sodetails but not in $data)
        $deletedItems = array_udiff($sodetails, $data, $compareByStockCode);

        // Process existing items (update)
        foreach ($commonItems as $item) {
            SODetail::where('SalesOrder', $salesOrderId)
                ->where('MStockCode', $item['MStockCode'])
                ->update([
                    'MOrderQty' => $item['MOrderQty'],
                    'MPrice' => $item['MPrice'],
                    'QTYinPCS' => $item['QTYinPCS']
                ]);
        }

        // Process new items (insert)
        // Set MWarehouse for each new item from the parent SOMaster warehouse
        $parentWarehouse = $request->data['Warehouse'];
        
        // Validate stock availability for new items before adding them
        if (!empty($newItems)) {
            $InventoryManager = new InventoryManager();
            $checkNewItemsArr = array_map(function ($item) use ($parentWarehouse) {
                return [
                    'stockCode' => $item['MStockCode'],
                    'qty' => (float)$item['QTYinPCS'],
                    'warehouse'=> $parentWarehouse,
                ];
            }, $newItems);
            
            $isEnoughForNewItems = $InventoryManager->isInvEnough($checkNewItemsArr);
            if (!$isEnoughForNewItems) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot add new items: Insufficient stock for one or more new items in the selected warehouse.',
                ], 400);
            }
        }
        
        foreach ($newItems as &$item) {
            $item['MWarehouse'] = $parentWarehouse;
        }
        $SOdata->sodetails()->createMany($newItems);

        // Process deleted items (remove)
        foreach ($deletedItems as $item) {
            SODetail::where('SalesOrder', $salesOrderId)
                ->where('MStockCode', $item['MStockCode'])
                ->delete();
        }

        // Aggregated logs for items added and removed
        if (!empty($newItems)) {
            $addedPayload = array_map(function($it){
                return [
                    'StockCode' => $it['MStockCode'] ?? null,
                    'Decription' => $it['MStockDes'] ?? null,
                    'TotalPrice' => (float)($it['MPrice'] ?? 0),
                ];
            }, $newItems);
            $addedTotal = array_sum(array_map(function($it){
                return (float)($it['TotalPrice'] ?? 0);
            }, $addedPayload));

            activity('sales_order')
                ->withProperties([
                    'sales_order_number' => $salesOrderId,
                    'subject_type' => 'App\\Models\\SalesOrder\\SOMaster',
                    'subject_id' => $salesOrderId,
                    'event' => 'items_added',
                    'items' => array_values($addedPayload),
                    'items_total' => $addedTotal,
                ])
                ->event('items_added')
                ->log("Added items to Sales Order #{$salesOrderId}");
        }

        if (!empty($deletedItems)) {
            $removedPayload = array_map(function($it){
                return [
                    'StockCode' => $it['MStockCode'] ?? null,
                    'Decription' => $it['MStockDes'] ?? null,
                    'TotalPrice' => (float)($it['MPrice'] ?? 0),
                ];
            }, $deletedItems);
            $removedTotal = array_sum(array_map(function($it){
                return (float)($it['TotalPrice'] ?? 0);
            }, $removedPayload));

            activity('sales_order')
                ->withProperties([
                    'sales_order_number' => $salesOrderId,
                    'subject_type' => 'App\\Models\\SalesOrder\\SOMaster',
                    'subject_id' => $salesOrderId,
                    'event' => 'items_removed',
                    'items' => array_values($removedPayload),
                    'items_total' => $removedTotal,
                ])
                ->event('items_removed')
                ->log("Removed items from Sales Order #{$salesOrderId}");
        }

        if (empty($newItems) && empty($deletedItems)) {
            activity('sales_order')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'sales_order_number' => $salesOrderId,
                    'customer_name' => $customerDetails->Contact,
                    'updated_items' => count($commonItems),
                    'new_items' => count($newItems),
                    'deleted_items' => count($deletedItems),
                    'warehouse' => $request->data['Warehouse'],
                    'branch' => $request->data['Branch'],
                    'subject_type' => 'App\\Models\\SalesOrder\\SOMaster',
                    'subject_id' => $salesOrderId,
                    'event' => 'updated'
                ])
                ->log("Updated sales order #{$salesOrderId} - modified " . count($commonItems) . " items, added " . count($newItems) . " items, removed " . count($deletedItems) . " items");
        }

        return response()->json([
            'success' => true,
            'message' => 'Sales Order updated successfully',
            'commonItems' => $commonItems,
            'newItems' => $newItems,
            'deletedItems' => $deletedItems,
        ]);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function SOStatus_Available(Request $request)
    {   
        try{
            $data = SOMaster::where('SalesOrder', $request->salesOrder)->where('OrderStatus',1)->first();
            if(!$data){
                return response()->json([
                    'success' => false,
                    'message' => 'The sales order was not found or has been modified. Please refresh your data.',
                    'data' => $data
                ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            }
            $data->update([
                'OrderStatus' => '4',
                'LastOperator' => $request->lastOperator
            ]);

            // Log the activity
            activity('sales_order')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'sales_order_number' => $data->SalesOrder,
                    'old_status' => '1',
                    'new_status' => '4',
                    'status_description' => 'Available (In Warehouse)',
                    'operator' => $request->lastOperator,
                    'subject_type' => 'App\\Models\\SalesOrder\\SOMaster',
                    'subject_id' => $data->SalesOrder,
                    'event' => 'status_changed'
                ])
                ->log("Marked sales order #{$data->SalesOrder} as Available (In Warehouse)");

            $safe = [
                'SalesOrder' => $data->SalesOrder,
                'OrderStatus' => $data->OrderStatus,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status to "In Warehouse"',
                'data' => $safe
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
    }

    public function SOStatus_NotAvailable(Request $request)
    {   
        try{
            $data = SOMaster::where('SalesOrder', $request->salesOrder)->where('OrderStatus',1)->first();
            if(!$data){
                return response()->json([
                    'success' => false,
                    'message' => 'The sales order was not found or has been modified. Please refresh your data.',
                    'data' => $data
                ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            }
            $data->update([
                'OrderStatus' => '2',
                'LastOperator' => $request->lastOperator
            ]);

            // Log the activity
            activity('sales_order')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'sales_order_number' => $data->SalesOrder,
                    'old_status' => '1',
                    'new_status' => '2',
                    'status_description' => 'Not Available (Open Back Order)',
                    'operator' => $request->lastOperator,
                    'subject_type' => 'App\\Models\\SalesOrder\\SOMaster',
                    'subject_id' => $data->SalesOrder,
                    'event' => 'status_changed'
                ])
                ->log("Marked sales order #{$data->SalesOrder} as Not Available (Open Back Order)");

            $safe = [
                'SalesOrder' => $data->SalesOrder,
                'OrderStatus' => $data->OrderStatus,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status to "Not Available"',
                'data' => $safe
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
    }

    public function SOStatus_InAvailable(Request $request)
    {   
        try{
            $data = SOMaster::where('SalesOrder', $request->salesOrder)->where('OrderStatus',2)->first();
            if(!$data){
                return response()->json([
                    'success' => false,
                    'message' => 'The sales order was not found or has been modified. Please refresh your data.',
                    'data' => $data
                ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            }
            $data->update([
                'OrderStatus' => '3',
                'LastOperator' => $request->lastOperator
            ]);

            // Log the activity
            activity('sales_order')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'sales_order_number' => $data->SalesOrder,
                    'old_status' => '2',
                    'new_status' => '3',
                    'status_description' => 'Restocked (Release Back Order)',
                    'operator' => $request->lastOperator,
                    'subject_type' => 'App\\Models\\SalesOrder\\SOMaster',
                    'subject_id' => $data->SalesOrder,
                    'event' => 'status_changed'
                ])
                ->log("Marked sales order #{$data->SalesOrder} as Restocked (Release Back Order)");

            $safe = [
                'SalesOrder' => $data->SalesOrder,
                'OrderStatus' => $data->OrderStatus,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status to "Release Back Order"',
                'data' => $safe
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); 
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
    }
    
    public function SOStatus_InSuspense(Request $request)
    {   
        try{
            $data = SOMaster::where('SalesOrder', $request->salesOrder)->where('OrderStatus',4)->first();
            if(!$data){
                return response()->json([
                    'success' => false,
                    'message' => 'The sales order was not found or has been modified. Please refresh your data.',
                    'data' => $data
                ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            }
            $data->update([
                'OrderStatus' => 'S',
                'LastOperator' => $request->lastOperator
            ]);

            // Log the activity
            activity('sales_order')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'sales_order_number' => $data->SalesOrder,
                    'old_status' => '4',
                    'new_status' => 'S',
                    'status_description' => 'Suspense Order',
                    'operator' => $request->lastOperator,
                    'subject_type' => 'App\\Models\\SalesOrder\\SOMaster',
                    'subject_id' => $data->SalesOrder,
                    'event' => 'status_changed'
                ])
                ->log("Marked sales order #{$data->SalesOrder} as Suspense Order");

            $safe = [
                'SalesOrder' => $data->SalesOrder,
                'OrderStatus' => $data->OrderStatus,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status to "In Suspense"',
                'data' => $safe
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
    }

    public function SOStatus_ToInvoice(Request $request)
    {   
        try{
            $data = SOMaster::where('SalesOrder', $request->salesOrder)->where('OrderStatus',4)->first();
            if(!$data){
                return response()->json([
                    'success' => false,
                    'message' => 'The sales order was not found or has been modified. Please refresh your data.',
                    'data' => $data
                ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            }
            $data->update([
                'OrderStatus' => '8',
                'LastOperator' => $request->lastOperator
            ]);

            // Log the activity
            activity('sales_order')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'sales_order_number' => $data->SalesOrder,
                    'old_status' => '4',
                    'new_status' => '8',
                    'status_description' => 'Proceed to Invoice',
                    'operator' => $request->lastOperator,
                    'subject_type' => 'App\\Models\\SalesOrder\\SOMaster',
                    'subject_id' => $data->SalesOrder,
                    'event' => 'status_changed'
                ])
                ->log("Marked sales order #{$data->SalesOrder} to Proceed to Invoice");

            $safe = [
                'SalesOrder' => $data->SalesOrder,
                'OrderStatus' => $data->OrderStatus,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status "To Invoice"',
                'data' => $safe
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
    }

    public function SOStatus_Complete(Request $request)
    {   
        try{
            $InventoryManager = new InventoryManager();

            $details = $request->sodata['details'];
            $soHeaderDetails = $request->sodata;
            unset($soHeaderDetails['details']);

            // Get the correct warehouse from SOMaster instead of using SODetail's MWarehouse
            $soMaster = SOMaster::where('SalesOrder', $request->salesOrder)->first();
            $correctWarehouse = $soMaster->Warehouse;
            
            $checkProdArr = array_map(function ($item) use ($correctWarehouse) {
                return [
                    'stockCode' => $item['MStockCode'],
                    // 'qty' => (float)$item['MOrderQty'],
                    'qty' => (float)$item['QTYinPCS'],
                    'warehouse'=> $correctWarehouse,
                ];
            }, $details);

            // Check if there's enough inventory before allowing completion
            $isEnough = $InventoryManager->isInvEnough($checkProdArr);
            if($isEnough){
                $data = SOMaster::where('SalesOrder', $request->salesOrder)->where('OrderStatus',8)->first();
                if(!$data){
                    return response()->json([
                        'success' => false,
                        'message' => 'The sales order was not found or has been modified. Please refresh your data.',
                        'data' => $data
                    ], 200);
                }
                $data->update([
                    'OrderStatus' => '9',
                    'LastOperator' => $request->lastOperator
                ]);

                foreach ($details as $detail) {
                    $sku = $detail['MStockCode'];
                    $warehouse = $correctWarehouse; // Use correct warehouse from SOMaster
                    $qty = $detail['QTYinPCS'];
                    
                    // Update the detail with correct warehouse for inventory movement
                    $detail['MWarehouse'] = $correctWarehouse;
                    
                    $InventoryManager->InvWareHouseDirectionHandler($sku, $warehouse, $qty, "OUT", null);
                    $InventoryManager->InvMovement($soHeaderDetails,  $detail, 'S', null);
                }

                // Create Accounts Receivable record for completed Sales Order
                try {
                    // Calculate total amount from SO details
                    // BUGFIX: Use line total (MPrice) directly; do NOT multiply by QTYinPCS
                    // MPrice already represents Quantity x Unit Price for the line
                    $totalAmount = array_sum(array_map(function($detail) {
                        return (float)($detail['MPrice'] ?? 0);
                    }, $details));

                    // Get customer information
                    $customer = Customer::where('Customer', $data->Customer)->first();
                    
                    // Create the accounts receivable record
                    $newAR = AccountsReceivable::create([
                        'date' => now()->format('Y-m-d'),
                        'customer_code' => $data->Customer,
                        'customer_name' => $customer ? $customer->Name : $data->CustomerName,
                        'so_number' => $data->SalesOrder,
                        'reference_number' => $data->CustomerPoNumber,
                        'total_amount' => $totalAmount,
                        'terms' => $customer && $customer->TermsCode ? $customer->TermsCode . ' Days' : '30 Days',
                        'status' => 'Outstanding',
                        'remarks' => 'Auto-generated from completed Sales Order',
                        'process_by' => $request->lastOperator ?? 'system'
                    ]);

                    try {
                        CustomerCredit::updateCustomerCredit($data->Customer);
                    } catch (\Throwable $e) {
                    }


                    // Mirror AP RR flow: apply available customer credit memos to THIS new invoice
                    try {
                        // Build target reference for descriptive notes/remarks
                        $targetRef = $newAR->reference_number ?? $newAR->so_number ?? ('AR-' . $newAR->id);

                        // Determine current balance of the new AR
                        $paidOnNew = Payment::where('accounts_receivable_id', $newAR->id)->sum('payment_amount');
                        $newArBalance = max(0, floatval($newAR->total_amount) - floatval($paidOnNew ?? 0));

                        if ($newArBalance > 0.0001) {
                            // Fetch all AR records for this customer that generated credits
                            $creditRecords = AccountsReceivable::where('customer_code', $newAR->customer_code)
                                ->where(function($q) {
                                    $q->whereNotNull('credit_generated')
                                      ->where('credit_generated', '>', 0);
                                })
                                ->orderBy('date', 'asc')
                                ->orderBy('id', 'asc')
                                ->get(['id', 'reference_number', 'so_number', 'credit_generated']);

                            foreach ($creditRecords as $src) {
                                if ($newArBalance <= 0.0001) break;

                                $sourceRef = $src->reference_number ?? $src->so_number ?? ('AR-' . $src->id);
                                // Compute already used amount for this source
                                $usedAmount = Payment::whereNotNull('accounts_receivable_id')
                                    ->where('reference_number', 'AUTO-CM-' . $sourceRef)
                                    ->sum('payment_amount') ?? 0;

                                $available = floatval($src->credit_generated ?? 0) - floatval($usedAmount);
                                if ($available <= 0.0001) continue;

                                $apply = min($available, $newArBalance);
                                if ($apply > 0.0001) {
                                    // Create automatic payment toward the new invoice
                                    Payment::create([
                                        'accounts_receivable_id' => $newAR->id,
                                        'payment_amount' => $apply,
                                        'payment_type' => 'cash',
                                        'payment_status' => ($apply >= $newArBalance) ? 'full' : 'partial',
                                        'payment_date' => now(),
                                        'reference_number' => 'AUTO-CM-' . $sourceRef,
                                        'remarks' => 'Automatic credit memo application from ' . $sourceRef . ' to new invoice ' . $targetRef,
                                        'process_by' => $request->lastOperator ?? (auth()->user()->name ?? 'System'),
                                    ]);

                                    // Record AR credit memo application entry (new invoice case)
                                    try {
                                        ARCreditMemoApplication::create([
                                            'source_ar_id' => $src->id,
                                            'target_ar_id' => $newAR->id,
                                            'credit_amount' => $apply,
                                            'application_date' => now(),
                                            'created_by' => auth()->id(),
                                            'notes' => 'Automatic credit memo application from ' . $sourceRef . ' to new invoice ' . $targetRef,
                                            'status' => 'Applied',
                                        ]);
                                    } catch (\Exception $e) {
                                        Log::warning('Failed to record AR CM application (new invoice)', [
                                            'error' => $e->getMessage(),
                                            'source_ar_id' => $src->id,
                                            'target_ar_id' => $newAR->id,
                                            'amount' => $apply,
                                        ]);
                                    }

                                    // Update running balance and status of the new AR
                                    $newArBalance = max(0, $newArBalance - $apply);
                                    $newStatus = ($newArBalance <= 0.0001) ? 'Settled' : 'Outstanding';
                                    $newAR->status = $newStatus;
                                    $newAR->current_balance = $newArBalance;
                                    $newAR->last_balance_update = now();
                                    $newAR->save();
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        // Do not fail SO completion if auto-apply fails; just log
                        Log::error('Error applying AR auto credit memos to new invoice: ' . $e->getMessage(), [
                            'customer_code' => $data->Customer ?? null,
                            'ar_id' => isset($newAR) ? $newAR->id : null,
                        ]);
                    }
                } catch (\Exception $arException) {
                    // Log the error but don't fail the SO completion
                    Log::error('Failed to create Accounts Receivable for SO ' . $data->SalesOrder . ': ' . $arException->getMessage());
                }

                // Log the activity
                activity('sales_order')
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => $request->header('User-Agent'),
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'sales_order_number' => $data->SalesOrder,
                        'old_status' => '8',
                        'new_status' => '9',
                        'status_description' => 'Completed Order',
                        'operator' => $request->lastOperator,
                        'inventory_movements' => count($details),
                        'subject_type' => 'App\\Models\\SalesOrder\\SOMaster',
                        'subject_id' => $data->SalesOrder,
                        'event' => 'status_changed'
                    ])
                    ->log("Completed sales order #{$data->SalesOrder} with inventory movements for " . count($details) . " items");
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales Order cannot be invoiced due to insufficient stock.',
                ], 200);  // HTTP 200 OK
            }

            $safe = $data ? [
                'SalesOrder' => $data->SalesOrder,
                'OrderStatus' => $data->OrderStatus,
            ] : null;

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status "To Completed"',
                'data' => $safe
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
    }

    public function SOStatus_Delete(Request $request)
    {   
        try{
            // Get the sales order data before updating
            $salesOrder = SOMaster::where('SalesOrder', $request->salesOrder)->first();
            if(!$salesOrder){
                return response()->json([
                    'success' => false,
                    'message' => 'The sales order was not found.',
                    'data' => null
                ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            }

            $oldStatus = $salesOrder->OrderStatus;
            $data = SOMaster::where('SalesOrder', $request->salesOrder)->update([
                'OrderStatus' => '\\',
                'LastOperator' => $request->lastOperator
            ]);

            // Log the activity
            activity('sales_order')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'sales_order_number' => $request->salesOrder,
                    'old_status' => $oldStatus,
                    'new_status' => '\\',
                    'status_description' => 'Deleted Order',
                    'operator' => $request->lastOperator,
                    'subject_type' => 'App\\Models\\SalesOrder\\SOMaster',
                    'subject_id' => $request->salesOrder,
                    'event' => 'deleted'
                ])
                ->log("Deleted sales order #{$request->salesOrder}");

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status to "Deleted"',
                'data' => $data
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }
    }
}
