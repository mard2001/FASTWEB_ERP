<?php

namespace App\Http\Controllers\api;

use App\Models\ProductPrices;
use Illuminate\Http\Request;
use App\Traits\dbconfigs;

class ProductPricesController
{
    use dbconfigs;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ProductPrices  $productPrices
     * @return \Illuminate\Http\Response
     */
    public function show(ProductPrices $productPrices)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ProductPrices  $productPrices
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductPrices $productPrices)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ProductPrices  $productPrices
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductPrices $productPrices)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ProductPrices  $productPrices
     * @return \Illuminate\Http\Response
     */
    public function destroy(ProductPrices $productPrices)
    {
        //
    }

    public function getProductPrice(Request $request)
    {

        try {

            $request->validate([
                'StockCode' => 'required|integer|exists:tblPrice,StockCode', // Fixed "exist" to "exists"
                'priceCode' => 'required|string', // Added `string` validation for priceCode if it's a string
            ]);

            $pricesConnection = $this->getFastSFADBConfig();

            $data = ProductPrices::on($pricesConnection)->where('stockCode', $request->StockCode)->where('priceCode', $request->priceCode)->first();

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

    public function getProductPricev2(Request $request)
    {
        try {

            // Validate the request
            $validatedData = $request->validate([
                'stockCode' => 'required', // Ensure the stock code exists
                'priceCode' => 'required|string', // Validate priceCode as a string
            ]);

            // Query the product prices table
            $data = ProductPrices::where('stockCode', trim($validatedData['stockCode']))
                ->where('priceCode', trim($validatedData['priceCode']))
                ->first();
                

            // Return appropriate response
            if (!$data) {
                return response()->json([
                    'response' => 'Data not found',
                    'status_response' => 2,
                ]);
            }

            return response()->json([
                'response' => $data,
                'convertionFactor' => $data->product->only(['ConvFactAltUom', 'ConvFactOthUom']),
                'status_response' => 1,
            ], 200); // Use HTTP 200 for success

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'response' => $e->errors(),
                'status_response' => 0,
            ], 422); // Use HTTP 422 for validation errors
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'response' => $e->getMessage(),
                'status_response' => 0,
            ], 500); // Use HTTP 500 for server errors
        }
    }

    public function getProductPriceCodes()
    {
        try {

            // Query the product prices table
            $data = ProductPrices::select('PRICECODE')->distinct()->get();

            // Return appropriate response
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Price codes not found',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Price code retrieved successfully',
                'data' => $data,
            ], 200); // Use HTTP 200 for success

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            return response()->json([
                'message' => $e->errors(),
                'success' => 0,
            ], 422); // Use HTTP 422 for validation errors
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json([
                'message' => $e->getMessage(),
                'success' => 0,
            ], 500); // Use HTTP 500 for server errors
        }
    }
}
