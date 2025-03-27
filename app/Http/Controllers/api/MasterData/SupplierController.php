<?php

namespace App\Http\Controllers\api\MasterData;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController
{
    public function index(Request $request)
    {

        try {
            $shippedTo = Supplier::all();

            if ($shippedTo->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No shipped to details found',
                    'data' => []
                ], 404);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Shipped to details retrieved successfully',
                'data' => $shippedTo
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function getCustomersNames(Request $request)
    {
        try {
            
            $data = Supplier::select(['id', 'custname', 'address'])->orderBy('id')->get();

            if (!$data) {
                return response()->json([
                    'response' => 'data not found',
                    'status_response' => 0
                ]);
            } else {
                return response()->json([
                    'response' => $data,
                    'status_response' => 1
                ]);
            }
        } catch (\Exception $e) {

            return response()->json([
                'response' => $e->getMessage(),
                'status_response' => 0
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $responseMessage = array();
        // return $request['data'];
        try {

            Supplier::create($request);

            $responseMessage = [
                'response' => 'Items inserted succesfully!',
                'status_response' => 1,
            ];

           
        } catch (\Exception $e) {
            $responseMessage = [
                'response' => $e->getMessage(),
                'status_response' => 0,
                'total_inserted' => 0,
                'tatal_entry' => 0
            ];
        }

        return response()->json($responseMessage);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $response = array();
        try {
            
            $data = $request['data'];
            $found = Supplier::find($id);

            if (!$found) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];

                //break to reserve server resouces
                return response()->json($response);
            }

            //return response()->json($data);

            $found->update($data);

            $response = [
                'response' => 'Items updated succesfully!',
                'status_response' => 1,
            ];
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
    public function destroy(Request $request, string $id)
    {
        $response = array();
        try {
            
            $data = Supplier::where('id', $id)->delete();

            if (!$data) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];

                //break to reserve server resouces
                return response()->json($response);
            }
            $response = [
                'response' => 'Item deleted succesfully!',
                'status_response' => 1
            ];
        } catch (\Exception $e) {

            $response = [
                'response' => $e->getMessage(),
                'status_response' => 0
            ];
        }

        return response()->json($response);
    }

}
