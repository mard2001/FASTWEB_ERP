<?php

namespace App\Http\Controllers\api\MasterData;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\SupplierCredit;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\SupplierRequest;
use Illuminate\Validation\ValidationException;

class SupplierController
{
    public function index()
    {
        try {
            $data = Supplier::leftJoin('tblSupplierCredits', 'tblSupplier.SupplierCode', '=', 'tblSupplierCredits.supplier_code')
                           ->select(
                               'tblSupplier.*',
                               'tblSupplierCredits.credit_limit',
                               'tblSupplierCredits.credit_balance',
                               'tblSupplierCredits.total_credit',
                               'tblSupplierCredits.total_paid',
                               'tblSupplierCredits.balance'
                           )
                           ->orderBy('tblSupplier.lastUpdated', 'desc')
                           ->orderBy('tblSupplier.SupplierCode', 'desc')
                           ->get();

            if (count($data) == 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'No Supplier details found',
                    'data' => []
                ], 200);   
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
    public function store(SupplierRequest $request)
    {
        try {
            $data = $request->data;
            // Add current timestamp to lastUpdated field with Asia/Manila timezone
            $data['lastUpdated'] = now()->setTimezone('Asia/Manila');
            
            $res = Supplier::create($data);

            // Log the activity
            activity('supplier')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'supplier_code' => $data['SupplierCode'],
                    'supplier_name' => $data['SupplierName'],
                    'supplier_type' => $data['SupplierType'],
                    'contact_person' => $data['ContactPerson'],
                    'region' => $data['Region'],
                    'province' => $data['Province'],
                    'municipality' => $data['Municipality'],
                    'subject_type' => 'App\\Models\\Supplier',
                    'subject_id' => $data['SupplierCode'],
                    'event' => 'created',
                    'attributes' => $data
                ])
                ->log("Created new supplier '{$data['SupplierName']}' with code '{$data['SupplierCode']}'");

            return response()->json([
                'message' => 'Supplier inserted successfully!',
                'success' => true,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors()
            ], 422);  // HTTP 422 Unprocessable Entity
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
    public function update(SupplierRequest $request, string $supplierCode)
    {
        try {
            $data = $request['data'];
            // Add current timestamp to lastUpdated field with Asia/Manila timezone
            $data['lastUpdated'] = now()->setTimezone('Asia/Manila');
            
            $found = Supplier::where('SupplierCode', $supplierCode)->first();
            
            if (!$found) {
                return response()->json([
                    'message' => 'Supplier not found',
                    'success' => false
                ], 404);
            }

            // Store old data for logging
            $oldData = $found->toArray();
            
            $found->update($data);

            // Log the activity
            activity('supplier')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'supplier_code' => $supplierCode,
                    'supplier_name' => $data['SupplierName'],
                    'subject_type' => 'App\\Models\\Supplier',
                    'subject_id' => $supplierCode,
                    'event' => 'updated',
                    'attributes' => $data,
                    'old' => $oldData
                ])
                ->log("Updated supplier '{$data['SupplierName']}' (Code: {$supplierCode})");

            return response()->json([
                'message' => 'Supplier details updated successfully!',
                'success' => true,
                'data' => $found
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->validator->errors()->first(),
                'errors' => $e->validator->errors()
            ], 422);  // HTTP 422 Unprocessable Entity
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'success' => false
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            // Get supplier data before deletion for logging
            $supplier = Supplier::where('SupplierCode', $id)->first();
            
            if (!$supplier) {
                $response = [
                    'message' => 'Supplier data not found',
                    'success' => false
                ];

                //break to reserve server resouces
                return response()->json($response);
            }

            // Store supplier data for logging
            $supplierData = $supplier->toArray();
            
            $data = Supplier::where('SupplierCode', $id)->delete();

            // Log the activity
            activity('supplier')
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'supplier_code' => $id,
                    'supplier_name' => $supplierData['SupplierName'],
                    'subject_type' => 'App\\Models\\Supplier',
                    'subject_id' => $id,
                    'event' => 'deleted',
                    'old' => $supplierData
                ])
                ->log("Deleted supplier '{$supplierData['SupplierName']}' (Code: {$id})");

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
