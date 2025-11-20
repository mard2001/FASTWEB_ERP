<?php

namespace App\Http\Controllers\api\AccountsReceivable;

use App\Http\Controllers\Controller;
use App\Models\AccountsReceivable;
use App\Models\Payment;
use App\Models\Bank;
use App\Models\Check;
use App\Models\Customer\Customer;
use App\Models\CustomerCredit;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AccountsReceivableController extends Controller
{
    /**
     * Display a listing of accounts receivable
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Use raw SQL for better performance similar to AP system
            $perPage = $request->get('per_page', 25);
            $page = $request->get('page', 1);
            
            $query = DB::table('tblAccountsReceivable as ar')
                ->leftJoin('tblCustomer as c', 'ar.customer_code', '=', 'c.Customer')
                ->leftJoin(DB::raw('(
                    SELECT accounts_receivable_id, SUM(payment_amount) as total_paid 
                    FROM tblPayments 
                    WHERE accounts_receivable_id IS NOT NULL
                    GROUP BY accounts_receivable_id
                ) as payments'), 'ar.id', '=', 'payments.accounts_receivable_id')
                ->select([
                    'ar.id',
                    'ar.date',
                    'ar.customer_code',
                    'ar.customer_name',
                    'ar.so_number',
                    'ar.reference_number',
                    'ar.total_amount',
                    'ar.terms',
                    'ar.status',
                    'ar.credit_generated',
                    'ar.process_by',
                    'ar.created_at',
                    'c.Name as customer_name_from_table',
                    DB::raw('ISNULL(payments.total_paid, 0) as total_paid')
                ]);

            // Apply filters efficiently
            if ($request->has('date_from') && $request->has('date_to')) {
                $query->whereBetween('ar.date', [$request->date_from, $request->date_to]);
            }

            if ($request->has('customer_code') && $request->customer_code != '') {
                $query->where(function($q) use ($request) {
                    $q->where('ar.customer_code', 'like', '%' . $request->customer_code . '%')
                      ->orWhere('ar.customer_name', 'like', '%' . $request->customer_code . '%');
                });
            }

            if ($request->has('status') && $request->status != '') {
                $query->where('ar.status', $request->status);
            }

            if ($request->has('so_number') && $request->so_number != '') {
                $query->where('ar.so_number', 'like', '%' . $request->so_number . '%');
            }

            // Get paginated results
            $offset = ($page - 1) * $perPage;
            $totalCount = $query->count();
            
            $results = $query->orderBy('ar.date', 'desc')
                           ->orderBy('ar.id', 'desc')
                           ->offset($offset)
                           ->limit($perPage)
                           ->get();

            // Process results efficiently
            $data = $results->map(function ($item) {
                $totalPaid = floatval($item->total_paid ?? 0);
                $totalCreditMemo = floatval($item->credit_generated ?? 0);
                $balanceAmount = floatval($item->total_amount) - $totalPaid;
                
                // Calculate overdue status
                $isOverdue = false;
                if ($item->status !== 'Settled') {
                    preg_match('/(\d+)/', $item->terms ?? '30', $matches);
                    $termDays = isset($matches[1]) ? (int)$matches[1] : 30;
                    $dueDate = \Carbon\Carbon::parse($item->date)->addDays($termDays);
                    $isOverdue = now()->gt($dueDate);
                }
                
                return [
                    'id' => $item->id,
                    'date' => $item->date,
                    'customer_code' => $item->customer_code,
                    'customer_name' => $item->customer_name ?: $item->customer_name_from_table,
                    'so_number' => $item->so_number,
                    'reference_number' => $item->reference_number,
                    'total_amount' => floatval($item->total_amount),
                    'terms' => $item->terms,
                    'status' => $item->status,
                    'balance_amount' => $balanceAmount,
                    'CreditMemo' => floatval($item->credit_generated ?? 0),
                    'process_by' => $item->process_by,
                    'is_overdue' => $isOverdue,
                    'created_at' => $item->created_at,
                    'sort_timestamp' => strtotime($item->created_at)
                ];
            });

            // Calculate pagination info
            $totalPages = ceil($totalCount / $perPage);
            $currentPage = $page;

            if ($data->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No Accounts Receivable data found',
                    'data' => [],
                    'pagination' => [
                        'current_page' => $currentPage,
                        'total_pages' => $totalPages,
                        'per_page' => $perPage,
                        'total_records' => $totalCount
                    ]
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }

            return response()->json([
                'success' => true,
                'message' => 'Accounts Receivable data retrieved successfully',
                'data' => $data,
                'pagination' => [
                    'current_page' => $currentPage,
                    'total_pages' => $totalPages,
                    'per_page' => $perPage,
                    'total_records' => $totalCount,
                    'has_more_pages' => $currentPage < $totalPages
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Error fetching accounts receivable: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch accounts receivable data',
                'error' => $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Store a newly created accounts receivable
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_code' => 'required|string|max:50',
                'so_number' => 'required|string|max:50',
                'date' => 'required|date',
                'total_amount' => 'required|numeric|min:0',
                'terms' => 'nullable|string|max:50',
                'remarks' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $receivable = AccountsReceivable::create([
                'customer_code' => $request->customer_code,
                'customer_name' => $request->customer_name ?? '',
                'so_number' => $request->so_number,
                'date' => $request->date,
                'total_amount' => $request->total_amount,
                'terms' => $request->terms ?? '30 Days',
                'remarks' => $request->remarks,
                'status' => 'Outstanding',
                'process_by' => auth()->user()->username ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Accounts receivable created successfully',
                'data' => $receivable->load('customer')
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating accounts receivable: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create accounts receivable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified accounts receivable
     */
    public function show($id): JsonResponse
    {
        try {
            $receivable = AccountsReceivable::with(['customer'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $receivable
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching accounts receivable: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Accounts receivable not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified accounts receivable
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $receivable = AccountsReceivable::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'customer_code' => 'sometimes|string|max:50',
                'so_number' => 'sometimes|string|max:50',
                'date' => 'sometimes|date',
                'total_amount' => 'sometimes|numeric|min:0',
                'terms' => 'sometimes|string|max:50',
                'remarks' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $receivable->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Accounts receivable updated successfully',
                'data' => $receivable->load('customer')
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating accounts receivable: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update accounts receivable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified accounts receivable
     */
    public function destroy($id): JsonResponse
    {
        try {
            $receivable = AccountsReceivable::findOrFail($id);
            $receivable->delete();

            return response()->json([
                'success' => true,
                'message' => 'Accounts receivable deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting accounts receivable: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete accounts receivable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customers for dropdown
     */
    public function getCustomers(): JsonResponse
    {
        try {
            $customers = Customer::select('Customer as customer_code', 'Name as customer_name')
                ->whereRaw('LTRIM(RTRIM(Name)) != \'\'')
                ->whereNotNull('Name')
                ->orderBy('Name')
                ->get()
                ->map(function ($customer) {
                    return [
                        'customer_code' => mb_convert_encoding($customer->customer_code, 'UTF-8', 'UTF-8'),
                        'customer_name' => mb_convert_encoding($customer->customer_name, 'UTF-8', 'UTF-8')
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $customers
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Error fetching customers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch customers',
                'error' => $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Get banks for dropdown
     */
    public function getBanks(): JsonResponse
    {
        try {
            $banks = Bank::select('id', 'bank_name', 'account_number')
                ->where('is_active', true)
                ->orderBy('bank_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $banks
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching banks: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch banks',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment for accounts receivable
     */
    public function processPayment(Request $request, $id): JsonResponse
    {
        try {
            // Clean UTF-8 characters from input data to prevent encoding errors
            $cleanInput = [];
            foreach ($request->all() as $key => $value) {
                if (is_string($value)) {
                    // Clean and sanitize string values
                    $cleanValue = $value;
                    
                    // Fix encoding issues
                    if (!mb_check_encoding($cleanValue, 'UTF-8')) {
                        $cleanValue = mb_convert_encoding($cleanValue, 'UTF-8', 'auto');
                    }
                    
                    // Replace problematic characters that might cause JSON encoding issues
                    $cleanValue = str_replace(["\r\n", "\r", "\n"], ' ', $cleanValue);
                    $cleanValue = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleanValue);
                    
                    $cleanInput[$key] = trim($cleanValue);
                } else {
                    $cleanInput[$key] = $value;
                }
            }
            
            // Replace request data with cleaned data
            $request->replace($cleanInput);
            
            $receivable = AccountsReceivable::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'payment_amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:cash,bank_transfer,gcash,check',
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

            // Additional validation: bank_id is required when payment_method is 'bank_transfer'
            if ($request->payment_method === 'bank_transfer' && empty($request->bank_id)) {
                $validator->after(function ($validator) {
                    $validator->errors()->add('bank_id', 'Bank selection is required for bank payments.');
                });
            }

            // Additional validation: gcash_id is required when payment_method is 'gcash'
            if ($request->payment_method === 'gcash' && empty($request->gcash_id)) {
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
                ], 422, [], JSON_UNESCAPED_UNICODE);
            }

            // Removed FIFO Payment Validation: allow paying any invoice order for this customer
            // This change aligns AR behavior with Supplier Credit flexibility and enables
            // auto CM application across invoices regardless of payment sequence.

            // Process payment amount - overpayments are now allowed
            $paymentAmount = $request->payment_amount;
            $currentBalance = $receivable->balance_amount;

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

            // Create payment record (using unified Payment table)
            $paymentData = [
                'accounts_receivable_id' => $receivable->id,
                'accounts_payable_id' => null, // Ensure AP ID is null for AR payments
                'payment_amount' => $paymentAmount, // Use full payment amount (including overpayment)
                'payment_type' => $request->payment_method, // cash, bank_transfer, gcash, check
                'payment_status' => $paymentStatus, // full, partial
                'payment_date' => now(),
                'reference_number' => $request->reference_number,
                'remarks' => $request->remarks,
                'process_by' => auth()->user()->name ?? 'System'
            ];

            // Add bank_id if payment method is bank_transfer or check and bank_id is provided
            if (($request->payment_method === 'bank_transfer' || $request->payment_method === 'check') && $request->bank_id) {
                $paymentData['bank_id'] = $request->bank_id;
            }

            // Add gcash_id if payment method is gcash and gcash_id is provided
            if ($request->payment_method === 'gcash' && $request->gcash_id) {
                $paymentData['gcash_id'] = $request->gcash_id;
            }

            // Handle check payment if enabled
            $checkId = null;
            
            // Debug: Log all request data related to check payment
            Log::info('AR Check Payment Debug - Request Data', [
                'pay_by_check' => $request->pay_by_check,
                'payment_method' => $request->payment_method,
                'bank_id' => $request->bank_id,
                'check_payee' => $request->check_payee,
                'check_date' => $request->check_date,
                'check_amount_in_words' => $request->check_amount_in_words,
                'check_number' => $request->check_number,
                'payment_amount' => $paymentAmount
            ]);
            
            // Check if pay_by_check is truthy (could be '1', 1, true, 'true', etc.)
            $shouldCreateCheck = ($request->pay_by_check == '1' || $request->pay_by_check === true || $request->pay_by_check === 'true') && ($request->payment_method === 'bank_transfer' || $request->payment_method === 'check');
            
            Log::info('AR Check Payment Debug - Condition Check', [
                'pay_by_check_value' => $request->pay_by_check,
                'pay_by_check_type' => gettype($request->pay_by_check),
                'payment_method' => $request->payment_method,
                'should_create_check' => $shouldCreateCheck
            ]);
            
            if ($shouldCreateCheck) {
                Log::info('AR Check Payment Debug - Creating check record...');
                
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
                    'Remarks' => 'Payment for AR #' . $receivable->id . ($creditMemo > 0 ? ' (includes overpayment of ₱' . number_format($creditMemo, 2) . ')' : '')
                ];

                Log::info('AR Check Payment Debug - Check data to create', $checkData);

                try {
                    $check = Check::create($checkData);
                    $checkId = $check->CheckID;

                    Log::info('AR Check Payment Debug - Check created successfully', [
                        'check_id' => $checkId,
                        'check_object' => $check->toArray()
                    ]);

                    // Add check_id to payment data
                    $paymentData['check_id'] = $checkId;
                } catch (\Exception $e) {
                    Log::error('AR Check Payment Debug - Check creation failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'check_data' => $checkData
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create check record: ' . $e->getMessage()
                    ], 500);
                }
            } else {
                Log::info('AR Check Payment Debug - Check creation skipped');
            }

            // Clean payment data before creating record
            foreach ($paymentData as $key => $value) {
                if (is_string($value) && !empty($value)) {
                    // Ensure UTF-8 encoding and remove control characters
                    $cleanValue = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
                    $cleanValue = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleanValue);
                    $paymentData[$key] = $cleanValue;
                }
            }

            $payment = Payment::create($paymentData);

            try {
                activity('accounts_receivable')
                    ->performedOn($receivable)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'url' => request()->fullUrl(),
                        'method' => request()->method(),
                        'ar_id' => $receivable->id,
                        'customer_code' => $receivable->customer_code,
                        'customer_name' => $receivable->customer_name,
                        'so_number' => $receivable->so_number,
                        'reference_number' => $receivable->reference_number,
                        'payment_amount' => $paymentAmount,
                        'payment_type' => $request->payment_method,
                        'payment_status' => $paymentStatus,
                        'remarks' => $request->remarks,
                        'bank_id' => $paymentData['bank_id'] ?? null,
                        'gcash_id' => $paymentData['gcash_id'] ?? null,
                        'check_id' => $checkId
                    ])
                    ->event('payment_made')
                    ->log('Payment of ₱' . number_format($paymentAmount, 2) . ' recorded for AR #' . $receivable->id . ' (' . ($receivable->reference_number ?? $receivable->so_number ?? 'N/A') . ')');
            } catch (\Throwable $e) {
            }

            // Log to bank reconciliation when payment is via bank or bank check (deposit)
            try {
                $affectedBankId = $paymentData['bank_id'] ?? null;
                if (!$affectedBankId && $checkId) {
                    $check = Check::find($checkId);
                    $affectedBankId = $check ? $check->BankID : null;
                }

                if ($affectedBankId) {
                    $bank = \App\Models\Bank::find($affectedBankId);
                    $bankName = $bank ? $bank->BankName : 'Unknown Bank';
                    $paymentTypeDisplay = ($request->payment_method === 'check' || $checkId) ? 'Bank Check' : 'Bank';
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
                            'ar_id' => $receivable->id,
                            'ar_reference' => $receivable->reference_number,
                            'customer_code' => $receivable->customer_code,
                            'customer_name' => $receivable->customer_name,
                            'payment_id' => $payment->id,
                            'payment_amount' => $paymentAmount,
                            'payment_type' => $paymentTypeDisplay,
                            'bank_id' => $affectedBankId,
                            'check_id' => $checkId,
                        ])
                        ->event('AR Deposit')
                        ->log('Deposit of ₱' . number_format($paymentAmount, 2) . ' via ' . $paymentTypeDisplay . ' for AR #' . $receivable->id . ' on \'' . $bankName . '\'');
                }
            } catch (\Throwable $e) {
            }


            // Update receivable status and balance
            if ($newBalance <= 0 || $paymentStatus === 'full') {
                $receivable->status = 'Settled';
                $receivable->current_balance = 0; // Ensure balance is exactly 0 for settled items
            } else {
                // AR system keeps status as 'Outstanding' for partial payments
                // The payment tracking is handled by the CustomerPayment history
                $receivable->status = 'Outstanding';
                $receivable->current_balance = $newBalance;
            }
            
            // Update last balance update timestamp
            $receivable->last_balance_update = now();
            
            // Update credit memo if there's overpayment
            if ($creditMemo > 0) {
                $receivable->credit_generated = ($receivable->credit_generated ?? 0) + $creditMemo;
            }
            
            $receivable->saveQuietly();

            try {
                CustomerCredit::updateCustomerCredit($receivable->customer_code);
            } catch (\Throwable $e) {
            }

            // Prepare response message (avoid peso symbol for UTF-8 safety)
            $message = 'Payment processed successfully';
            if ($creditMemo > 0) {
                $message = 'Payment processed successfully. Overpayment of PHP ' . number_format($creditMemo, 2) . ' stored as Credit Memo.';

                // Auto-apply credit memos to other outstanding invoices for this customer
                // Mirrors the supplier/AP behavior: immediately distribute available credit
                try {
                    Log::info('AR payment generated credit memo, applying auto credit memos for customer', [
                        'customer_code' => $receivable->customer_code,
                        'credit_memo_generated' => $creditMemo,
                        'source_reference' => $receivable->reference_number ?? $receivable->so_number ?? ('AR-' . $receivable->id)
                    ]);
                    // Use the internal method to apply credits for this specific customer
                    $this->autoApplyCreditMemos($receivable->customer_code);
                } catch (\Exception $e) {
                    // Log any errors but do not fail the payment
                    Log::error('Error applying AR auto credit memos after payment: ' . $e->getMessage(), [
                        'customer_code' => $receivable->customer_code,
                        'ar_id' => $receivable->id
                    ]);
                }
            }

            // Simplified response to avoid UTF-8 issues with complex objects
            $responseData = [
                'success' => true,
                'message' => mb_convert_encoding($message, 'UTF-8', 'UTF-8'),
                'payment_id' => $payment->id,
                'new_balance' => round($newBalance, 2),
                'credit_memo' => round($creditMemo, 2),
                'payment_amount' => round($paymentAmount, 2),
                'payment_status' => $paymentStatus,
                'ar_status' => $receivable->status
            ];

            // Include check ID if check was created
            if ($checkId) {
                $responseData['check_id'] = $checkId;
                $responseData['message'] = mb_convert_encoding('Payment and check processed successfully', 'UTF-8', 'UTF-8');
            }

            return response()->json($responseData, 200, ['Content-Type' => 'application/json; charset=utf-8']);

        } catch (\Exception $e) {
            Log::error('Error processing AR payment: ' . $e->getMessage());
            
            // Clean the error message to prevent JSON encoding issues
            $errorMessage = mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8');
            $errorMessage = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $errorMessage);
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $errorMessage
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Get payment history for accounts receivable
     */
    public function getPaymentHistory($id): JsonResponse
    {
        try {
            $receivable = AccountsReceivable::with(['payments' => function($query) {
                $query->orderBy('payment_date', 'desc');
            }, 'customer'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'receivable' => $receivable,
                    'payments' => $receivable->payments,
                    'total_paid' => $receivable->total_paid_amount,
                    'balance' => $receivable->balance_amount
                ]
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            Log::error('Error fetching AR payment history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment history',
                'error' => $e->getMessage()
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Get summary statistics
     */
    public function summary(): JsonResponse
    {
        try {
            $totalReceivables = AccountsReceivable::sum('total_amount');
            
            // Calculate total paid from unified payments table
            $totalPaid = DB::table('tblPayments')
                ->where('accounts_receivable_id', '!=', null)
                ->sum('payment_amount') ?? 0;
                
            // Calculate outstanding amount using proper balance calculation
            $outstandingAmount = DB::table('tblAccountsReceivable as ar')
                ->leftJoin(DB::raw('(
                    SELECT accounts_receivable_id, SUM(payment_amount) as total_paid 
                    FROM tblPayments 
                    WHERE accounts_receivable_id IS NOT NULL
                    GROUP BY accounts_receivable_id
                ) as payments'), 'ar.id', '=', 'payments.accounts_receivable_id')
                ->where('ar.status', 'Outstanding')
                ->selectRaw('SUM(ar.total_amount - ISNULL(payments.total_paid, 0)) as balance')
                ->value('balance') ?? 0;
                
            // Calculate overdue amount using proper balance calculation  
            $overdueAmount = DB::table('tblAccountsReceivable as ar')
                ->leftJoin(DB::raw('(
                    SELECT accounts_receivable_id, SUM(payment_amount) as total_paid 
                    FROM tblPayments 
                    WHERE accounts_receivable_id IS NOT NULL
                    GROUP BY accounts_receivable_id
                ) as payments'), 'ar.id', '=', 'payments.accounts_receivable_id')
                ->where('ar.status', 'Outstanding')
                ->whereRaw("DATEADD(day, CAST(SUBSTRING(ISNULL(ar.terms, '30'), PATINDEX('%[0-9]%', ISNULL(ar.terms, '30')), PATINDEX('%[^0-9]%', SUBSTRING(ISNULL(ar.terms, '30'), PATINDEX('%[0-9]%', ISNULL(ar.terms, '30')), LEN(ISNULL(ar.terms, '30')))) - 1) AS INT), ar.date) < GETDATE()")
                ->selectRaw('SUM(ar.total_amount - ISNULL(payments.total_paid, 0)) as balance')
                ->value('balance') ?? 0;
            
            $outstandingCount = AccountsReceivable::outstanding()->count();
            $overdueCount = AccountsReceivable::overdue()->count();
            $settledCount = AccountsReceivable::settled()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_receivables' => $totalReceivables,
                    'total_paid' => $totalPaid,
                    'outstanding_amount' => $outstandingAmount,
                    'overdue_amount' => $overdueAmount,
                    'outstanding_count' => $outstandingCount,
                    'overdue_count' => $overdueCount,
                    'settled_count' => $settledCount
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching summary: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply automatic credit memos
     */
    public function applyAutoCreditMemos(Request $request): JsonResponse
    {
        try {
            $customerCode = $request->get('customer_code');
            $result = $this->autoApplyCreditMemos($customerCode);

            return response()->json([
                'success' => true,
                'message' => 'Auto credit memos applied successfully',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            Log::error('Error applying auto credit memos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply auto credit memos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set AR number for redirection
     */
    public function setARNum(Request $request): JsonResponse
    {
        try {
            $request->session()->put('ar_number', $request->ar_number);
            
            return response()->json([
                'success' => true,
                'message' => 'AR number set successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error setting AR number: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to set AR number',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print page for accounts receivable
     */
    public function printPage(Request $request)
    {
        try {
            $arNumber = $request->session()->get('ar_number');
            if (!$arNumber) {
                return redirect()->route('accounts-receivable')->with('error', 'No AR number specified for printing');
            }

            $receivable = AccountsReceivable::where('so_number', $arNumber)
                ->with(['customer'])
                ->first();

            if (!$receivable) {
                return redirect()->route('accounts-receivable')->with('error', 'Accounts receivable not found');
            }

            return view('Pages.Printing.AR_printing', compact('receivable'));
        } catch (\Exception $e) {
            Log::error('Error loading print page: ' . $e->getMessage());
            return redirect()->route('accounts-receivable')->with('error', 'Failed to load print page');
        }
    }

    /**
     * Auto-apply credit memos for a customer
     */
    private function autoApplyCreditMemos($customerCode)
    {
        // Implements automatic application of overpayment credit memos to open AR invoices
        // Mirrors the AP flow but uses AR fields and unified Payments table
        $summary = [
            'applied_count' => 0,
            'total_amount' => 0,
            'customers_processed' => [],
        ];

        try {
            // Build base query of AR with total paid aggregation
            $query = DB::table('tblAccountsReceivable as ar')
                ->leftJoin('tblCustomer as c', 'ar.customer_code', '=', 'c.Customer')
                ->leftJoin(DB::raw('(
                    SELECT accounts_receivable_id, SUM(payment_amount) as total_paid 
                    FROM tblPayments 
                    WHERE accounts_receivable_id IS NOT NULL
                    GROUP BY accounts_receivable_id
                ) as payments'), 'ar.id', '=', 'payments.accounts_receivable_id')
                ->select([
                    'ar.id',
                    'ar.date',
                    'ar.customer_code',
                    'ar.customer_name',
                    'ar.so_number',
                    'ar.reference_number',
                    'ar.total_amount',
                    'ar.terms',
                    'ar.status',
                    'ar.credit_generated',
                    'ar.process_by',
                    'ar.created_at',
                    DB::raw('ISNULL(payments.total_paid, 0) as total_paid')
                ]);

            if (!empty($customerCode)) {
                $query->where('ar.customer_code', $customerCode);
            }

            // Sort stable for sequential application
            $records = $query->orderBy('ar.date', 'asc')
                            ->orderBy('ar.id', 'asc')
                            ->get();

            if ($records->isEmpty()) {
                return $summary; // Nothing to process
            }

            // Group by customer to apply credits within the same customer only
            $byCustomer = $records->groupBy('customer_code');

            foreach ($byCustomer as $custCode => $items) {
                $sorted = $items->values();

                // Build credit sources from AR records with credit_generated
                $creditSources = [];
                foreach ($sorted as $idx => $rec) {
                    $creditAmount = floatval($rec->credit_generated ?? 0);
                    if ($creditAmount > 0.0001) {
                        // Compute used amount for this source via AUTO-CM payments tagged with the source reference
                        $sourceRef = $rec->reference_number ?? $rec->so_number ?? ('AR-' . $rec->id);
                        $usedAmount = Payment::whereNotNull('accounts_receivable_id')
                            ->where('reference_number', 'AUTO-CM-' . $sourceRef)
                            ->sum('payment_amount') ?? 0;

                        $availableAmount = $creditAmount - floatval($usedAmount);
                        if ($availableAmount > 0.0001) {
                            $creditSources[] = [
                                'source_index' => $idx,
                                'source_id' => $rec->id,
                                'source_reference' => $sourceRef,
                                'available_amount' => $availableAmount,
                                'original_amount' => $creditAmount,
                            ];
                        }
                    }
                }

                // Apply credits sequentially: forward from source+1, then wrap to start
                foreach ($creditSources as $creditSource) {
                    $remaining = $creditSource['available_amount'];
                    if ($remaining <= 0.0001) continue;

                    // Forward pass
                    for ($i = $creditSource['source_index'] + 1; $i < count($sorted) && $remaining > 0.0001; $i++) {
                        $targetRec = $sorted[$i];
                        // Skip if already settled
                        if (($targetRec->status ?? '') === 'Settled') continue;

                        // Compute current balance from DB to avoid stale total_paid
                        $paid = Payment::where('accounts_receivable_id', $targetRec->id)->sum('payment_amount');
                        $balance = floatval($targetRec->total_amount) - floatval($paid ?? 0);
                        if ($balance <= 0.0001) continue;

                        $apply = min($remaining, $balance);
                        if ($apply > 0.0001) {
                            // Build target reference for descriptive notes/remarks
                            $targetRef = $targetRec->reference_number ?? $targetRec->so_number ?? ('AR-' . $targetRec->id);

                            // Create automatic payment
                            $payment = Payment::create([
                                'accounts_receivable_id' => $targetRec->id,
                                'payment_amount' => $apply,
                                'payment_type' => 'cash',
                                'payment_status' => ($apply >= $balance) ? 'full' : 'partial',
                                'payment_date' => now(),
                                'reference_number' => 'AUTO-CM-' . $creditSource['source_reference'],
                                'remarks' => 'Sequential automatic credit memo application from ' . $creditSource['source_reference'] . ' to ' . $targetRef,
                                'process_by' => auth()->user()->name ?? 'System',
                            ]);

                            // Record AR credit memo application entry
                            try {
                                \App\Models\ARCreditMemoApplication::create([
                                    'source_ar_id' => $creditSource['source_id'],
                                    'target_ar_id' => $targetRec->id,
                                    'credit_amount' => $apply,
                                    'application_date' => now(),
                                    'created_by' => auth()->id(),
                                    'notes' => 'Sequential automatic credit memo application from ' . $creditSource['source_reference'] . ' to ' . $targetRef,
                                    'status' => 'Applied',
                                ]);
                            } catch (\Exception $e) {
                                Log::warning('Failed to record AR CM application', [
                                    'error' => $e->getMessage(),
                                    'source_ar_id' => $creditSource['source_id'],
                                    'target_ar_id' => $targetRec->id,
                                    'amount' => $apply,
                                ]);
                            }

                            // Update AR status and balance fields
                            $newBalance = max(0, $balance - $apply);
                            $newStatus = ($newBalance <= 0.0001) ? 'Settled' : 'Outstanding';
                            AccountsReceivable::where('id', $targetRec->id)->update([
                                'status' => $newStatus,
                                'current_balance' => $newBalance,
                                'last_balance_update' => now(),
                            ]);

                            // Decrement remaining
                            $remaining -= $apply;
                            $summary['applied_count'] += 1;
                            $summary['total_amount'] += $apply;
                        }
                    }

                    // Wrap-around pass (apply to earlier invoices)
                    for ($i = 0; $i < $creditSource['source_index'] && $remaining > 0.0001; $i++) {
                        $targetRec = $sorted[$i];
                        if (($targetRec->status ?? '') === 'Settled') continue;

                        $paid = Payment::where('accounts_receivable_id', $targetRec->id)->sum('payment_amount');
                        $balance = floatval($targetRec->total_amount) - floatval($paid ?? 0);
                        if ($balance <= 0.0001) continue;

                        $apply = min($remaining, $balance);
                        if ($apply > 0.0001) {
                            // Build target reference for descriptive notes/remarks
                            $targetRef = $targetRec->reference_number ?? $targetRec->so_number ?? ('AR-' . $targetRec->id);

                            $payment = Payment::create([
                                'accounts_receivable_id' => $targetRec->id,
                                'payment_amount' => $apply,
                                'payment_type' => 'cash',
                                'payment_status' => ($apply >= $balance) ? 'full' : 'partial',
                                'payment_date' => now(),
                                'reference_number' => 'AUTO-CM-' . $creditSource['source_reference'],
                                'remarks' => 'Wrap-around automatic credit memo application from ' . $creditSource['source_reference'] . ' to ' . $targetRef,
                                'process_by' => auth()->user()->name ?? 'System',
                            ]);

                            // Record AR credit memo application entry (wrap pass)
                            try {
                                \App\Models\ARCreditMemoApplication::create([
                                    'source_ar_id' => $creditSource['source_id'],
                                    'target_ar_id' => $targetRec->id,
                                    'credit_amount' => $apply,
                                    'application_date' => now(),
                                    'created_by' => auth()->id(),
                                    'notes' => 'Wrap-around automatic credit memo application from ' . $creditSource['source_reference'] . ' to ' . $targetRef,
                                    'status' => 'Applied',
                                ]);
                            } catch (\Exception $e) {
                                Log::warning('Failed to record AR CM application (wrap)', [
                                    'error' => $e->getMessage(),
                                    'source_ar_id' => $creditSource['source_id'],
                                    'target_ar_id' => $targetRec->id,
                                    'amount' => $apply,
                                ]);
                            }

                            $newBalance = max(0, $balance - $apply);
                            $newStatus = ($newBalance <= 0.0001) ? 'Settled' : 'Outstanding';
                            AccountsReceivable::where('id', $targetRec->id)->update([
                                'status' => $newStatus,
                                'current_balance' => $newBalance,
                                'last_balance_update' => now(),
                            ]);

                            $remaining -= $apply;
                            $summary['applied_count'] += 1;
                            $summary['total_amount'] += $apply;
                        }
                    }
                }

                $summary['customers_processed'][] = $custCode;
            }

            // Round totals for neatness
            $summary['total_amount'] = round($summary['total_amount'], 2);
            return $summary;
        } catch (\Exception $e) {
            Log::error('AR Auto CM error: ' . $e->getMessage());
            return [
                'applied_count' => 0,
                'total_amount' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }
}