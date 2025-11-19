<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = Bank::orderBy('DateCreated', 'desc')->get();

            // Return success even if no data found, with empty array
            return response()->json([
                'success' => true,
                'message' => count($data) > 0 ? 'All bank details retrieved successfully' : 'No bank details found',
                'data' => $data
            ], 200);   

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bank data: ' . $e->getMessage(),
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
            
            // Validate the formatted input
            $accountNumber = $data['AccountNumber'];
            $cardNumber = $data['CardNumber'] ?? null;
            
            // Validate account number format (XXXX-XXXX-XXXX)
            if (!preg_match('/^\d{4}-\d{4}-\d{4}$/', $accountNumber)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account number must be in XXXX-XXXX-XXXX format',
                ], 400);
            }
            
            // Validate card number format if provided (XXXX-XXXX-XXXX-XXXX)
            if ($cardNumber && !preg_match('/^\d{4}-\d{4}-\d{4}-\d{4}$/', $cardNumber)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card number must be in XXXX-XXXX-XXXX-XXXX format',
                ], 400);
            }
            
            // Check if account number already exists
            $existingBank = Bank::where('AccountNumber', $accountNumber)->first();
            if ($existingBank) {
                return response()->json([
                    'success' => 409,
                    'message' => 'Account number already exists',
                ], 409);
            }
            
            $bank = Bank::create($data);
            
            activity('bank')
                ->performedOn($bank)
                ->causedBy($request->user())
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'bank_name' => $data['BankName'],
                    'account_name' => $data['AccountName'],
                    'account_number' => $data['AccountNumber'],
                    'status' => $data['Status'],
                    'attributes' => $data
                ])
                ->event('created')
                ->log("Created new bank '{$data['BankName']}' - Account: {$data['AccountName']} ({$data['AccountNumber']})");
            
            return response()->json([
                'success' => true,
                'message' => 'New bank created successfully',
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
            $bank = Bank::where('BankID', $id)->first();

            if (is_null($bank)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No bank details found',
                    'data' => []
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bank details retrieved successfully',
                'data' => $bank
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
            $found = Bank::where('BankID', $id)->first();

            if (!$found) {
                return response()->json([
                    'message' => 'Bank data not found',
                    'success' => false
                ]);
            }

            // Validate the formatted input
            $accountNumber = $data['AccountNumber'];
            $cardNumber = $data['CardNumber'] ?? null;
            
            // Validate account number format (XXXX-XXXX-XXXX)
            if (!preg_match('/^\d{4}-\d{4}-\d{4}$/', $accountNumber)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account number must be in XXXX-XXXX-XXXX format',
                ], 400);
            }
            
            // Validate card number format if provided (XXXX-XXXX-XXXX-XXXX)
            if ($cardNumber && !preg_match('/^\d{4}-\d{4}-\d{4}-\d{4}$/', $cardNumber)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card number must be in XXXX-XXXX-XXXX-XXXX format',
                ], 400);
            }

            // Check if account number already exists (excluding current record)
            $existingBank = Bank::where('AccountNumber', $data['AccountNumber'])
                               ->where('BankID', '!=', $id)
                               ->first();
            if ($existingBank) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account number already exists',
                ], 409);
            }

            // Store old data for logging
            $oldData = $found->toArray();

            $found->update($data);

            activity('bank')
                ->performedOn($found)
                ->causedBy($request->user())
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'bank_id' => $id,
                    'bank_name' => $data['BankName'],
                    'account_name' => $data['AccountName'],
                    'account_number' => $data['AccountNumber'],
                    'attributes' => $data,
                    'old' => $oldData
                ])
                ->event('updated')
                ->log("Updated bank '{$data['BankName']}' - Account: {$data['AccountName']} ({$data['AccountNumber']})");
                        $response = [
                'message' => 'Bank details updated successfully!',
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
            // Get bank data before deletion for logging
            $bank = Bank::where('BankID', $id)->first();
            
            if (!$bank) {
                $response = [
                    'message' => 'Bank data not found',
                    'success' => false
                ];

                return response()->json($response);
            }

            // Store bank data for logging
            $bankData = $bank->toArray();
            
            $data = Bank::where('BankID', $id)->delete();

            activity('bank')
                ->performedOn($bank)
                ->causedBy(request()->user())
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'bank_id' => $id,
                    'bank_name' => $bankData['BankName'],
                    'account_number' => $bankData['AccountNumber'],
                    'old' => $bankData
                ])
                ->event('deleted')
                ->log("Deleted bank '{$bankData['BankName']}' - Account: {$bankData['AccountNumber']}");

            $response = [
                'message' => 'Bank deleted successfully!',
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
