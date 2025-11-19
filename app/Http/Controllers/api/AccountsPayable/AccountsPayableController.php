<?php

namespace App\Http\Controllers\api\AccountsPayable;

use App\Http\Controllers\Controller;
use App\Models\AccountsPayable;
use App\Models\Payment;
use App\Models\Check;
use App\Models\SupplierCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class AccountsPayableController extends Controller
{
    /**
     * Display a listing of accounts payable.
     */
    public function index(Request $request)
    {
        try {
            // Aggressive pagination for performance
            $perPage = $request->get('per_page', 25); // Reduce to 25 records
            $page = $request->get('page', 1);
            
            // Use raw SQL for better performance - avoid Eloquent overhead
            $query = DB::table('tblAccountsPayable as ap')
                ->leftJoin('tblSupplier as s', 'ap.supplier_code', '=', 's.SupplierCode')
                ->leftJoin(DB::raw('(
                    SELECT accounts_payable_id, SUM(payment_amount) as total_paid 
                    FROM tblPayments 
                    GROUP BY accounts_payable_id
                ) as payments'), 'ap.id', '=', 'payments.accounts_payable_id')
                ->select([
                    'ap.id',
                    'ap.date',
                    'ap.supplier_code',
                    'ap.supplier_name',
                    'ap.rr_number',
                    'ap.reference_number',
                    'ap.total_amount',
                    'ap.terms',
                    'ap.status',
                    'ap.CreditMemo',
                    'ap.process_by',
                    'ap.created_at',
                    's.SupplierName',
                    DB::raw('ISNULL(payments.total_paid, 0) as total_paid')
                ]);

            // Apply filters efficiently
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('ap.date', [$request->start_date, $request->end_date]);
            }

            if ($request->has('supplier') && $request->supplier != '') {
                $query->where(function($q) use ($request) {
                    $q->where('ap.supplier_code', 'like', '%' . $request->supplier . '%')
                      ->orWhere('ap.supplier_name', 'like', '%' . $request->supplier . '%');
                });
            }

            if ($request->has('status') && $request->status != '') {
                $query->where('ap.status', $request->status);
            }

            if ($request->has('rr_number') && $request->rr_number != '') {
                $query->where('ap.rr_number', 'like', '%' . $request->rr_number . '%');
            }

            // Get paginated results
            $offset = ($page - 1) * $perPage;
            $totalCount = $query->count();
            
            $results = $query->orderBy('ap.date', 'desc')
                           ->orderBy('ap.id', 'desc')
                           ->offset($offset)
                           ->limit($perPage)
                           ->get();

            // Process results efficiently
            $data = $results->map(function ($item) {
                $totalPaid = floatval($item->total_paid ?? 0);
                $totalCreditMemo = floatval($item->CreditMemo ?? 0);
                // Calculate balance without subtracting CreditMemo to properly detect overpayments
                $balanceAmount = floatval($item->total_amount) - $totalPaid;
                
                // Calculate overdue status
                $isOverdue = false;
                if ($item->status !== 'Paid') {
                    preg_match('/(\d+)/', $item->terms ?? '30', $matches);
                    $termDays = isset($matches[1]) ? (int)$matches[1] : 30;
                    $dueDate = \Carbon\Carbon::parse($item->date)->addDays($termDays);
                    $isOverdue = now()->gt($dueDate);
                }
                
                return [
                    'id' => $item->id,
                    'date' => $item->date,
                    'supplier_code' => $item->supplier_code,
                    'supplier_name' => $item->supplier_name ?: $item->SupplierName,
                    'rr_number' => $item->rr_number,
                    'reference_number' => $item->reference_number,
                    'total_amount' => floatval($item->total_amount),
                    'terms' => $item->terms,
                    'status' => $item->status,
                    'balance_amount' => $balanceAmount,
                    'CreditMemo' => floatval($item->CreditMemo ?? 0),
                    'process_by' => $item->process_by,
                    'is_overdue' => $isOverdue,
                    'sort_timestamp' => strtotime($item->created_at)
                ];
            });

            // Note: Auto credit memo logic removed from here to prevent running on every page load

            // Calculate pagination info
            $totalPages = ceil($totalCount / $perPage);
            $currentPage = $page;

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No Accounts Payable data found',
                    'data' => [],
                    'pagination' => [
                        'current_page' => $currentPage,
                        'total_pages' => $totalPages,
                        'per_page' => $perPage,
                        'total_records' => $totalCount
                    ]
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Accounts Payable data retrieved successfully',
                'data' => $data,
                'pagination' => [
                    'current_page' => $currentPage,
                    'total_pages' => $totalPages,
                    'per_page' => $perPage,
                    'total_records' => $totalCount,
                    'has_more_pages' => $currentPage < $totalPages
                ]
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
            
            // Get total applied credit memos (from CreditMemoApplication table)
            $appliedCreditMemos = \App\Models\CreditMemoApplication::whereHas('sourceAccountsPayable', function($query) use ($supplierCode) {
                $query->where('supplier_code', trim($supplierCode));
            })
            ->sum('credit_amount') ?? 0;
            
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
                'payment_amount' => $paymentAmount, // Use full payment amount (including overpayment)
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

            try {
                activity('accounts_payable')
                    ->performedOn($accountsPayable)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'url' => request()->fullUrl(),
                        'method' => request()->method(),
                        'ap_id' => $accountsPayable->id,
                        'ap_reference' => $accountsPayable->reference_number,
                        'rr_number' => $accountsPayable->rr_number,
                        'supplier_code' => $accountsPayable->supplier_code,
                        'supplier_name' => $accountsPayable->supplier_name,
                        'payment_id' => $payment->id,
                        'payment_amount' => $paymentAmount,
                        'actual_payment_amount' => $actualPaymentAmount,
                        'payment_type' => $request->payment_type,
                        'payment_status' => $paymentStatus,
                        'reference_number' => $request->reference_number,
                        'remarks' => $request->remarks,
                        'bank_id' => $paymentData['bank_id'] ?? null,
                        'gcash_id' => $paymentData['gcash_id'] ?? null,
                        'check_id' => $checkId,
                        'credit_memo_generated' => $creditMemo
                    ])
                    ->event('payment_made')
                    ->log('Payment of ₱' . number_format($paymentAmount, 2) . ' recorded for AP #' . $accountsPayable->id . ' (' . ($accountsPayable->reference_number ?? 'N/A') . ')');
            } catch (\Throwable $e) {
            }

            // Log to bank reconciliation when payment is via bank or bank check
            try {
                $affectedBankId = $paymentData['bank_id'] ?? null;
                if (!$affectedBankId && $checkId) {
                    $check = Check::find($checkId);
                    $affectedBankId = $check ? $check->BankID : null;
                }

                if ($affectedBankId) {
                    $bank = \App\Models\Bank::find($affectedBankId);
                    $bankName = $bank ? $bank->BankName : 'Unknown Bank';
                    $paymentTypeDisplay = ($request->payment_type === 'bank' && $checkId) ? 'Bank Check' : 'Bank';
                    $reconciliation = \App\Models\BankReconciliation::where('BankID', $affectedBankId)
                        ->orderBy('DateCreated', 'desc')
                        ->first();

                    activity('bank_reconciliation')
                        ->performedOn($reconciliation ?: $bank)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'ip' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                            'url' => request()->fullUrl(),
                            'method' => request()->method(),
                            'ap_id' => $accountsPayable->id,
                            'ap_reference' => $accountsPayable->reference_number,
                            'supplier_code' => $accountsPayable->supplier_code,
                            'supplier_name' => $accountsPayable->supplier_name,
                            'payment_id' => $payment->id,
                            'payment_amount' => $paymentAmount,
                            'payment_type' => $paymentTypeDisplay,
                            'bank_id' => $affectedBankId,
                            'check_id' => $checkId,
                        ])
                        ->event('AP Withdrawal')
                        ->log('Withdrawal of ₱' . number_format($paymentAmount, 2) . ' via ' . $paymentTypeDisplay . ' for AP #' . $accountsPayable->id . ' on \'' . $bankName . '\'');
                }
            } catch (\Throwable $e) {
            }

            // Calculate new balance after payment
            // For balance calculation, we need to account for the fact that overpayments 
            // are stored as credit memos, not applied to the balance
            $totalPaid = $accountsPayable->payments()->sum('payment_amount');
            $totalCreditMemo = $accountsPayable->CreditMemo ?? 0;
            
            // If this payment created a credit memo, add it to the total
            if ($creditMemo > 0) {
                $totalCreditMemo += $creditMemo;
            }
            
            // Adjust total paid by subtracting credit memos to get actual amount applied to balance
            $actualTotalPaid = $totalPaid - $totalCreditMemo;
            $newBalance = $accountsPayable->total_amount - $actualTotalPaid;

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

            // Create SupplierRunningBalance entry for this payment
            try {
                // The amount should be negative for payments (reducing the debt)
                \App\Models\SupplierRunningBalance::addEntry(
                    $accountsPayable->supplier_code,
                    now(),
                    $accountsPayable->id,
                    'payment',
                    -$actualPaymentAmount, // Negative because payment reduces debt
                    'Payment processed via ' . ucfirst($request->payment_type) . 
                    ($creditMemo > 0 ? ' (includes overpayment of ₱' . number_format($creditMemo, 2) . ')' : '')
                );

                // If credit memo was generated, create a CreditMemoApplication entry
                if ($creditMemo > 0) {
                    // Create entry in tblSupplierRunningBalance for credit memo generated
                    \App\Models\SupplierRunningBalance::addEntry(
                        $accountsPayable->supplier_code,
                        now(),
                        $accountsPayable->id,
                        'credit_memo_generated',
                        0, // Credit memo doesn't affect running balance directly
                        'Credit memo of ₱' . number_format($creditMemo, 2) . ' generated from overpayment'
                    );
                }
            } catch (Exception $e) {
                // Log error but don't fail the payment
                Log::error('Error creating SupplierRunningBalance entry: ' . $e->getMessage());
            }

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

            // Apply auto credit memos after successful payment processing
            // This will check if there are any available credits for this supplier
            // and apply them to outstanding invoices
            try {
                // Only apply auto credit memos if this payment generated a credit memo
                // This prevents unnecessary processing for regular payments
                if ($creditMemo > 0) {
                    Log::info('Payment generated credit memo, applying auto credit memos for supplier', [
                        'supplier_code' => $accountsPayable->supplier_code,
                        'credit_memo_generated' => $creditMemo
                    ]);
                    $this->applyAutoCreditMemosForSupplier($accountsPayable->supplier_code);
                } else {
                    Log::info('No credit memo generated from payment, skipping auto application', [
                        'supplier_code' => $accountsPayable->supplier_code,
                        'payment_amount' => $paymentAmount,
                        'balance_amount' => $currentBalance
                    ]);
                }
            } catch (Exception $e) {
                // Log the error but don't fail the payment
                Log::error('Error applying auto credit memos after payment: ' . $e->getMessage());
            }

            // Supplier credit will be updated via AccountsPayableObserver on model events

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

    /**
     * Apply auto credit memos using sequential invoice finding logic
     */
    private function applyAutoCreditMemosToAP($data)
    {
        // Convert to array for easier manipulation
        $dataArray = $data->toArray();
        
        Log::info('Auto Credit Memo Debug - Starting new sequential process', [
            'total_records' => count($dataArray)
        ]);
        
        // Group data by supplier
        $supplierGroups = collect($dataArray)->groupBy('supplier_code');
        
        foreach ($supplierGroups as $supplierCode => $records) {
            Log::info('Processing supplier with new logic', [
                'supplier_code' => $supplierCode,
                'record_count' => count($records)
            ]);
            
            // Sort records by date and ID ascending (oldest first) for proper sequential processing
            // This ensures proper chronological order even when dates are the same
            $sortedRecords = $records->sortBy([
                ['date', 'asc'],
                ['id', 'asc']
            ])->values();
            
            // Debug: Log the sorted order to verify correct sequence
            Log::info('Sorted records order for supplier ' . $supplierCode, [
                'records' => $sortedRecords->map(function($record, $index) {
                    return [
                        'index' => $index,
                        'id' => $record['id'],
                        'reference' => $record['reference_number'],
                        'date' => $record['date'],
                        'credit_memo' => $record['CreditMemo'] ?? 0
                    ];
                })->toArray()
            ]);
            
            // First pass: Identify invoices with credit memos
            $creditMemoSources = [];
            foreach ($sortedRecords as $index => $record) {
                $creditMemoAmount = floatval($record['CreditMemo'] ?? 0);
                
                if ($creditMemoAmount > 0) {
                    // Check how much of this credit memo has already been used
                    // Use CreditMemoApplication table to get accurate usage tracking
                    $usedAmount = \App\Models\CreditMemoApplication::where('source_ap_id', $record['id'])
                        ->sum('credit_amount') ?? 0;
                    
                    $availableAmount = $creditMemoAmount - $usedAmount;
                    
                    if ($availableAmount > 0.01) {
                        $creditMemoSources[] = [
                            'source_index' => $index,
                            'source_id' => $record['id'],
                            'source_reference' => $record['reference_number'],
                            'available_amount' => $availableAmount,
                            'original_amount' => $creditMemoAmount
                        ];
                        
                        Log::info('Found credit memo source', [
                            'source_reference' => $record['reference_number'],
                            'original_amount' => $creditMemoAmount,
                            'used_amount' => $usedAmount,
                            'available_amount' => $availableAmount
                        ]);
                    } else {
                        Log::info('Credit memo fully used, skipping', [
                            'source_reference' => $record['reference_number'],
                            'original_amount' => $creditMemoAmount,
                            'used_amount' => $usedAmount,
                            'available_amount' => $availableAmount
                        ]);
                    }
                }
            }
            
            // Second pass: Apply credit memos sequentially
            foreach ($creditMemoSources as $creditSource) {
                $remainingCredit = $creditSource['available_amount'];
                $sourceIndex = $creditSource['source_index'];
                
                Log::info('Applying credit memo sequentially', [
                    'source_reference' => $creditSource['source_reference'],
                    'remaining_credit' => $remainingCredit,
                    'source_index' => $sourceIndex
                ]);
                
                // Start searching from the next invoice after the credit memo source
                $searchStartIndex = $sourceIndex + 1;
                $appliedToAnyInvoice = false;
                
                // Search forward first (from source+1 to end)
                for ($i = $searchStartIndex; $i < count($sortedRecords) && $remainingCredit > 0.01; $i++) {
                    $remainingCredit = $this->applyCreditToInvoice($sortedRecords[$i], $creditSource, $remainingCredit, $dataArray);
                    if ($remainingCredit < $creditSource['available_amount']) {
                        $appliedToAnyInvoice = true;
                    }
                }
                
                // If credit still remains, search backward (from beginning to source-1 in ascending order: 1, 2, 3...)
                if ($remainingCredit > 0.01) {
                    Log::info('Credit remaining after forward search, searching backward in ascending order', [
                        'remaining_credit' => $remainingCredit,
                        'source_index' => $sourceIndex,
                        'search_range' => '0 to ' . ($sourceIndex - 1)
                    ]);
                    
                    // Search from index 0 to sourceIndex-1 (ascending order: 1, 2, 3...)
                    for ($i = 0; $i < $sourceIndex && $remainingCredit > 0.01; $i++) {
                        Log::info('Backward search - checking invoice at index', [
                            'index' => $i,
                            'invoice_reference' => $sortedRecords[$i]['reference_number'] ?? 'N/A',
                            'remaining_credit' => $remainingCredit
                        ]);
                        
                        $remainingCredit = $this->applyCreditToInvoice($sortedRecords[$i], $creditSource, $remainingCredit, $dataArray);
                        if ($remainingCredit < $creditSource['available_amount']) {
                            $appliedToAnyInvoice = true;
                        }
                    }
                }
                
                Log::info('Credit memo application completed', [
                    'source_reference' => $creditSource['source_reference'],
                    'original_available' => $creditSource['available_amount'],
                    'remaining_after_application' => $remainingCredit,
                    'total_applied' => $creditSource['available_amount'] - $remainingCredit,
                    'applied_to_any_invoice' => $appliedToAnyInvoice
                ]);
            }
        }
        
        return collect($dataArray);
    }
    
    /**
     * Apply credit memo to a specific invoice if eligible
     */
    private function applyCreditToInvoice($targetRecord, $creditSource, $remainingCredit, &$dataArray)
    {
        // Double-check available credit amount from source to prevent over-application
        $sourceRecord = AccountsPayable::find($creditSource['source_id']);
        if (!$sourceRecord) {
            Log::warning('Source credit memo record not found', ['source_id' => $creditSource['source_id']]);
            return $remainingCredit;
        }
        
        $originalCreditAmount = floatval($sourceRecord->CreditMemo ?? 0);
        $usedCreditAmount = \App\Models\CreditMemoApplication::where('source_ap_id', $creditSource['source_id'])
            ->sum('credit_amount') ?? 0;
        $actualAvailableCredit = $originalCreditAmount - $usedCreditAmount;
        
        // Ensure we don't exceed the actual available credit
        $remainingCredit = min($remainingCredit, $actualAvailableCredit);
        
        if ($remainingCredit <= 0.01) {
            Log::info('No credit remaining for application', [
                'source_reference' => $creditSource['source_reference'],
                'original_amount' => $originalCreditAmount,
                'used_amount' => $usedCreditAmount,
                'available' => $actualAvailableCredit
            ]);
            return 0;
        }
        
        $targetBalance = floatval($targetRecord['total_amount']) - 
                        Payment::where('accounts_payable_id', $targetRecord['id'])->sum('payment_amount');
        
        // Skip if invoice is already fully paid or has no balance
        if ($targetBalance <= 0.01) {
            return $remainingCredit;
        }
        
        // Note: Removed restriction on multiple auto credit applications
        // An invoice can receive multiple credit memo applications as long as it has remaining balance
        
        // Calculate how much credit to apply
        $creditToApply = min($remainingCredit, $targetBalance);
        
        if ($creditToApply > 0.01) {
            Log::info('Applying credit to invoice', [
                'source_reference' => $creditSource['source_reference'],
                'target_reference' => $targetRecord['reference_number'],
                'target_balance' => $targetBalance,
                'credit_to_apply' => $creditToApply,
                'remaining_after' => $remainingCredit - $creditToApply
            ]);
            
            try {
                // Create automatic payment record
                $payment = Payment::create([
                    'accounts_payable_id' => $targetRecord['id'],
                    'payment_amount' => $creditToApply,
                    'payment_type' => 'cash',
                    'payment_status' => ($creditToApply >= $targetBalance) ? 'full' : 'partial',
                    'payment_date' => now(),
                    'reference_number' => 'AUTO-CM-' . $creditSource['source_reference'],
                    'remarks' => 'Automatic credit memo application from ' . $creditSource['source_reference'],
                    'process_by' => 'System'
                ]);
                
                // Update target invoice status
                $newBalance = $targetBalance - $creditToApply;
                $newStatus = ($newBalance <= 0.01) ? 'Paid' : 'Partial';
                
                AccountsPayable::where('id', $targetRecord['id'])->update([
                    'status' => $newStatus
                ]);
                
                // Update the data array for display
                $originalIndex = collect($dataArray)->search(function($item) use ($targetRecord) {
                    return $item['id'] == $targetRecord['id'];
                });
                
                if ($originalIndex !== false) {
                    $dataArray[$originalIndex]['status'] = $newStatus;
                    $dataArray[$originalIndex]['balance_amount'] = $newBalance;
                }
                
                // Create CreditMemoApplication entry
                try {
                    \App\Models\CreditMemoApplication::create([
                        'source_ap_id' => $creditSource['source_id'],
                        'target_ap_id' => $targetRecord['id'],
                        'credit_amount' => $creditToApply,
                        'application_date' => now(),
                        'created_by' => auth()->user()->name ?? 'System',
                        'notes' => 'Sequential automatic credit memo application from ' . $creditSource['source_reference'] . ' to ' . $targetRecord['reference_number']
                    ]);
                } catch (Exception $e) {
                    Log::error('Error creating CreditMemoApplication entry: ' . $e->getMessage());
                }
                
                // Create SupplierRunningBalance entry
                try {
                    \App\Models\SupplierRunningBalance::addEntry(
                        $targetRecord['supplier_code'],
                        now(),
                        $targetRecord['id'],
                        'credit_memo_applied',
                        -$creditToApply,
                        'Sequential credit memo applied from ' . $creditSource['source_reference'] . ' (₱' . number_format($creditToApply, 2) . ')'
                    );
                } catch (Exception $e) {
                    Log::error('Error creating SupplierRunningBalance entry: ' . $e->getMessage());
                }
                
                // Update supplier credit data
                try {
                    SupplierCredit::updateSupplierCredit($targetRecord['supplier_code']);
                } catch (Exception $e) {
                    Log::error('Error updating supplier credit: ' . $e->getMessage());
                }
                
                // Reduce remaining credit
                $remainingCredit -= $creditToApply;
                
            } catch (Exception $e) {
                Log::error('Error applying credit to invoice', [
                    'target_id' => $targetRecord['id'],
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $remainingCredit;
    }

    /**
     * Manually apply auto credit memos to accounts payable records.
     * This endpoint can be called when needed instead of running on every page load.
     */
    public function applyAutoCreditMemos(Request $request)
    {
        try {
            // Get all accounts payable records for processing
            $query = DB::table('tblAccountsPayable as ap')
                ->leftJoin('tblSupplier as s', 'ap.supplier_code', '=', 's.SupplierCode')
                ->leftJoin(DB::raw('(
                    SELECT accounts_payable_id, SUM(payment_amount) as total_paid 
                    FROM tblPayments 
                    GROUP BY accounts_payable_id
                ) as payments'), 'ap.id', '=', 'payments.accounts_payable_id')
                ->select([
                    'ap.id',
                    'ap.date',
                    'ap.supplier_code',
                    'ap.supplier_name',
                    'ap.rr_number',
                    'ap.reference_number',
                    'ap.total_amount',
                    'ap.terms',
                    'ap.status',
                    'ap.CreditMemo',
                    'ap.process_by',
                    'ap.created_at',
                    's.SupplierName',
                    DB::raw('ISNULL(payments.total_paid, 0) as total_paid')
                ]);

            $results = $query->orderBy('ap.date', 'desc')
                           ->orderBy('ap.id', 'desc')
                           ->get();

            // Process results
            $data = $results->map(function ($item) {
                $totalPaid = floatval($item->total_paid ?? 0);
                $balanceAmount = floatval($item->total_amount) - $totalPaid;
                
                return [
                    'id' => $item->id,
                    'date' => $item->date,
                    'supplier_code' => $item->supplier_code,
                    'supplier_name' => $item->supplier_name ?: $item->SupplierName,
                    'rr_number' => $item->rr_number,
                    'reference_number' => $item->reference_number,
                    'total_amount' => floatval($item->total_amount),
                    'terms' => $item->terms,
                    'status' => $item->status,
                    'balance_amount' => $balanceAmount,
                    'CreditMemo' => floatval($item->CreditMemo ?? 0),
                    'process_by' => $item->process_by,
                    'sort_timestamp' => strtotime($item->created_at)
                ];
            });

            // Apply auto credit memos
            $processedData = $this->applyAutoCreditMemosToAP($data);

            return response()->json([
                'success' => true,
                'message' => 'Auto credit memos applied successfully',
                'processed_records' => $processedData->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error applying auto credit memos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error applying auto credit memos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate available credit memos for a supplier with proper usage tracking
     */
    private function getAvailableCreditMemosForSupplier($supplierCode)
    {
        // Get total credit memos generated for this supplier
        $totalCreditMemos = AccountsPayable::where('supplier_code', $supplierCode)
            ->sum('CreditMemo') ?? 0;
        
        // Get total credit memos used (from CreditMemoApplication table)
        $usedCreditMemos = \App\Models\CreditMemoApplication::whereHas('sourceAccountsPayable', function($query) use ($supplierCode) {
            $query->where('supplier_code', $supplierCode);
        })
        ->sum('credit_amount') ?? 0;
        
        $availableCredits = $totalCreditMemos - $usedCreditMemos;
        
        Log::info('Credit memo calculation for supplier', [
            'supplier_code' => $supplierCode,
            'total_generated' => $totalCreditMemos,
            'total_used' => $usedCreditMemos,
            'available' => $availableCredits
        ]);
        
        return max(0, $availableCredits); // Ensure non-negative
    }

    /**
     * Apply auto credit memos for a specific supplier.
     * This method is called after payment processing to apply available credits
     * to outstanding invoices for the same supplier.
     */
    private function applyAutoCreditMemosForSupplier($supplierCode)
    {
        Log::info('Auto Credit Memo - Processing supplier', ['supplier_code' => $supplierCode]);

        // First, check if there are any available credit memos for this supplier
        $availableCredits = $this->getAvailableCreditMemosForSupplier($supplierCode);
        
        if ($availableCredits <= 0) {
            Log::info('No available credit memos for supplier, skipping auto application', [
                'supplier_code' => $supplierCode,
                'available_credits' => $availableCredits
            ]);
            return; // Exit early if no credits available
        }

        // Get all accounts payable records for this supplier
        $query = DB::table('tblAccountsPayable as ap')
            ->leftJoin('tblSupplier as s', 'ap.supplier_code', '=', 's.SupplierCode')
            ->leftJoin(DB::raw('(
                SELECT accounts_payable_id, SUM(payment_amount) as total_paid 
                FROM tblPayments 
                GROUP BY accounts_payable_id
            ) as payments'), 'ap.id', '=', 'payments.accounts_payable_id')
            ->where('ap.supplier_code', $supplierCode)
            ->select([
                'ap.id',
                'ap.date',
                'ap.supplier_code',
                'ap.supplier_name',
                'ap.rr_number',
                'ap.reference_number',
                'ap.total_amount',
                'ap.terms',
                'ap.status',
                'ap.CreditMemo',
                'ap.process_by',
                'ap.created_at',
                's.SupplierName',
                DB::raw('ISNULL(payments.total_paid, 0) as total_paid')
            ]);

        $results = $query->orderBy('ap.date', 'desc')
                       ->orderBy('ap.id', 'desc')
                       ->get();

        // Process results
        $data = $results->map(function ($item) {
            $totalPaid = floatval($item->total_paid ?? 0);
            $balanceAmount = floatval($item->total_amount) - $totalPaid;
            
            return [
                'id' => $item->id,
                'date' => $item->date,
                'supplier_code' => $item->supplier_code,
                'supplier_name' => $item->supplier_name ?: $item->SupplierName,
                'rr_number' => $item->rr_number,
                'reference_number' => $item->reference_number,
                'total_amount' => floatval($item->total_amount),
                'terms' => $item->terms,
                'status' => $item->status,
                'balance_amount' => $balanceAmount,
                'CreditMemo' => floatval($item->CreditMemo ?? 0),
                'process_by' => $item->process_by,
                'sort_timestamp' => strtotime($item->created_at)
            ];
        });

        // Apply auto credit memos using the existing logic
        $this->applyAutoCreditMemosToAP($data);

        Log::info('Auto Credit Memo - Completed processing supplier', ['supplier_code' => $supplierCode]);
    }

    /**
     * Fix double-applied credit memos by removing excess auto-payments
     * This is a maintenance endpoint to fix existing data issues
     */
    public function fixDoubleAppliedCreditMemos(Request $request)
    {
        try {
            $supplierCode = $request->get('supplier_code');
            $fixCount = 0;
            $issues = [];
            
            // Get suppliers to process
            $suppliers = $supplierCode ? [$supplierCode] : 
                AccountsPayable::distinct('supplier_code')->pluck('supplier_code');
            
            foreach ($suppliers as $supplier) {
                Log::info('Checking supplier for credit memo issues', ['supplier_code' => $supplier]);
                
                // Calculate actual available credit memos
                $totalCreditMemos = AccountsPayable::where('supplier_code', $supplier)
                    ->sum('CreditMemo') ?? 0;
                
                $totalUsedCreditMemos = Payment::whereHas('accountsPayable', function($query) use ($supplier) {
                    $query->where('supplier_code', $supplier);
                })
                ->where('reference_number', 'LIKE', 'AUTO-CM-%')
                ->sum('payment_amount') ?? 0;
                
                $expectedAvailable = $totalCreditMemos - $totalUsedCreditMemos;
                
                if ($expectedAvailable < 0) {
                    // Credit memos over-applied - we have an issue
                    $overApplied = abs($expectedAvailable);
                    
                    $issues[] = [
                        'supplier_code' => $supplier,
                        'total_generated' => $totalCreditMemos,
                        'total_used' => $totalUsedCreditMemos,
                        'over_applied_amount' => $overApplied
                    ];
                    
                    Log::warning('Credit memo over-application detected', [
                        'supplier_code' => $supplier,
                        'total_generated' => $totalCreditMemos,
                        'total_used' => $totalUsedCreditMemos,
                        'over_applied' => $overApplied
                    ]);
                    
                    // Find the most recent auto-CM payments to reverse
                    $excessPayments = Payment::whereHas('accountsPayable', function($query) use ($supplier) {
                        $query->where('supplier_code', $supplier);
                    })
                    ->where('reference_number', 'LIKE', 'AUTO-CM-%')
                    ->orderBy('payment_date', 'desc')
                    ->orderBy('id', 'desc')
                    ->get();
                    
                    $amountToReverse = $overApplied;
                    
                    foreach ($excessPayments as $payment) {
                        if ($amountToReverse <= 0) break;
                        
                        $paymentAmount = floatval($payment->payment_amount);
                        
                        if ($paymentAmount <= $amountToReverse) {
                            // Remove entire payment
                            Log::info('Removing excess auto-CM payment', [
                                'payment_id' => $payment->id,
                                'amount' => $paymentAmount,
                                'reference' => $payment->reference_number
                            ]);
                            
                            $payment->delete();
                            $amountToReverse -= $paymentAmount;
                            $fixCount++;
                        } else {
                            // Reduce payment amount
                            $newAmount = $paymentAmount - $amountToReverse;
                            
                            Log::info('Reducing excess auto-CM payment', [
                                'payment_id' => $payment->id,
                                'old_amount' => $paymentAmount,
                                'new_amount' => $newAmount,
                                'reduction' => $amountToReverse
                            ]);
                            
                            $payment->update(['payment_amount' => $newAmount]);
                            $amountToReverse = 0;
                            $fixCount++;
                        }
                    }
                    
                    // Recalculate and update AP statuses for this supplier
                    $supplierAPs = AccountsPayable::where('supplier_code', $supplier)->get();
                    foreach ($supplierAPs as $ap) {
                        $totalPaid = $ap->payments()->sum('payment_amount');
                        $balance = $ap->total_amount - $totalPaid;
                        
                        $newStatus = 'Pending';
                        if ($balance <= 0) {
                            $newStatus = 'Paid';
                        } elseif ($totalPaid > 0) {
                            $newStatus = 'Partial';
                        }
                        
                        $ap->update(['status' => $newStatus]);
                    }
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Fixed {$fixCount} double-applied credit memo payments",
                'fixes_applied' => $fixCount,
                'issues_found' => $issues
            ]);
            
        } catch (Exception $e) {
            Log::error('Error fixing double-applied credit memos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fixing credit memo issues: ' . $e->getMessage()
            ], 500);
        }
    }
}