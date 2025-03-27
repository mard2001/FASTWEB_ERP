<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\SupplierShipTo;


class SupplierShipToController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        try {
            $shippedTo = SupplierShipTo::all();

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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {

            $request->validate([
                'companyName' => 'required|string|max:255',
                'Address' => 'required|string|max:255',
                'contactCellNumber' => 'nullable|string|max:15',  // assuming max length of cell number is 15
                'contactPerson' => 'nullable|string|max:255',      // assuming max length of contact person is 255
            ]);

            SupplierShipTo::create($request);

            return response()->json([
                'response' => 'Supplier inserted succesfully!',
                'status_response' => 1
            ]);
        } catch (\Exception  $e) {

            return response()->json([
                'response' => $e->getMessage(),
                'status_response' => 0
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Request  $Request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRequestRequest  $request
     * @param  \App\Models\Request  $Request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Request  $Request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $Request)
    {
        //
    }
}
