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
            $data = SOMaster::select('SalesOrder', 'NextDetailLine', 'OrderStatus', 'DocumentType', 'Customer', 'CustomerName', 'Salesperson', 'CustomerPoNumber', 'OrderDate', 'EntrySystemDate', 'ReqShipDate', 'DateLastDocPrt', 'InvoiceCount', 'Branch', 'Warehouse', 'ShipAddress1', 'ShipAddress2', 'ShipAddress3', 'ShipToGpsLat', 'ShipToGpsLong', 'Branch', )
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

        // Log the activity
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
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);  // HTTP 200 OK
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
                    $totalAmount = array_sum(array_map(function($detail) {
                        return (float)$detail['QTYinPCS'] * (float)($detail['MPrice'] ?? 0);
                    }, $details));

                    // Get customer information
                    $customer = Customer::where('Customer', $data->Customer)->first();
                    
                    // Create the accounts receivable record
                    AccountsReceivable::create([
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
