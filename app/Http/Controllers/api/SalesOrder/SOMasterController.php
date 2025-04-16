<?php

namespace App\Http\Controllers\api\SalesOrder;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\Customer\Customer;
use App\Services\InventoryManager;
use Illuminate\Support\Facades\DB;
use App\Models\SalesOrder\SODetail;
use App\Models\SalesOrder\SOMaster;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Event;
use App\Events\Inventory\InventoryMovement;
use App\Events\Inventory\InventoryWarehouse;

class SOMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $today = date('Y-m-d'); // Today's date
            $oneMonthAgo = date('Y-m-d', strtotime('-1 month'));

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
            $data = $request->data;
            $items = Arr::pull($data, 'Items');
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
            $so->sodetails()->createMany($items);

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
        $SOdata->sodetails()->createMany($newItems);

        // Process deleted items (remove)
        foreach ($deletedItems as $item) {
            SODetail::where('SalesOrder', $salesOrderId)
                ->where('MStockCode', $item['MStockCode'])
                ->delete();
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
                ], 200);
            }
            $data->update([
                'OrderStatus' => '4',
                'LastOperator' => $request->lastOperator
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status to "In Warehouse"',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
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
                ], 200);
            }
            $data->update([
                'OrderStatus' => '2',
                'LastOperator' => $request->lastOperator
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status to "Not Available"',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
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
                ], 200);
            }
            $data->update([
                'OrderStatus' => '3',
                'LastOperator' => $request->lastOperator
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status to "Release Back Order"',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
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
                ], 200);
            }
            $data->update([
                'OrderStatus' => 'S',
                'LastOperator' => $request->lastOperator
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status to "In Suspense"',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
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
                ], 200);
            }
            $data->update([
                'OrderStatus' => '8',
                'LastOperator' => $request->lastOperator
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status "To Invoice"',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function SOStatus_Complete(Request $request)
    {   
        try{
            $InventoryManager = new InventoryManager();

            $details = $request->sodata['details'];
            $soHeaderDetails = $request->sodata;
            unset($soHeaderDetails['details']);

            $checkProdArr = array_map(function ($item) {
                return [
                    'stockCode' => $item['MStockCode'],
                    // 'qty' => (float)$item['MOrderQty'],
                    'qty' => (float)$item['QTYinPCS'],
                    'warehouse'=> $item['MWarehouse'],
                ];
            }, $details);

            $isEnough = 1;
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
                    $warehouse = $detail['MWarehouse'];
                    $qty = $detail['QTYinPCS'];
                    $InventoryManager->InvWareHouseDirectionHandler($sku, $warehouse, $qty, "OUT");
                    $InventoryManager->InvMovement($soHeaderDetails,  $detail, 'S');
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Sales Order cannot be invoiced due to insufficient stock.',
                ], 200);  // HTTP 200 OK
            }

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status "To Completed"',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function SOStatus_Delete(Request $request)
    {   
        try{
            $data = SOMaster::where('SalesOrder', $request->salesOrder)->update([
                'OrderStatus' => '\\',
                'LastOperator' => $request->lastOperator
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales Order set status to "Deleted"',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
