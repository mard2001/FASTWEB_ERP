<?php

namespace App\Http\Controllers\api\Product;

use App\Models\Product;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Http\Requests\Products\StoreProductData;
use App\Http\Controllers\Helpers\DynamicSQLHelper;

class ProdController extends DynamicSQLHelper
{
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
            // Additional safety: remove any remaining invalid sequences
            $sanitized = htmlspecialchars(strip_tags($sanitized), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            return $sanitized ?: (string)$data; // Fallback to original if all cleaning fails
        }
        return $data;
    }
    
    public function index()
    {
        try {
            $purchaseOrders = Product::orderBy('StockCode','desc')
                                ->select( 
                                    'StockCode', 'Description', 'LongDesc', 'AlternateKey1', 'StockUom', 'AlternateUom', 'OtherUom', 'ConvFactAltUom', 'ConvFactOthUom', 'Mass', 'Volume', 'ProductClass', 'WarehouseToUse', 'Brand'
                                )->get();

            if ($purchaseOrders->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No Product found',
                ], 200);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => $purchaseOrders
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
        
    }

    public function store(Request $request)
    {

        try {
            $data = $request->data;
            // Try to find the product by StockCode
            $checkproduct = Product::where('StockCode', $data['StockCode'])->first();
            if($checkproduct){
                return response()->json([
                    'success' => false,
                    'message' => 'Product already exists',
                ], 409);
            }

            Product::create($data);

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

            return response()->json([
                'success' => true,
                'message' => 'New Product created successfully',
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    // public function storeBulk(Request $request)
    // {   
    //     $allproducts = $request->json()->all();
    //     $inserted = 0;
    //     $notInserted = 0;
    //     try {
    //         foreach ($allproducts as $prodData) {
    //             unset($prodData['TimeStamp']);
                
    //             $InsertProd = Product::firstOrCreate(['StockCode' => $prodData['StockCode']],$prodData);
    //             if ($InsertProd->wasRecentlyCreated) {
    //                 $inserted++;
    //             } else {
    //                 $notInserted++;
    //             }
    //         }
    //         if($notInserted > 0){
    //             $retval = 2;
    //         }
    //         else{
    //             $retval = 1;
    //         }
    //         return response()->json([
    //             'success' => true,
    //             'status_response' => $retval,
    //             'message' => 'Products created successfully',
    //             'successful' => $inserted,
    //             'unsuccessful' => $notInserted,
    //             'totalFileLength' => count($allproducts)
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'status_response' => 0,
    //             'message' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    

    public function storeBulk(Request $request)
    {   
        $allProducts = $request->json()->all();
        $inserted = 0;
        $notInserted = 0;
        $batchSize = 100; 

        try {
            // Remove duplicate StockCodes in the payload
            $uniqueProducts = collect($allProducts)
                ->unique('StockCode')
                ->values()
                ->toArray();

            foreach (array_chunk($uniqueProducts, $batchSize) as $batch) {
                // Extract stock codes from the batch
                $stockCodes = array_column($batch, 'StockCode');

                // Fetch existing stock codes in one query
                $existingStockCodes = Product::whereIn('StockCode', $stockCodes)
                    ->pluck('StockCode')
                    ->toArray();

                $existingStockCodes = array_flip($existingStockCodes); // Faster lookup

                $productsToInsert = [];

                foreach ($batch as $product) {
                    if (isset($existingStockCodes[$product['StockCode']])) {
                        $notInserted++;
                        continue; // Skip duplicates
                    }

                    // Prepare product data for bulk insert
                    $productsToInsert[] = [
                        'StockCode'      => $product['StockCode'],
                        'Description'    => $product['Description'],
                        'LongDesc'       => $product['LongDesc'],
                        'AlternateKey1'  => $product['AlternateKey1'],
                        'StockUom'       => $product['StockUom'],
                        'AlternateUom'   => $product['AlternateUom'],
                        'ConvFactAltUom' => $product['ConvFactAltUom'],
                        'OtherUom'       => $product['OtherUom'],
                        'ConvFactOthUom' => $product['ConvFactOthUom'],
                        'Mass'           => $product['Mass'],
                        'Volume'         => $product['Volume'],
                        'ProductClass'   => $product['ProductClass'],
                        'WarehouseToUse' => $product['WarehouseToUse'],
                        'Brand'          => $product['Brand'],
                    ];
                }

                // Bulk Insert
                if (!empty($productsToInsert)) {
                    Product::insert($productsToInsert);
                    $inserted += count($productsToInsert);
                }
            }

            $retval = $notInserted > 0 ? 2 : 1;

            return response()->json([
                "success"          => true,
                "status_response"  => $retval,
                "message"          => "$inserted products successfully inserted.",
                "successful"       => $inserted,
                "unsuccessful"     => $notInserted,
                "totalFileLength"  => count($allProducts)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                "message" => "Database Error: " . $e->getMessage()
            ], 500);
        }
    }


    public function show(string $stockCode)
    {
        $response = array();
        try {
            $data = Product::where('stockCode', $stockCode)->select(
                'StockCode',
                'Description',
                'LongDesc',
                'AlternateKey1',
                'StockUom',
                'AlternateUom',
                'OtherUom',
                'ConvFactAltUom',
                'ConvFactOthUom',
                'Mass',
                'Volume',
                'ProductClass',
                'WarehouseToUse',
                'Brand',
            )->firstOrFail();

            $response = [
                'message' => 'Specific Product retrieved successfully',
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

    public function update(StoreProductData $request, string $stockCode)
    {
        try {
            $data = $request['data'];
            $found = Product::where('StockCode', $stockCode)->first();

            if (!$found) {
                $response = [
                    'response' => 'data not found',
                    'success' => false
                ];

                //break to reserve server resouces
                return response()->json($response);
            }

            // Store original data for logging
            $originalData = $this->sanitizeForJson($found->toArray());

            // Handle file upload
            // if ($request->hasFile('image_file')) {
            //     // Store the file
            //     $filePath = $request->file('image_file')->store('images', 'public');

            //     $data['uploaded_image'] = $filePath; // Store the file path in the data array
            // }
            
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
                        'product_code' => $stockCode,
                        'product_description' => $sanitizedData['Description'] ?? $originalData['Description'] ?? 'N/A',
                        'product_brand' => $sanitizedData['Brand'] ?? $originalData['Brand'] ?? 'N/A',
                        'action_type' => 'update',
                        'updated_fields' => $changedFields,
                        'subject_type' => 'App\\Models\\Product',
                        'subject_id' => $stockCode,
                        'event' => 'updated',
                        // Add old and attributes for change tracking (same format as salesman and customer)
                        'old' => $oldValues,
                        'attributes' => $newValues
                    ])
                    ->log("Updated product: " . ($sanitizedData['Description'] ?? $originalData['Description'] ?? 'N/A') . " (Code: " . $stockCode . ") - Brand: " . ($sanitizedData['Brand'] ?? $originalData['Brand'] ?? 'N/A'));
            } catch (\Exception $e) {
                // Simple fallback logging if detailed logging fails
                try {
                    activity('product_maintenance')
                        ->withProperties([
                            'action_type' => 'update',
                            'product_code' => $stockCode,
                            'event' => 'updated'
                        ])
                        ->log("Product updated (Code: {$stockCode})");
                } catch (\Exception $e2) {
                    // Last resort - basic log without properties
                    activity('product_maintenance')->log("Product update operation performed");
                }
            }

            return response()->json([
                'success' => true,
                'message' =>  "Product updated succesfully!",
            ]);

        } catch (\Exception $e) {

            $response = [
                'message' => $e->getMessage(),
                'success' => false
            ];
        }

        return response()->json($response);
    }

    public function destroy(Request $request, string $stockCode)
    {
        try {

            $data = Product::where('StockCode', $stockCode)->first();

            if (!$data) {
                $response = [
                    'message' => 'Product not found',
                    'success' => false
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
                        'product_code' => $stockCode,
                        'product_description' => $productData['Description'] ?? 'N/A',
                        'product_brand' => $productData['Brand'] ?? 'N/A',
                        'product_uom' => $productData['StockUom'] ?? 'N/A',
                        'action_type' => 'delete',
                        'deleted_data' => $productData,
                        'subject_type' => 'App\\Models\\Product',
                        'subject_id' => $stockCode,
                        'event' => 'deleted'
                    ])
                    ->log("Deleted product: " . ($productData['Description'] ?? 'N/A') . " (Code: " . $stockCode . ") - Brand: " . ($productData['Brand'] ?? 'N/A'));
            } catch (\Exception $e) {
                // Simple fallback logging if detailed logging fails
                try {
                    activity('product_maintenance')
                        ->withProperties([
                            'action_type' => 'delete',
                            'product_code' => $stockCode,
                            'event' => 'deleted'
                        ])
                        ->log("Product deleted (Code: {$stockCode})");
                } catch (\Exception $e2) {
                    // Last resort - basic log without properties
                    activity('product_maintenance')->log("Product deletion operation performed");
                }
            }

            $response = [
                'message' => 'Product deleted succesfully!',
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
