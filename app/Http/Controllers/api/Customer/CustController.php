<?php

namespace App\Http\Controllers\api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use Illuminate\Http\Request;

class CustController extends Controller
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
            $sanitized = strip_tags($sanitized);
            $sanitized = htmlspecialchars($sanitized, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            return $sanitized ?: (string)$data; // Fallback to original if all cleaning fails
        }
        return $data;
    }
    
    /**
     * Safely encode data to JSON, removing problematic fields if needed
     */
    private function safeJsonEncode($data)
    {
        $sanitized = $this->sanitizeForJson($data);
        
        // Test if the data can be JSON encoded
        $json = json_encode($sanitized);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $sanitized;
        }
        
        // If still has an error, return only safe basic data
        if (is_array($sanitized)) {
            return [
                'status' => 'data_sanitized',
                'keys' => array_keys($sanitized),
                'count' => count($sanitized)
            ];
        }
        
        return ['status' => 'data_sanitized', 'type' => gettype($sanitized)];
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = Customer::select('Customer', 'Name', 'ShortName', 'Salesperson', 'PriceCode', 'CustomerClass', 'Telephone', 'Contact', 'SoldToAddr1', 'SoldToAddr2', 'SoldToAddr3', 'SoldToGpsLat', 'SoldToGpsLong')
                ->with('salesman')->get();
            
            if (count($data) == 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'No Customer Data found',
                ], 200);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Customers Data retrieved successfully',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
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
        try {
            $data = $request->data;
            
            // Sanitize string data for UTF-8 encoding
            $sanitizedData = $this->sanitizeForJson($data);
            
            $customer = Customer::create($sanitizedData);
            
            // Log the activity with error handling
            try {
                activity('customer_maintenance')
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => $request->header('User-Agent'),
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'customer_code' => $sanitizedData['Customer'] ?? 'N/A',
                        'customer_name' => $sanitizedData['Name'] ?? 'N/A',
                        'contact_person' => $sanitizedData['Contact'] ?? 'N/A',
                        'salesperson' => $sanitizedData['Salesperson'] ?? 'N/A',
                        'action_type' => 'create',
                        'customer_data' => $sanitizedData,
                        'subject_type' => 'App\\Models\\Customer\\Customer',
                        'subject_id' => $sanitizedData['Customer'] ?? 'N/A',
                        'event' => 'created'
                    ])
                    ->log("Created new customer: " . ($sanitizedData['Name'] ?? 'N/A') . " with contact: " . ($sanitizedData['Contact'] ?? 'N/A') . ", assigned to salesperson: " . ($sanitizedData['Salesperson'] ?? 'N/A'));
            } catch (\Exception $e) {
                // Log a simple version if the detailed logging fails
                activity('customer_maintenance')->log("Customer created: " . ($sanitizedData['Name'] ?? 'N/A'));
            }
            
            return response()->json([
                'success' => true,
                'message' => 'New Customer created successfully',
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function storeBulk(Request $request)
    {   
        $allCustomers = $request->json()->all();
        $inserted = 0;
        $notInserted = 0;
        try {
            foreach ($allCustomers as $customerData) {
                
                $InsertCust = Customer::firstOrCreate(['custCode' => $customerData['custCode']],$customerData);
                if ($InsertCust->wasRecentlyCreated) {
                    $inserted++;
                } else {
                    $notInserted++;
                }
            }
            if($notInserted > 0){
                $retval = 2;
            }
            else{
                $retval = 1;
            }
            return response()->json([
                'success' => true,
                'status_response' => $retval,
                'message' => 'Customers created successfully',
                'successful' => $inserted,
                'unsuccessful' => $notInserted,
                'totalFileLength' => count($allCustomers)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status_response' => 0,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = Customer::select('Customer', 'Name', 'ShortName', 'Salesperson', 'PriceCode', 'CustomerClass', 'Telephone', 'Contact', 'SoldToAddr1', 'SoldToAddr2', 'SoldToAddr3')
                ->with('salesman')->where('Customer', $id)->first();

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found',
                ], 404);
            }
            return response()->json([
                'message' => 'Customer Details retrieved successfully',
                'data' => $data,
                'success' => true,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $custID)
    {
        try {
            $data = $request['data'];
            
            // Sanitize input data before processing
            $data = $this->sanitizeForJson($data);
            
            $found = Customer::where('Customer', $custID)->first();

            if (!$found) {
                $response = [
                    'response' => 'data not found',
                    'success' => false
                ];

                //break to reserve server resouces
                return response()->json($response);
            }
            
            // Store original data for logging (sanitized)
            $originalData = $this->sanitizeForJson($found->toArray());
            
            // dd($data, $found);
            $found->update($data);
            
            // Refresh the model to get updated data and sanitize it
            $found->refresh();
            $updatedCustomer = $this->sanitizeForJson($found->toArray());
            
            // Re-enable activity logging now that UTF-8 issues are resolved
            $enableActivityLogging = true; // Set to true to enable logging
            
            if ($enableActivityLogging) {
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
                
                // Log the activity with old and new values for change tracking
                try {
                    activity('customer_maintenance')
                        ->withProperties([
                            'ip' => $request->ip(),
                            'user_agent' => substr($request->header('User-Agent', ''), 0, 100), // Limit length
                            'url' => $request->fullUrl(),
                            'method' => $request->method(),
                            'customer_code' => $this->sanitizeForJson($custID),
                            'customer_name' => $this->sanitizeForJson($data['Name'] ?? $originalData['Name'] ?? 'N/A'),
                            'contact_person' => $this->sanitizeForJson($data['Contact'] ?? $originalData['Contact'] ?? 'N/A'),
                            'salesperson' => $this->sanitizeForJson($data['Salesperson'] ?? $originalData['Salesperson'] ?? 'N/A'),
                            'action_type' => 'update',
                            'updated_fields' => $changedFields,
                            'subject_type' => 'App\\Models\\Customer\\Customer',
                            'subject_id' => $custID,
                            'event' => 'updated',
                            // Add old and attributes for change tracking (same format as salesman)
                            'old' => $oldValues,
                            'attributes' => $newValues
                        ])
                        ->log("Updated customer: " . $this->sanitizeForJson($data['Name'] ?? $originalData['Name'] ?? 'N/A'));
                } catch (\Exception $e) {
                    // Even simpler fallback logging
                    try {
                        activity('customer_maintenance')
                            ->withProperties([
                                'action_type' => 'update',
                                'customer_code' => $custID,
                                'event' => 'updated'
                            ])
                            ->log("Customer updated (Code: {$custID})");
                    } catch (\Exception $e2) {
                        // Last resort - basic log without properties
                        activity('customer_maintenance')->log("Customer update operation performed");
                    }
                }
            }
                
            // Create response with sanitized data
            $responseData = [
                'success' => true,
                'message' => "Customer updated successfully!",
                "data" => $updatedCustomer  // Use sanitized data
            ];
            
            // Test if response can be JSON encoded before returning
            $jsonTest = json_encode($responseData);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // If there's still a JSON error, return minimal response
                return response()->json([
                    'success' => true,
                    'message' => "Customer updated successfully!",
                    'data' => [
                        'Customer' => $this->sanitizeForJson($custID),
                        'Name' => $this->sanitizeForJson($data['Name'] ?? 'N/A'),
                        'status' => 'updated_with_sanitized_data'
                    ]
                ]);
            }
            
            return response()->json($responseData);

        } catch (\Exception $e) {

            $response = [
                'message' => $this->sanitizeForJson($e->getMessage()),
                'success' => false
            ];
            
            // Ensure error response can be JSON encoded
            $jsonTest = json_encode($response);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $response = [
                    'message' => 'An error occurred while updating customer',
                    'success' => false
                ];
            }
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            
            $data = Customer::where('Customer', $id)->first();
            // echo $id;

            if (!$data) {
                $response = [
                    'message' => 'Customer not found',
                    'success' => false
                ];

                //break to reserve server resouces
                return response()->json($response);
            }

            // Store customer data for logging before deletion
            $customerData = $data->toArray();
            
            // Sanitize data for JSON encoding
            $sanitizedCustomerData = $this->sanitizeForJson($customerData);
            
            $data->delete();

            // Log the activity with error handling
            try {
                activity('customer_maintenance')
                    ->withProperties([
                        'ip' => request()->ip(),
                        'user_agent' => request()->header('User-Agent'),
                        'url' => request()->fullUrl(),
                        'method' => request()->method(),
                        'customer_code' => $sanitizedCustomerData['Customer'],
                        'customer_name' => $sanitizedCustomerData['Name'],
                        'contact_person' => $sanitizedCustomerData['Contact'] ?? 'N/A',
                        'salesperson' => $sanitizedCustomerData['Salesperson'] ?? 'N/A',
                        'action_type' => 'delete',
                        'deleted_data' => $sanitizedCustomerData,
                        'subject_type' => 'App\\Models\\Customer\\Customer',
                        'subject_id' => $sanitizedCustomerData['Customer'],
                        'event' => 'deleted'
                    ])
                    ->log("Deleted customer: " . $sanitizedCustomerData['Name'] . ", Contact: " . ($sanitizedCustomerData['Contact'] ?? 'N/A') . ", Salesperson: " . ($sanitizedCustomerData['Salesperson'] ?? 'N/A'));
            } catch (\Exception $e) {
                // Log a simple version if the detailed logging fails
                activity('customer_maintenance')->log("Customer deleted: " . $sanitizedCustomerData['Name']);
            }

            $response = [
                'message' => 'Customer deleted successfully!',
                'success' => true,
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
