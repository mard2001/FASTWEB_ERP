<?php

namespace App\Http\Controllers\api\MasterData;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

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

    /**
     * Sanitize data for UTF-8 encoding to prevent JSON encoding errors
     */
    private function sanitizeForJson($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeForJson'], $data);
        } elseif (is_string($data)) {
            // More aggressive UTF-8 cleaning
            $sanitized = @iconv('UTF-8', 'UTF-8//IGNORE', $data);
            if ($sanitized === false) {
                $sanitized = mb_convert_encoding($data, 'UTF-8', 'auto');
            }
            // Remove control characters and non-printable characters
            $sanitized = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $sanitized);
            // Additional safety: remove any remaining invalid sequences (strip low/high ASCII)
            $sanitized = preg_replace('/[^\P{C}\n]+/u', '', $sanitized);
            return $sanitized ?: (string)$data; // Fallback to original if all cleaning fails
        }
        return $data;
    }

    private function convert_from_latin1_to_utf8_recursively($dat)
    {
        if (is_string($dat)) {
            return mb_convert_encoding($dat, 'UTF-8', 'auto');
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

            // Log the activity with error handling
            try {
                $sanitizedData = $this->sanitizeForJson($data);
                activity('product_maintenance')
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => substr($request->header('User-Agent', ''), 0, 100),
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'product_code' => $sanitizedData['StockCode'] ?? 'N/A',
                        'product_description' => $sanitizedData['Description'] ?? 'N/A',
                        'product_brand' => $sanitizedData['Brand'] ?? 'N/A',
                        'product_uom' => $sanitizedData['StockUom'] ?? 'N/A',
                        'action_type' => 'create',
                        'product_data' => $sanitizedData,
                        'subject_type' => 'App\\Models\\Product',
                        'subject_id' => $sanitizedData['StockCode'] ?? 'N/A',
                        'event' => 'created'
                    ])
                    ->log("Created new product: " . ($sanitizedData['Description'] ?? 'N/A') . " (Code: " . ($sanitizedData['StockCode'] ?? 'N/A') . ") - Brand: " . ($sanitizedData['Brand'] ?? 'N/A'));
            } catch (\Exception $e) {
                // Simple fallback logging if detailed logging fails
                try {
                    activity('product_maintenance')
                        ->withProperties([
                            'action_type' => 'create',
                            'product_code' => $data['StockCode'] ?? 'N/A',
                            'event' => 'created'
                        ])
                        ->log("Product created (Code: " . ($data['StockCode'] ?? 'N/A') . ")");
                } catch (\Exception $e2) {
                    // Last resort - basic log without properties
                    activity('product_maintenance')->log("Product creation operation performed");
                }
            }

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

            // Store original data for logging
            $originalData = $this->sanitizeForJson($found->toArray());

            $data = $this->convertFormData($request);

            // Handle file upload
            if ($request->hasFile('image_file')) {
                // Store the file
                $filePath = $request->file('image_file')->store('images', 'public');

                $data['uploaded_image'] = $filePath; // Store the file path in the data array
            }

            //return response()->json($data);

            $found->update($data);

            // Log the activity with error handling
            try {
                $sanitizedData = $this->sanitizeForJson($data);
                
                // Prepare old and new values for change tracking
                $oldValues = [];
                $newValues = [];
                $changedFields = [];
                foreach ($data as $field => $newValue) {
                    $oldValue = $originalData[$field] ?? null;
                    if ($oldValue !== $newValue) {
                        $changedFields[] = $field;
                        $oldValues[$field] = $oldValue;
                        $newValues[$field] = $newValue;
                    }
                }

                activity('product_maintenance')
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => substr($request->header('User-Agent', ''), 0, 100),
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'product_id' => $id,
                        'product_code' => $sanitizedData['StockCode'] ?? $originalData['StockCode'] ?? 'N/A',
                        'product_description' => $sanitizedData['Description'] ?? $originalData['Description'] ?? 'N/A',
                        'product_brand' => $sanitizedData['Brand'] ?? $originalData['Brand'] ?? 'N/A',
                        'action_type' => 'update',
                        'updated_fields' => $changedFields,
                        'subject_type' => 'App\\Models\\Product',
                        'subject_id' => $id,
                        'event' => 'updated',
                        // Add old and attributes for change tracking (same format as salesman and customer)
                        'old' => $oldValues,
                        'attributes' => $newValues
                    ])
                    ->log("Updated product: " . ($sanitizedData['Description'] ?? $originalData['Description'] ?? 'N/A') . " (Code: " . ($sanitizedData['StockCode'] ?? $originalData['StockCode'] ?? 'N/A') . ") - Brand: " . ($sanitizedData['Brand'] ?? $originalData['Brand'] ?? 'N/A'));
            } catch (\Exception $e) {
                // Simple fallback logging if detailed logging fails
                try {
                    activity('product_maintenance')
                        ->withProperties([
                            'action_type' => 'update',
                            'product_id' => $id,
                            'product_code' => $data['StockCode'] ?? $originalData['StockCode'] ?? 'N/A',
                            'event' => 'updated'
                        ])
                        ->log("Product updated (ID: {$id})");
                } catch (\Exception $e2) {
                    // Last resort - basic log without properties
                    activity('product_maintenance')->log("Product update operation performed");
                }
            }

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

            // Store product data for logging before deletion
            $productData = $this->sanitizeForJson($data->toArray());

            $data->delete();

            // Log the activity with error handling
            try {
                activity('product_maintenance')
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => substr($request->header('User-Agent', ''), 0, 100),
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'product_id' => $id,
                        'product_code' => $productData['StockCode'] ?? 'N/A',
                        'product_description' => $productData['Description'] ?? 'N/A',
                        'product_brand' => $productData['Brand'] ?? 'N/A',
                        'product_uom' => $productData['StockUom'] ?? 'N/A',
                        'action_type' => 'delete',
                        'deleted_data' => $productData,
                        'subject_type' => 'App\\Models\\Product',
                        'subject_id' => $id,
                        'event' => 'deleted'
                    ])
                    ->log("Deleted product: " . ($productData['Description'] ?? 'N/A') . " (Code: " . ($productData['StockCode'] ?? 'N/A') . ") - Brand: " . ($productData['Brand'] ?? 'N/A'));
            } catch (\Exception $e) {
                // Simple fallback logging if detailed logging fails
                try {
                    activity('product_maintenance')
                        ->withProperties([
                            'action_type' => 'delete',
                            'product_id' => $id,
                            'product_code' => $productData['StockCode'] ?? 'N/A',
                            'event' => 'deleted'
                        ])
                        ->log("Product deleted (ID: {$id})");
                } catch (\Exception $e2) {
                    // Last resort - basic log without properties
                    activity('product_maintenance')->log("Product deletion operation performed");
                }
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
