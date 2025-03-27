<?php

namespace App\Http\Controllers\api\Orders;

use Illuminate\Http\Request;
use App\http\Requests\Orders\StorePOItemsRequest;
use App\models\Orders\POItems;

class POItemsController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $poItems = POItems::all();

            if (!$poItems) {
                return response()->json([
                    'success' => false,
                    'message' => 'No shipped to details found',
                    'data' => []
                ], 404);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'PO Items retrieved successfully',
                'data' => $poItems
            ], 200);  // HTTP 200 OK


        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePOItemsRequest $request)
    {
        try {
            // return response()->json($request);
            $isInsertSuccess = POItems::create($request->data);

            return response()->json([
                'success' => true,
                'message' => 'Items inserted succesfully!',
            ]);
        } catch (\Exception  $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $response = array();
        try {

            $data = POItems::find($id);

            if (!$data) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];
            } else {
                $response = [
                    'response' => $data,
                    'status_response' => 1
                ];
            }
        } catch (\Exception $e) {

            $response = [
                'response' => $e->getMessage(),
                'status_response' => 0
            ];
        }

        return response()->json($response);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(StorePOItemsRequest $request, string $id)
    {
        $response = [
            'response' => 'Items updated succesfully!',
            'status_response' => 1
        ];

        try {
            $data = $request['data'];
            $found = POItems::find($id);

            if (!$found) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];
            }

            $found->update($data);
        } catch (\Exception $e) {

            $response = [
                'response' => $e->getMessage(),
                'status_response' => 0
            ];
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = array();
        try {

            // $data = POItems::with('POHeader')->find($id);

            $data = POItems::find($id);


            if (!$data) {
                $response = [
                    'message' => 'data not found',
                    'success' => false
                ];

                //break to reserve server resouces
                return response()->json($response);
            }


            if ($data->POHeader->POStatus == null) {

                $data->delete();
                return response()->json( [
                    'message' => 'Item deleted succesfully!',
                    'success' => true
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Cannot delete item is already processed.',
            ], 400);
        } catch (\Exception $e) {

            $response = [
                'message' => $e->getMessage(),
                'success' => false
            ];
        }

        return response()->json($response);
    }

    public function searchByPO(Request $request, string $po)
    {

        try {

            $POItems = POItems::where('PONumber', $po)->orderBy('PRD_INDEX', 'desc')->get();

            if (!$POItems) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase Order Items not found',
                    'data' => []
                ], 404);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order Items retrieved successfully',
                'data' => $POItems
            ], 200);  // HTTP 200 OK 

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error

        }
    }
}
