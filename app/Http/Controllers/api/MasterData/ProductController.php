<?php

namespace App\Http\Controllers\api\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Product;
use App\Http\Controllers\helpers\DynamicSQLHelper;
use App\Traits\dbconfigs;

class ProductController extends DynamicSQLHelper
{
    use dbconfigs;

    // Declare the property but do not assign it a value directly
    private $connection;

    public function __construct()
    {
        // Dynamically set the connection using the method from the trait
        $this->connection = $this->getFastSFADBConfig();
        
    }

    private function convert_from_latin1_to_utf8_recursively($dat)
    {
        if (is_string($dat)) {
            return utf8_encode($dat);
        } elseif (is_array($dat)) {
            $ret = [];
            foreach ($dat as $i => $d) $ret[$i] = self::convert_from_latin1_to_utf8_recursively($d);
            return $ret;
        } elseif (is_object($dat)) {
            foreach ($dat as $i => $d) $dat->$i = self::convert_from_latin1_to_utf8_recursively($d);
            return $dat;
        } else {
            return $dat;
        }
    }

    public function index(Request $request)
    {
        try {
            $products = Product::select('StockCode', 'Description', 'StockUom', 'AlternateUom', 'OtherUom')->get();
            $products = self::convert_from_latin1_to_utf8_recursively($products->toArray());

            if (!$products) {
                return response()->json([
                    'success' => false,
                    'message' => 'No product details found',
                    'data' => []
                ], 404);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Products details retrieved successfully',
                'data' => $products
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function getProductList(Request $request)
    {

        try {

            $data = Product::select(['stockCode', 'price', 'case_con', 'uploaded_image'])->orderBy('id')->get();

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
        $response = array();

        try {
            $data = $this->convertFormData($request);

            if (!count($data) > 0) {

                $response = [
                    'response' => 'No data in csv',
                    'status_response' => 2,
                ];
                return;
            }


            // Handle file upload
            if ($request->hasFile('image_file')) {
                // Validate the file (optional, but recommended)
                $request->validate([
                    'image_file' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Customize validation rules as needed
                ]);

                // Store the file
                $filePath = $request->file('image_file')->store('images', 'public'); // Store in the 'images' directory in 'public' disk
                $data['uploaded_image'] = $filePath; // Store the file path in the data array
            }

            //static blank because column dont allow null;
            $data['buyingAccounts'] = "";
            $data['Supplier'] = "";

            Product::insert($data);

            $response = [
                'response' => 'Items updated succesfully!',
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

    public function storebulk(Request $request)
    {
        $responseMessage = array();

        try {

            // Prepare data for insertion
            //convert formData into json

            $bulkdata = $request['data'];

            $successfulInserts = 0;

            $responseMessage = [
                'response' => 'Items inserted succesfully!',
                'status_response' => 1,
                'total_inserted' => count($bulkdata),
                'tatal_entry' => count($bulkdata)
            ];

            foreach ($bulkdata as $perRow) {

                try {
                    // Attempt to insert the row
                    Product::insert($perRow);
                    $successfulInserts++;
                } catch (\Exception $e) {
                    $responseMessage = [
                        'response' => $e->getMessage(),
                        'status_response' => 0,
                        'total_inserted' => 0,
                        'tatal_entry' => count($bulkdata)
                    ];
                }
            }
        } catch (\Exception $e) {
            $responseMessage = [
                'response' => $e->getMessage(),
                'status_response' => 0,
                'total_inserted' => 0,
                'tatal_entry' => count($bulkdata)
            ];
        }

        return response()->json($responseMessage);
    }





    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {

        $response = array();
        try {

            $data = Product::find($id);

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
    public function update(Request $request, string $id)
    {
        $response = array();

        try {
            $found = Product::find($id);

            if (!$found) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];

                //break to reserve server resouces
                return response()->json($response);
            }

            $data = $this->convertFormData($request);

            // Handle file upload
            if ($request->hasFile('image_file')) {
                // Store the file
                $filePath = $request->file('image_file')->store('images', 'public');

                $data['uploaded_image'] = $filePath; // Store the file path in the data array
            }

            //return response()->json($data);

            $found->update($data);

            $response = [
                'response' => 'Items updated succesfully!',
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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $response = array();
        try {

            $data = Product::find($id);

            if (!$data) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];

                //break to reserve server resouces
                return response()->json($response);
            }

            $data->delete();

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
