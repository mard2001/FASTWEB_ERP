<?php

namespace App\Http\Controllers\api\Orders;

use Illuminate\Http\Request;
use App\Models\Orders\PO;
use Illuminate\Support\Facades\DB;

use App\http\Requests\Orders\StorePOHeaderRequest;
use App\http\Requests\Orders\StorePOItemsRequest;
use App\Http\Controllers\api\Orders\POItemsController;
use Illuminate\Support\Arr;

class POController
{
    protected $POItemsController;

    public function __construct(POItemsController $POItemsController)
    {
        $this->POItemsController = $POItemsController;
    }

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



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $data = $request->data;
            // return PO::create($data);;

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

    public function insertWholeData(Request $request)
    {
        try {
            $request->validate([
                'pdf_file' => 'file|mimes:pdf|max:10240', // Validate each file
            ]);

            $requestDataHeader = $request->data;
            $requestDataItems = collect($request->data['Items']);
            //return  $requestDataHeader;

            // Validate headers, items, and deliveries
            $validationErrors = $this->validateRequest($requestDataHeader);

            if ($validationErrors) {
                return response()->json($validationErrors);
            }

            unset($requestDataHeader['Items']);
            $headerInsertResult = PO::create($requestDataHeader);

            if ($headerInsertResult) {

                $items = new StorePOItemsRequest();
                $requestDataItems = $requestDataItems->map(function ($item) use ($headerInsertResult) {
                    // Add a new 'full_name' column to each user
                    $item->PONumber = $headerInsertResult->PONumber;
                    return $item;
                });


                $items->merge(['data' =>  $requestDataItems]);
                $items->merge(['dynamicConnection' => $request->connectionName]);

                $itemInsertResult = $this->POItemsController->store($items);
                $itemInsertResultData =  $itemInsertResult->getData(true);

                if ($itemInsertResultData['status'] == '0') {
                    $headerInsertResult->delete();
                    return response()->json($itemInsertResult);
                }
            }

            return response()->json([
                'response' => 'PDF data inserted succesfully!',
                'status' => 1
            ]);
        } catch (\Exception  $e) {

            return response()->json([
                'response' => $e->getMessage(),
                'status' => 0
            ]);
        }
    }

    private function validateRequest(array $header)
    {
        $validateHeader = StorePOHeaderRequest::validate($header);

        if ($validateHeader->fails()) {
            return array_merge(
                $validateHeader->errors()->toArray(),
            );
        }

        return null;
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
                $found->update($data);
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
        try {
            $data = PO::with('POItems')->findOrFail($poid);
            return view('Pages.PurchaseOrder-PDF', ['po' => $data]); // Pass the user to the view

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);  // HTTP 400 BAD REQ.

        }
    }
}
