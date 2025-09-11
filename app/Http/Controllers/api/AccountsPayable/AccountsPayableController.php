<?php

namespace App\Http\Controllers\api\AccountsPayable;

use App\Http\Controllers\Controller;
use App\Models\AccountsPayable;
use App\Models\Payment;
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
            $query = AccountsPayable::with('supplier');

            // Filter by date range if provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->byDateRange($request->start_date, $request->end_date);
            }

            // Filter by supplier if provided
            if ($request->has('supplier') && $request->supplier != '') {
                $query->where(function($q) use ($request) {
                    $q->where('supplier_code', 'like', '%' . $request->supplier . '%')
                      ->orWhere('supplier_name', 'like', '%' . $request->supplier . '%');
                });
            }

            // Filter by status
            if ($request->has('status') && $request->status != '') {
                switch (strtolower($request->status)) {
                    case 'pending':
                        $query->pending();
                        break;
                    case 'paid':
                        $query->paid();
                        break;
                    case 'overdue':
                        $query->overdue();
                        break;
                    default:
                        $query->byStatus($request->status);
                        break;
                }
            }

            // Filter by RR number if provided
            if ($request->has('rr_number') && $request->rr_number != '') {
                $query->where('rr_number', 'like', '%' . $request->rr_number . '%');
            }

            $data = $query->orderBy('created_at', 'desc')
                          ->orderBy('date', 'desc')
                          ->orderBy('id', 'desc')
                          ->get();

            // Add computed fields
            $data->each(function ($item) {
                $item->formatted_total_amount = $item->formatted_total_amount;
                $item->formatted_payment_amount = $item->formatted_payment_amount;
                $item->formatted_balance_amount = $item->formatted_balance_amount;
                $item->balance_amount = $item->balance_amount;
                $item->is_overdue = $item->is_overdue;
                $item->due_date = $item->due_date;
                // Add sort timestamp for precise frontend sorting
                $item->sort_timestamp = $item->created_at ? $item->created_at->timestamp : 0;
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
                'date' => 'required|date',
                'supplier_code' => 'required|string|max:50',
                'supplier_name' => 'required|string|max:255',
                'rr_number' => 'required|string|max:50|unique:tblAccountsPayable,rr_number',
                'reference_number' => 'required|string|max:100',
                'total_amount' => 'required|numeric|min:0',
                'terms' => 'nullable|string|max:100',
                'status' => 'nullable|in:Pending,Paid',
                'remarks' => 'nullable|string|max:500',
                'process_by' => 'nullable|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $data['status'] = $data['status'] ?? 'Pending';
            $data['process_by'] = $data['process_by'] ?? auth()->user()->name ?? 'System';

            $accountsPayable = AccountsPayable::create($data);

            // Load the relationship
            $accountsPayable->load('supplier');

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
            $accountsPayable = AccountsPayable::with('supplier')->findOrFail($id);

            // Add computed attributes
            $accountsPayable->formatted_total_amount = $accountsPayable->formatted_total_amount;
            $accountsPayable->formatted_payment_amount = $accountsPayable->formatted_payment_amount;
            $accountsPayable->formatted_balance_amount = $accountsPayable->formatted_balance_amount;
            $accountsPayable->balance_amount = $accountsPayable->balance_amount;
            $accountsPayable->is_overdue = $accountsPayable->is_overdue;
            $accountsPayable->due_date = $accountsPayable->due_date;

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
                'date' => 'sometimes|required|date',
                'supplier_code' => 'sometimes|required|string|max:50',
                'supplier_name' => 'sometimes|required|string|max:255',
                'rr_number' => 'sometimes|required|string|max:50|unique:tblAccountsPayable,rr_number,' . $id,
                'reference_number' => 'sometimes|required|string|max:100',
                'total_amount' => 'sometimes|required|numeric|min:0',
                'terms' => 'nullable|string|max:100',
                'status' => 'nullable|in:Pending,Paid',
                'remarks' => 'nullable|string|max:500',
                'process_by' => 'nullable|string|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $accountsPayable->update($request->all());
            $accountsPayable->load('supplier');

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
            $query = AccountsPayable::query();

            // Filter by date range if provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->byDateRange($request->start_date, $request->end_date);
            }

            $totalPending = $query->clone()->pending()->sum('total_amount');
            $totalPaid = $query->clone()->paid()->sum('total_amount');
            $totalOverdue = $query->clone()->overdue()->sum('total_amount');
            $pendingBalance = $query->clone()->pending()->get()->sum('balance_amount');

            $summary = [
                'total_pending_amount' => $totalPending,
                'total_paid_amount' => $totalPaid,
                'total_overdue_amount' => $totalOverdue,
                'total_pending_balance' => $pendingBalance,
                'count_pending' => $query->clone()->pending()->count(),
                'count_paid' => $query->clone()->paid()->count(),
                'count_overdue' => $query->clone()->overdue()->count(),
                'count_total' => $query->clone()->count()
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
     * Process payment for an accounts payable record
     */
    public function processPayment(Request $request, $id)
    {
        try {
            $accountsPayable = AccountsPayable::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'payment_amount' => 'required|numeric|min:0.01',
                'payment_type' => 'required|in:cash,bank,gcash',
                'reference_number' => 'nullable|string|max:100',
                'remarks' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validate payment amount
            $paymentAmount = $request->payment_amount;
            $currentBalance = $accountsPayable->balance_amount;

            if ($paymentAmount > $currentBalance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount cannot exceed the balance amount of ₱' . number_format($currentBalance, 2)
                ], 422);
            }

            // Determine payment status based on amount
            $totalAmount = $accountsPayable->total_amount;
            $paymentStatus = ($paymentAmount >= $totalAmount) ? 'full' : 'partial';

            // Create payment record
            $payment = Payment::create([
                'accounts_payable_id' => $accountsPayable->id,
                'payment_amount' => $paymentAmount,
                'payment_type' => $request->payment_type, // cash, bank, gcash
                'payment_status' => $paymentStatus, // full, partial
                'payment_date' => now(),
                'reference_number' => $request->reference_number,
                'remarks' => $request->remarks,
                'process_by' => auth()->user()->name ?? 'System'
            ]);

            // Calculate new balance after payment
            $totalPaid = $accountsPayable->payments()->sum('payment_amount');
            $newBalance = $accountsPayable->total_amount - $totalPaid;

            // Update status based on payment
            if ($newBalance <= 0 || $paymentStatus === 'full') {
                $accountsPayable->status = 'Paid';
            } else {
                // Partial payment with remaining balance
                $accountsPayable->status = 'Partial';
            }
            // Don't update balance_amount as it's a computed column
            $accountsPayable->save();

            // Reload relationships
            $accountsPayable->load('supplier', 'payments');

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $accountsPayable,
                'payment' => $payment,
                'new_balance' => $newBalance
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment history for an accounts payable record
     */
    public function getPaymentHistory($id)
    {
        try {
            $accountsPayable = AccountsPayable::with(['payments' => function($query) {
                $query->orderBy('payment_date', 'desc');
            }, 'supplier'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'accounts_payable' => $accountsPayable,
                    'payments' => $accountsPayable->payments,
                    'total_paid' => $accountsPayable->total_paid_amount,
                    'balance' => $accountsPayable->balance_amount
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching payment history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get suppliers for dropdown
     */
    public function getSuppliers()
    {
        try {
            $suppliers = \App\Models\Supplier::select('SupplierCode', 'SupplierName')
                                            ->orderBy('SupplierName')
                                            ->get();

            return response()->json([
                'success' => true,
                'message' => 'Suppliers retrieved successfully',
                'data' => $suppliers
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving suppliers: ' . $e->getMessage()
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
            // Always print all accounts payable data with supplier relationship
            $data = AccountsPayable::with('supplier')->orderBy('date', 'desc')->get();
            
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