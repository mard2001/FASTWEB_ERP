<?php

namespace App\Http\Controllers\api\MasterData;

use Illuminate\Http\Request;
use App\Models\Supplier;

class SupplierController
{
    public function index()
    {
        try {
            $data = Supplier::get();

            if (count($data) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Supplier details found',
                    'data' => []
                ], 404);   
            }

            return response()->json([
                'success' => true,
                'message' => 'All supplier details retrieved successfully',
                'data' => $data
            ], 200);   

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);   
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
        try {
            $data = $request->data;
            $res = Supplier::create($data);

            return response()->json( [
                'message' => 'Supplier inserted succesfully!',
                'success' => true,
            ]);

           
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
        try {
            $Supplier = Supplier::where('SupplierCode', $id)->first();

            if (is_null($Supplier)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Supplier details found',
                    'data' => []
                ], 404);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Supplier details retrieved successfully',
                'data' => $Supplier
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $supplierCode)
    {
        try {
            $data = $request['data'];
            $found = Supplier::where('SupplierCode', $supplierCode)->first();
            // dd($found);
            if (!$found) {
                $response = [
                    'message' => 'data not found',
                    'success' => false
                ];

                return response()->json($response);
            }

            $found->update($data);
            $response = [
                'message' => 'Supplier details updated succesfully!',
                'success' => true,
                "data"=> $found
            ];

        } catch (\Exception $e) {
            $response = [
                'message' => $e->getMessage(),
                'success' => false
            ];
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            
            $data = Supplier::where('SupplierCode', $id)->delete();

            if (!$data) {
                $response = [
                    'message' => 'Supplier data not found',
                    'success' => false
                ];

                //break to reserve server resouces
                return response()->json($response);
            }
            $response = [
                'message' => 'Supplier deleted succesfully!',
                'success' => true
            ];
        } catch (\Exception $e) {

            $response = [
                'message' => $e->getMessage(),
                'success' => 0
            ];
        }

        return response()->json($response);
    }

}
