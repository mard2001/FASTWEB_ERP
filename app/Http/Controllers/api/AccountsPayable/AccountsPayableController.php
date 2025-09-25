<?php

namespace App\Http\Controllers\api\AccountsPayable;

use App\Http\Controllers\Controller;
use App\Models\AccountsPayable;
use App\Models\Payment;
use App\Models\Check;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
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

            // Check for available credit memos for this supplier
            $supplierCode = $data['supplier_code'];
            $totalAmount = $data['total_amount'];
            
            // Get total original credit memos
            $totalCreditMemos = AccountsPayable::where('supplier_code', trim($supplierCode))
                ->sum('CreditMemo') ?? 0;
            
            // Get total applied credit memos (from AUTO-CM- payment records)
            $appliedCreditMemos = Payment::whereHas('accountsPayable', function($query) use ($supplierCode) {
                $query->where('supplier_code', trim($supplierCode));
            })
            ->where('reference_number', 'LIKE', 'AUTO-CM-%')
            ->sum('payment_amount') ?? 0;
            
            // Available credit memo = Original credit memos - Applied credit memos
            $availableCreditMemo = $totalCreditMemos - $appliedCreditMemos;
            $availableCreditMemo = max(0, $availableCreditMemo); // Ensure non-negative

            // Calculate automatic credit memo application
            $creditMemoApplied = 0;
            if ($availableCreditMemo > 0) {
                if ($availableCreditMemo >= $totalAmount) {
                    // Credit memo covers the entire amount
                    $creditMemoApplied = $totalAmount;
                    $data['status'] = 'Paid';
                    $data['remarks'] = ($data['remarks'] ?? '') . " [Auto-paid using credit memo: ₱" . number_format($creditMemoApplied, 2) . "]";
                } else {
                    // Credit memo covers partial amount
                    $creditMemoApplied = $availableCreditMemo;
                    $data['remarks'] = ($data['remarks'] ?? '') . " [Partial payment from credit memo: ₱" . number_format($creditMemoApplied, 2) . "]";
                }
            }

            $accountsPayable = AccountsPayable::create($data);

            // Apply credit memo if available
            if ($creditMemoApplied > 0) {
                // Create automatic payment record for credit memo application
                Payment::create([
                    'accounts_payable_id' => $accountsPayable->id,
                    'payment_amount' => $creditMemoApplied,
                    'payment_type' => 'cash',
                    'payment_status' => ($creditMemoApplied >= $totalAmount) ? 'full' : 'partial',
                    'payment_date' => now(),
                    'reference_number' => 'AUTO-CM-' . $accountsPayable->id,
                    'remarks' => 'Automatic credit memo application',
                    'process_by' => $data['process_by']
                ]);

                // NOTE: We no longer deduct from original credit memo records to preserve them.
                // Credit memo applications are now tracked through Payment records with AUTO-CM- reference.
                // The available credit memo calculation will be updated to consider these payment applications.
            }

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
                'remarks' => 'nullable|string|max:500',
                'bank_id' => 'nullable|integer',
                'gcash_id' => 'nullable|integer',
                // Check-related fields
                'pay_by_check' => 'nullable|boolean',
                'check_payee' => 'nullable|string|max:255',
                'check_date' => 'nullable|date',
                'check_number' => 'nullable|string|max:50',
                'check_amount' => 'nullable|numeric|min:0.01',
                'check_amount_in_words' => 'nullable|string|max:500'
            ]);

            // Additional validation: bank_id is required when payment_type is 'bank'
            if ($request->payment_type === 'bank' && empty($request->bank_id)) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('bank_id', 'Bank selection is required for bank payments.');
                });
            }

            // Additional validation: gcash_id is required when payment_type is 'gcash'
            if ($request->payment_type === 'gcash' && empty($request->gcash_id)) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('gcash_id', 'GCash account selection is required for GCash payments.');
                });
            }

            // Additional validation: check fields are required when pay_by_check is true
            if ($request->pay_by_check) {
                if (empty($request->check_payee)) {
                    $validator->after(function ($validator) {
                        $validator->errors()->add('check_payee', 'Payee name is required for check payments.');
                    });
                }
                if (empty($request->check_date)) {
                    $validator->after(function ($validator) {
                        $validator->errors()->add('check_date', 'Check date is required for check payments.');
                    });
                }
                if (empty($request->check_amount_in_words)) {
                    $validator->after(function ($validator) {
                        $validator->errors()->add('check_amount_in_words', 'Amount in words is required for check payments.');
                    });
                }
            }

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Process payment amount - overpayments are now allowed
            $paymentAmount = $request->payment_amount;
            $currentBalance = $accountsPayable->balance_amount;

            // Calculate credit memo for overpayments
            $creditMemo = 0;
            $actualPaymentAmount = $paymentAmount;
            
            if ($paymentAmount > $currentBalance) {
                // Overpayment detected - calculate credit memo
                $creditMemo = $paymentAmount - $currentBalance;
                $actualPaymentAmount = $currentBalance; // Only pay up to the balance
            }

            // Determine payment status - overpayments are considered full payment
            $newBalance = $currentBalance - $actualPaymentAmount;
            $paymentStatus = ($newBalance <= 0.001) ? 'full' : 'partial'; // Using small tolerance for floating point comparison

            // Create payment record
            $paymentData = [
                'accounts_payable_id' => $accountsPayable->id,
                'payment_amount' => $actualPaymentAmount, // Use actual payment amount (not overpayment)
                'payment_type' => $request->payment_type, // cash, bank, gcash
                'payment_status' => $paymentStatus, // full, partial
                'payment_date' => now(),
                'reference_number' => $request->reference_number,
                'remarks' => $request->remarks,
                'process_by' => auth()->user()->name ?? 'System'
            ];

            // Add bank_id if payment type is bank and bank_id is provided
            if ($request->payment_type === 'bank' && $request->bank_id) {
                $paymentData['bank_id'] = $request->bank_id;
            }

            // Add gcash_id if payment type is gcash and gcash_id is provided
            if ($request->payment_type === 'gcash' && $request->gcash_id) {
                $paymentData['gcash_id'] = $request->gcash_id;
            }

            // Handle check payment if enabled
            $checkId = null;
            
            // Debug: Log the values to see what's being received
            Log::info('Check payment debug', [
                'pay_by_check' => $request->pay_by_check,
                'payment_type' => $request->payment_type,
                'check_payee' => $request->check_payee,
                'check_date' => $request->check_date
            ]);
            
            // Check if pay_by_check is truthy (could be '1', 1, true, 'true', etc.)
            if (($request->pay_by_check == '1' || $request->pay_by_check === true || $request->pay_by_check === 'true') && $request->payment_type === 'bank') {
                Log::info('Creating check record...');
                
                // Create check record
                $checkData = [
                    'BankID' => $request->bank_id,
                    'Payee' => $request->check_payee,
                    'AmountInWords' => $request->check_amount_in_words,
                    'CheckDate' => $request->check_date,
                    'CheckAmount' => $paymentAmount, // Use the full entered amount for check
                    'CheckNumber' => $request->check_number,
                    'Status' => 'Active',
                    'CreatedBy' => auth()->user()->name ?? 'System',
                    'Remarks' => 'Payment for AP #' . $accountsPayable->id . ($creditMemo > 0 ? ' (includes overpayment of ₱' . number_format($creditMemo, 2) . ')' : '')
                ];

                Log::info('Check data to create', $checkData);

                $check = Check::create($checkData);
                $checkId = $check->CheckID;

                Log::info('Check created with ID: ' . $checkId);

                // Add check_id to payment data
                $paymentData['check_id'] = $checkId;
            } else {
                Log::info('Check payment not enabled or not bank payment');
            }

            $payment = Payment::create($paymentData);

            // Calculate new balance after payment
            $totalPaid = $accountsPayable->payments()->sum('payment_amount');
            $newBalance = $accountsPayable->total_amount - $totalPaid;

            // Update status and credit memo
            if ($newBalance <= 0 || $paymentStatus === 'full') {
                $accountsPayable->status = 'Paid';
            } else {
                // Partial payment with remaining balance
                $accountsPayable->status = 'Partial';
            }
            
            // Update credit memo if there's overpayment
            if ($creditMemo > 0) {
                $accountsPayable->CreditMemo = ($accountsPayable->CreditMemo ?? 0) + $creditMemo;
            }
            
            $accountsPayable->save();

            // Reload relationships
            $accountsPayable->load('supplier', 'payments');

            // Prepare response message
            $message = 'Payment processed successfully';
            if ($creditMemo > 0) {
                $message = 'Payment processed successfully. Overpayment of ₱' . number_format($creditMemo, 2) . ' stored as Credit Memo.';
            }

            $responseData = [
                'success' => true,
                'message' => $message,
                'data' => $accountsPayable,
                'payment' => $payment,
                'new_balance' => $newBalance,
                'credit_memo' => $creditMemo,
                'total_payment_entered' => $paymentAmount,
                'actual_payment_amount' => $actualPaymentAmount
            ];

            // Include check information if check was created
            if ($checkId) {
                $responseData['check'] = Check::find($checkId);
                $responseData['message'] = 'Payment and check processed successfully';
            }

            return response()->json($responseData, 200);

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
            $suppliers = \App\Models\Supplier::select('SupplierCode', 'SupplierName', 'ContactPerson')
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
     * Get banks for payment dropdown
     */
    public function getBanks()
    {
        try {
            // Get all banks - since we learned that there are no 'Active' status banks,
            // we'll return all banks for now. This can be adjusted later if needed.
            $banks = \App\Models\Bank::select('BankID', 'BankName', 'AccountName', 'AccountNumber', 'CardNumber', 'ExpirationDate', 'CCV')
                                    ->orderBy('BankName')
                                    ->get();

            return response()->json([
                'success' => true,
                'message' => 'Banks retrieved successfully',
                'data' => $banks
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving banks: ' . $e->getMessage()
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