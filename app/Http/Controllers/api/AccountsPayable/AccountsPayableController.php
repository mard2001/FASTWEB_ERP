<?php

namespace App\Http\Controllers\api\AccountsPayable;

use App\Http\Controllers\Controller;
use App\Models\AccountsPayable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class AccountsPayableController extends Controller
{
    /**
     * Display a listing of accounts payable.
     */
    public function index(Request $request)
    {
        try {
            $query = AccountsPayable::query();

            // Filter by date range if provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->byDateRange($request->start_date, $request->end_date);
            }

            // Filter by branch if provided
            if ($request->has('branch') && $request->branch != '') {
                $query->where('branch', 'like', '%' . $request->branch . '%');
            }

            // Exclude or include total based on parameter
            if ($request->has('exclude_total') && $request->exclude_total == 'true') {
                $query->excludeTotal();
            }

            // Filter by status
            if ($request->has('status')) {
                switch ($request->status) {
                    case 'outstanding':
                        $query->outstanding();
                        break;
                    case 'settled':
                        $query->where('closing_balance', '=', 0);
                        break;
                    case 'credit':
                        $query->where('closing_balance', '<', 0);
                        break;
                }
            }

            $data = $query->orderBy('report_date', 'desc')
                          ->orderBy('branch', 'asc')
                          ->get();

            // Add computed fields
            $data->each(function ($item) {
                $item->status = $item->status;
                $item->formatted_closing_balance = $item->formatted_closing_balance;
                $item->formatted_opening_balance = $item->formatted_opening_balance;
                $item->formatted_invoices = $item->formatted_invoices;
            });

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No Accounts Payable data found',
                    'data' => []
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Accounts Payable data retrieved successfully',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving accounts payable data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created accounts payable record.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'branch' => 'required|string|max:100',
                'opening_balance' => 'nullable|numeric',
                'invoices' => 'nullable|numeric',
                'debit_notes' => 'nullable|numeric',
                'credit_notes' => 'nullable|numeric',
                'adjustments' => 'nullable|numeric',
                'disbursements' => 'nullable|numeric',
                'revaluation' => 'nullable|numeric',
                'tax_relief' => 'nullable|numeric',
                'withholding_tax' => 'nullable|numeric',
                'closing_balance' => 'nullable|numeric',
                'report_date' => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $accountsPayable = AccountsPayable::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Accounts Payable record created successfully',
                'data' => $accountsPayable
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating accounts payable record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified accounts payable record.
     */
    public function show($id)
    {
        try {
            $accountsPayable = AccountsPayable::findOrFail($id);

        // Add formatted attributes
        $accountsPayable->status = $accountsPayable->status;
        $accountsPayable->formatted_closing_balance = $accountsPayable->formatted_closing_balance;
        $accountsPayable->formatted_opening_balance = $accountsPayable->formatted_opening_balance;
        $accountsPayable->formatted_invoices = $accountsPayable->formatted_invoices;

        return response()->json([
            'success' => true,
            'message' => 'Accounts Payable record retrieved successfully',
            'data' => $accountsPayable
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Accounts Payable record not found'
            ], 404);
        }
    }

    /**
     * Update the specified accounts payable record.
     */
    public function update(Request $request, $id)
    {
        try {
            $accountsPayable = AccountsPayable::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'branch' => 'sometimes|required|string|max:100',
                'opening_balance' => 'nullable|numeric',
                'invoices' => 'nullable|numeric',
                'debit_notes' => 'nullable|numeric',
                'credit_notes' => 'nullable|numeric',
                'adjustments' => 'nullable|numeric',
                'disbursements' => 'nullable|numeric',
                'revaluation' => 'nullable|numeric',
                'tax_relief' => 'nullable|numeric',
                'withholding_tax' => 'nullable|numeric',
                'closing_balance' => 'nullable|numeric',
                'report_date' => 'sometimes|required|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $accountsPayable->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Accounts Payable record updated successfully',
                'data' => $accountsPayable
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating accounts payable record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified accounts payable record.
     */
    public function destroy($id)
    {
        try {
            $accountsPayable = AccountsPayable::findOrFail($id);
            $accountsPayable->delete();

            return response()->json([
                'success' => true,
                'message' => 'Accounts Payable record deleted successfully'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting accounts payable record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get summary statistics for accounts payable.
     */
    public function summary(Request $request)
    {
        try {
            $query = AccountsPayable::excludeTotal();

            // Filter by date range if provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->byDateRange($request->start_date, $request->end_date);
            }

            $summary = [
                'total_outstanding' => $query->clone()->where('closing_balance', '>', 0)->sum('closing_balance'),
                'total_credit_balance' => abs($query->clone()->where('closing_balance', '<', 0)->sum('closing_balance')),
                'total_invoices' => $query->clone()->sum('invoices'),
                'total_disbursements' => abs($query->clone()->sum('disbursements')),
                'count_outstanding' => $query->clone()->where('closing_balance', '>', 0)->count(),
                'count_settled' => $query->clone()->where('closing_balance', '=', 0)->count(),
                'count_credit' => $query->clone()->where('closing_balance', '<', 0)->count()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Summary retrieved successfully',
                'data' => $summary
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set AP data in cache for printing all data
     */
    public function setAPNum(Request $request) {
        // Always print all data
        \Illuminate\Support\Facades\Cache::put('APPrintAll', true, now()->addMinutes(1));
        
        // Store current user info for printing if authenticated
        if (auth()->check()) {
            \Illuminate\Support\Facades\Cache::put('print_user', auth()->user(), now()->addMinutes(1));
        }

        return response()->json([
            'success' => true,
            'printAll' => true,
            'originalData' => 'all'
        ]);
    }

    /**
     * Display the print page for all accounts payable data
     */
    public function printPage()
    {
        try{
            // Always print all accounts payable data
            $data = AccountsPayable::all();
            
            // Get user from session or cache if available
            $user = null;
            if (auth()->check()) {
                $user = auth()->user();
            } else {
                // Try to get user info from cache if set during the print setup
                $user = \Illuminate\Support\Facades\Cache::get('print_user');
            }
            
            // Log printing activity for all data
            try {
                if ($user) {
                    activity('accounts_payable')
                        ->causedBy($user)
                        ->withProperties([
                            'ip' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'url' => request()->fullUrl(),
                            'method' => request()->method(),
                            'total_records' => $data->count(),
                        ])
                        ->event('printed_all')
                        ->log("Printed All Accounts Payable Reports ({$data->count()} records)");
                }
            } catch (\Throwable $e) {
                // Non-blocking: ignore logging failures
            }
            
            return view('Pages.Printing.AP_printing', [
                'reports' => $data, 
                'printAll' => true,
                'user' => $user
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}