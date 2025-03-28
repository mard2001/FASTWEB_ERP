<?php

namespace App\Http\Controllers\api\SalesOrder;

use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SalesOrder\SODetail;
use App\Models\SalesOrder\SOMaster;
use App\Http\Controllers\Controller;

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
            )->get();
            
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

            // dd($data['CustomerInfo']['ContactPerson']);
            // DB::transaction(function () use ($data) {
                $items = Arr::pull($data, 'Items');
                $so = SOMaster::create([
                    'NextDetailLine'=> count($items)+1,
                    'Customer'=> $data['CustomerInfo']['Customer'],
                    'Salesperson'=> $data['CustomerInfo']['Salesperson'],
                    'OrderDate' => $data['OrderDate'],
                    'EntrySystemDate'=> date('Y-m-d'),
                    'ReqShipDate' => $data['ReqShipDate'],
                    'DateLastDocPrt'=> date('Y-m-d'),
                    'CustomerName'=> $data['CustomerInfo']['Contact'],
                    'ShipAddress1'=> $data['CustomerInfo']['SoldToAddr1'],
                    'ShipAddress2'=> $data['CustomerInfo']['SoldToAddr2'],
                    'ShipAddress3'=> $data['CustomerInfo']['SoldToAddr3'],
                    'LastOperator' => $data['LastOperator'],
                ]);
                $so->sodetails()->createMany($items);
            // });

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
            $data = SOMaster::select('SalesOrder', 'NextDetailLine', 'OrderStatus', 'DocumentType', 'Customer', 'CustomerName', 'Salesperson', 'CustomerPoNumber', 'OrderDate', 'EntrySystemDate', 'ReqShipDate', 'DateLastDocPrt', 'InvoiceCount', 'Branch', 'Warehouse', 'ShipAddress1', 'ShipToGpsLat', 'ShipToGpsLong')
                ->where('SalesOrder', $salesorderID)
                ->first();
            $details = SODetail::select('SalesOrder', 'SalesOrderLine', 'MStockCode', 'MStockDes', 'MWarehouse', 'MOrderQty', 'MOrderUom', 'MStockQtyToShp', 'MStockingUom', 'MconvFactOrdUm', 'MPrice', 'MPriceUom', 'MProductClass', 'MStockUnitMass', 'MStockUnitVol', 'MPriceCode', 'MConvFactAlloc', 'MConvFactUnitQ', 'MAltUomUnitQ', 'MUnitCost', 'QTYinPCS')
                ->where('SalesOrder', $salesorderID)
                ->get();
            $data->details = $details;

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
    public function update(Request $request, $id)
    {
        //
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
            $data = SOMaster::where('SalesOrder', $request->salesOrder)->update([
                'OrderStatus' => '4',
                'LastOperator' => $request->lastOperator
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales Order Set Status to In Warehouse',
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
            $data = SOMaster::where('SalesOrder', $request->salesOrder)->update([
                'OrderStatus' => '2',
                'LastOperator' => $request->lastOperator
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales Order Set Status to Not Available',
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
            $data = SOMaster::where('SalesOrder', $request->salesOrder)->update([
                'OrderStatus' => '3',
                'LastOperator' => $request->lastOperator
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales Order Set Status to Release Back Order',
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
            $data = SOMaster::where('SalesOrder', $request->salesOrder)->update([
                'OrderStatus' => 'S',
                'LastOperator' => $request->lastOperator
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales Order Set Status to In Suspense',
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
            $data = SOMaster::where('SalesOrder', $request->salesOrder)->update([
                'OrderStatus' => '8',
                'LastOperator' => $request->lastOperator
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales Order Set Status to Invoice',
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
            $data = SOMaster::where('SalesOrder', $request->salesOrder)->update([
                'OrderStatus' => '9',
                'LastOperator' => $request->lastOperator
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Sales Order Set Status to Completed',
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
                'message' => 'Sales Order Set Status to Completed',
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
