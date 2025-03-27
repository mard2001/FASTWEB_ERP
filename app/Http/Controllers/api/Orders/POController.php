<?php

namespace App\Http\Controllers\api\Orders;

use Illuminate\Http\Request;
use App\Models\Orders\PO;
use App\Models\Supplier;

use Illuminate\Support\Facades\DB;

use App\http\Requests\Orders\StorePOHeaderRequest;
use Illuminate\Support\Arr;
use Barryvdh\DomPDF\Facade\Pdf;

class POController
{


    public function index()
    {
        try {
            $purchaseOrders = PO::orderBy('DateUploaded', 'desc')->select('id', 'OrderNumber', 'PONumber', 'SupplierName', 'PODate', 'orderPlacer', 'totalDiscount', 'totalCost', 'POStatus')->get();

            if ($purchaseOrders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No purchase orders found',
                ], 200);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase orders retrieved successfully',
                'data' => $purchaseOrders
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function filterPOByStatus(Request $status)
    {
        try {
            // return response()->json(gettype($status));
            // $status =  $request->input('status');

            $query = PO::orderBy('DateUploaded', 'desc')->select('id', 'OrderNumber', 'PONumber', 'SupplierName', 'PODate', 'orderPlacer', 'totalDiscount', 'totalCost', 'POStatus')->whereIn('POStatus', $status);

            if (in_array(null, $status->all(), true)) {
                $query->orWhereNull('POStatus');
            }

            $purchaseOrders = $query->get();


            if ($purchaseOrders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No purchase orders found',
                ], 200);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase orders retrieved successfully',
                'data' => $purchaseOrders
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $data = $request->data;

            DB::transaction(function () use ($data) {
                $items = Arr::pull($data, 'Items');
                $po = PO::create($data);
                $po->POItems()->createMany($items);
            });


            return response()->json([
                'success' => true,
                'message' => 'New Purchase Order created successfully',
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
     */
    public function show(string $id)
    {
        $response = array();
        try {

            $data = PO::with('POItems')->findOrFail($id);
            $response = [
                'message' => 'Purchase orders retrieved successfully',
                'data' => $data,
                'success' => true,
            ];
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 200);  // HTTP 200 OK
        }

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePOHeaderRequest $request, string $id)
    {
        try {
            $data = $request['data'];
            $found = PO::findOrFail($id);

            if ($found->POStatus == null) {
                $found->fill($data);
                if ($found->isDirty()) {
                    $found->save();
                }

                foreach ($data['Items'] as $item) {
                    $found->POItems()->updateOrCreate(
                        ['StockCode' => $item['StockCode']], // Search condition
                        $item // Data to update or create
                    );
                }



                return response()->json([
                    'success' => true,
                    'message' =>  "PO updated succesfully!",
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot edit PO is already processed.',
                ], 400);
            }
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {

            $data = PO::find($id);

            if (!$data) {

                return response()->json([
                    'success' => false,
                    'message' => 'No purchase order found',
                ], 400);  // HTTP 400 BAD REQ.
            }


            if ($data->POStatus == null) {
                $data->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'PO deleted succesfully!',
                ], 200);
            } else {

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete PO is already processed.',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);  // HTTP 400 BAD REQ.

        }
    }

    public function POConfirm(string $poid)
    {
        try {

            $data = PO::find($poid);

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'PONumber invalid',
                ], 400);  // HTTP 400 BAD REQ.
            }

            if ($data->POStatus != null) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase order is already processed!',
                ], 400);
            }

            $data->POStatus = 1;
            $data->save();

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order confirmed succesfully!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);  // HTTP 400 BAD REQ.

        }
    }

    public function generatePDF(string $poid)
    {

        $data = PO::with('POItems')->where('PONumber', $poid)->firstOrFail();
        $data->SupplierCode = trim($data->SupplierCode);
        $data->posupplier = $data->posupplier->toArray();
        $data->POItems = $data->POItems->toArray();
        // dd($data->toArray());

        return view('Pages.PurchaseOrder-PDF', ['po' => $data]); // Pass the user to the view


        // $pdf = PDF::loadView('Pages.PurchaseOrder-PDF', ['po' => $data])
        //     ->setPaper('letter')
        //     ->setOptions(['margin-top' => 0, 'margin-bottom' => 0, 'margin-left' => 0, 'margin-right' => 0]);

        // return $pdf->download('purchase-order-' . $poid . '.pdf');

        // return response()->json($data);

    }
}
