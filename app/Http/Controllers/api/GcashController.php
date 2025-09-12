<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Gcash;
use Illuminate\Http\Request;

class GcashController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = Gcash::orderBy('DateCreated', 'desc')->get();

            // Return success even if no data found, with empty array
            return response()->json([
                'success' => true,
                'message' => count($data) > 0 ? 'All GCash details retrieved successfully' : 'No GCash details found',
                'data' => $data
            ], 200);   

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving GCash data: ' . $e->getMessage(),
                'data' => []
            ], 500);   
        }
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
            
            // Validate account number format (11 digits starting with 09)
            $accountNumber = $data['AccountNumber'];
            if (!preg_match('/^09\d{9}$/', $accountNumber)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account number must be 11 digits starting with 09',
                ], 400);
            }
            
            // Check if account number already exists
            $existingGcash = Gcash::where('AccountNumber', $accountNumber)->first();
            if ($existingGcash) {
                return response()->json([
                    'success' => 409,
                    'message' => 'Account number already exists',
                ], 409);
            }
            
            Gcash::create($data);
            
            // Log the activity
            activity('gcash')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'account_name' => $data['AccountName'],
                    'account_number' => $data['AccountNumber'],
                    'status' => $data['Status'],
                    'subject_type' => 'App\\Models\\Gcash',
                    'event' => 'created',
                    'attributes' => $data
                ])
                ->log("Created new GCash account '{$data['AccountName']}' ({$data['AccountNumber']})");
            
            return response()->json([
                'success' => true,
                'message' => 'New GCash account created successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
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
            $gcash = Gcash::where('GcashID', $id)->first();

            if (is_null($gcash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No GCash details found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'GCash details retrieved successfully',
                'data' => $gcash
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request['data'];
            $found = Gcash::where('GcashID', $id)->first();

            if (!$found) {
                return response()->json([
                    'message' => 'GCash data not found',
                    'success' => false
                ]);
            }

            // Validate account number format (11 digits starting with 09)
            $accountNumber = $data['AccountNumber'];
            if (!preg_match('/^09\d{9}$/', $accountNumber)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account number must be 11 digits starting with 09',
                ], 400);
            }

            // Check if account number already exists (excluding current record)
            $existingGcash = Gcash::where('AccountNumber', $data['AccountNumber'])
                                  ->where('GcashID', '!=', $id)
                                  ->first();
            if ($existingGcash) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account number already exists',
                ], 409);
            }

            // Store old data for logging
            $oldData = $found->toArray();

            $found->update($data);

            // Log the activity
            activity('gcash')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'gcash_id' => $id,
                    'account_name' => $data['AccountName'],
                    'account_number' => $data['AccountNumber'],
                    'subject_type' => 'App\\Models\\Gcash',
                    'subject_id' => $id,
                    'event' => 'updated',
                    'attributes' => $data,
                    'old' => $oldData
                ])
                ->log("Updated GCash account '{$data['AccountName']}' ({$data['AccountNumber']})");

            $response = [
                'message' => 'GCash details updated successfully!',
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
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Get gcash data before deletion for logging
            $gcash = Gcash::where('GcashID', $id)->first();
            
            if (!$gcash) {
                $response = [
                    'message' => 'GCash data not found',
                    'success' => false
                ];

                return response()->json($response);
            }

            // Store gcash data for logging
            $gcashData = $gcash->toArray();
            
            $data = Gcash::where('GcashID', $id)->delete();

            // Log the activity
            activity('gcash')
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'gcash_id' => $id,
                    'account_name' => $gcashData['AccountName'],
                    'account_number' => $gcashData['AccountNumber'],
                    'subject_type' => 'App\\Models\\Gcash',
                    'subject_id' => $id,
                    'event' => 'deleted',
                    'old' => $gcashData
                ])
                ->log("Deleted GCash account '{$gcashData['AccountName']}' ({$gcashData['AccountNumber']})");

            $response = [
                'message' => 'GCash account deleted successfully!',
                'success' => true
            ];
        } catch (\Exception $e) {

            $response = [
                'message' => $e->getMessage(),
                'success' => false
            ];
        }

        return response()->json($response);
    }
}
