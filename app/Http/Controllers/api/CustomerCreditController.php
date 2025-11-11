<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer\Customer;
use App\Models\CustomerCredit;
use App\Models\AccountsReceivable;
use App\Models\Payment;
use App\Models\CreditMemoApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerCreditController extends Controller
{
    /**
     * Get customers with their credit summary information.
     */
    public function getCustomersWithCredits()
    {
        try {
            // Refresh customer credit data to ensure all customers included
            Log::info('Refreshing customer credit data to ensure all customers are included...');
            CustomerCredit::refreshAllCustomerCredits();

            // Get refreshed data from the CustomerCredit table
            $customers = CustomerCredit::with('customer')->orderBy('customer_name')->get();

            // Convert to array format expected by the frontend
            $customersArray = $customers->map(function ($customer) {
                return [
                    'CustomerCode' => $customer->customer_code,
                    'CustomerName' => $customer->customer_name,
                    'total_credit' => $customer->total_credit,
                    'total_paid' => $customer->total_paid,
                    'balance' => $customer->balance,
                    'credit_limit' => $customer->credit_limit,
                    'credit_balance' => $customer->credit_balance
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'data' => $customersArray
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getCustomersWithCredits: ' . $e->getMessage());
            
            // Fallback to original query if there's an issue with the new table
            try {
                $customers = DB::select("
                    SELECT 
                        c.Customer as CustomerCode,
                        c.Name as CustomerName,
                        c.CreditLimit as credit_limit,
                        ISNULL(credit_summary.total_credit, 0) as total_credit,
                        ISNULL(payment_summary.total_paid, 0) as total_paid,
                        (ISNULL(credit_summary.total_credit, 0) - ISNULL(payment_summary.total_paid, 0)) as balance,
                        (ISNULL(c.CreditLimit, 0) - (ISNULL(credit_summary.total_credit, 0) - ISNULL(payment_summary.total_paid, 0))) as credit_balance
                    FROM tblCustomer c
                    LEFT JOIN (
                        SELECT 
                            customer_code,
                            SUM(total_amount) as total_credit
                        FROM tblAccountsReceivable 
                        GROUP BY customer_code
                    ) credit_summary ON c.Customer = credit_summary.customer_code
                    LEFT JOIN (
                        SELECT 
                            ar.customer_code,
                            SUM(p.payment_amount) as total_paid
                        FROM tblAccountsReceivable ar
                        INNER JOIN tblPayments p ON ar.id = p.accounts_receivable_id
                        WHERE p.reference_number NOT LIKE 'AUTO-CM-%' OR p.reference_number IS NULL
                        GROUP BY ar.customer_code
                    ) payment_summary ON c.Customer = payment_summary.customer_code
                    ORDER BY c.Name
                ");

                return response()->json([
                    'success' => true,
                    'data' => $customers
                ]);
            } catch (\Exception $fallbackError) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load customer credit data',
                    'error' => $fallbackError->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Refresh all customer credit data in the table
     */
    public function refreshCustomerCredits()
    {
        try {
            $count = CustomerCredit::refreshAllCustomerCredits();
            return response()->json([
                'success' => true,
                'message' => "Successfully refreshed {$count} customer credit records",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error refreshing customer credits: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh customer credit data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get customer transactions and credit details
     */
    public function getCustomerTransactions($customerCode)
    {
        try {
            // Get customer basic information
            $customer = Customer::where('Customer', $customerCode)->first();
            
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            // Get accounts receivable transactions for this customer with payments
            $accountsReceivableTransactions = AccountsReceivable::where('customer_code', $customerCode)
                ->with([
                    'payments' => function($query) {
                        $query->with('check')->orderBy('payment_date', 'asc');
                    }
                ])
                ->orderBy('date', 'asc')
                ->get();

            // Calculate totals properly - we'll calculate these after processing all transactions
            // to avoid double-counting credit memo applications
            $totalDebt = 0;
            $totalPaid = 0;
            $totalCreditMemo = 0;

            // Create a complete transaction history using AccountsPayable and Payment data
            $transactionHistory = collect();
                // Build all transactions first, then calculate running balances
                $allTransactions = collect();
                
                // Add all invoices and payments
                foreach ($accountsReceivableTransactions as $arTransaction) {
                    // Separate regular payments from auto credit memo applications
                    $regularPayments = $arTransaction->payments->filter(function($payment) {
                        return strpos($payment->reference_number ?? '', 'AUTO-CM-') !== 0;
                    });

                    $autoCreditMemoPayments = $arTransaction->payments->filter(function($payment) {
                        return strpos($payment->reference_number ?? '', 'AUTO-CM-') === 0;
                    });

                    $totalAutoCreditMemo = $autoCreditMemoPayments->sum('payment_amount');

                    // For AR, actual CM applied are tracked via AUTO-CM payments on this AR
                    $actualCreditMemoApplied = Payment::where('accounts_receivable_id', $arTransaction->id)
                        ->where('reference_number', 'LIKE', 'AUTO-CM-%')
                        ->sum('payment_amount');

                    // Create invoice record with auto credit memo information if applicable
                    $invoiceDescription = 'Invoice - ' . ($arTransaction->reference_number ?? 'N/A');
                    $invoicePaymentAmount = 0;

                    if ($actualCreditMemoApplied > 0) {
                        $invoicePaymentAmount = $actualCreditMemoApplied;
                    }

                    $allTransactions->push([
                        'id' => $arTransaction->id,
                        'ar_transaction_id' => $arTransaction->id,
                        'type' => 'invoice',
                        'date' => $arTransaction->date->format('Y-m-d'),
                        'reference_number' => $arTransaction->reference_number,
                        'so_number' => $arTransaction->so_number,
                        'description' => $invoiceDescription,
                        'transaction_amount' => $arTransaction->total_amount,
                        'payment_amount' => $invoicePaymentAmount,
                        'auto_credit_memo' => $totalAutoCreditMemo,
                        'credit_memo_applied' => $actualCreditMemoApplied,
                        'status' => $arTransaction->status ?? 'Pending',
                        'terms' => $arTransaction->terms,
                        'remarks' => $arTransaction->remarks,
                        'is_overdue' => $arTransaction->is_overdue ?? false,
                        'parent_transaction_id' => $arTransaction->id,
                        'sort_date' => $arTransaction->created_at ? $arTransaction->created_at->format('Y-m-d H:i:s') : $arTransaction->date->format('Y-m-d H:i:s')
                    ]);

                    // Add regular payment records (excluding auto credit memo applications)
                    foreach ($regularPayments as $payment) {
                        // Check if this payment creates an overpayment
                        $totalRegularPayments = $regularPayments->sum('payment_amount');
                        $invoiceBalance = $arTransaction->total_amount - $actualCreditMemoApplied;
                        $hasOverpayment = $totalRegularPayments > $invoiceBalance;

                        // Determine if this specific payment creates the overpayment
                        $paymentsBeforeThis = $regularPayments->filter(function($p) use ($payment) {
                            return ($p->payment_date <= $payment->payment_date) && ($p->id < $payment->id);
                        });
                        $balanceBeforePayment = $invoiceBalance - $paymentsBeforeThis->sum('payment_amount');
                        // Use a small epsilon to avoid floating point precision issues
                        $epsilon = 0.0001;
                        $createsOverpayment = $balanceBeforePayment > 0 && (($balanceBeforePayment - $payment->payment_amount) < -$epsilon);

                        // Determine payment type display text
                        $paymentTypeDisplay = ucfirst($payment->payment_type ?? 'Cash');
                        if ($payment->payment_type === 'bank' && $payment->check_id) {
                            $paymentTypeDisplay = 'Bank Check';
                        }

                        $paymentDescription = 'Payment - ' . $paymentTypeDisplay;
                        // Mirror supplier logic: only label "with CM" if this invoice actually has CM applied
                        if ($createsOverpayment && ($actualCreditMemoApplied > 0)) {
                            $paymentDescription .= ' with CM';
                        }

                        $allTransactions->push([
                            'id' => 'payment_' . $payment->id,
                            'ar_transaction_id' => $arTransaction->id,
                            'type' => 'payment',
                            'date' => $payment->payment_date ? $payment->payment_date->format('Y-m-d') : null,
                            'reference_number' => $payment->reference_number ?? $arTransaction->reference_number,
                            'so_number' => $arTransaction->so_number,
                            'description' => $paymentDescription,
                            'transaction_amount' => 0,
                            'payment_amount' => $payment->payment_amount,
                            'payment_type' => $paymentTypeDisplay,
                            // Flag CM only when this payment creates overpayment AND invoice has CM applied
                            'has_credit_memo' => ($createsOverpayment && ($actualCreditMemoApplied > 0)),
                            // Optional: expose overpayment portion on this payment for downstream consumers
                            'credit_memo_amount' => ($createsOverpayment && ($actualCreditMemoApplied > 0)) ? max(0, $payment->payment_amount - max(0, $balanceBeforePayment)) : 0,
                            'status' => ($createsOverpayment && ($actualCreditMemoApplied > 0)) ? 'Fully Paid with CM' : 'Payment Made',
                            'terms' => $arTransaction->terms,
                            'remarks' => $payment->remarks ?? $arTransaction->remarks,
                            'is_overdue' => false,
                            'parent_transaction_id' => $arTransaction->id,
                            'sort_date' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : ($payment->payment_date ? $payment->payment_date->format('Y-m-d H:i:s') : null)
                        ]);
                    }
                }

                // Sort all transactions by date
                $sortedTransactions = $allTransactions->sortBy('sort_date');

                // Calculate individual invoice balances
                $invoiceBalances = [];
                
                // First, initialize invoice balances and subtract auto credit memos
                foreach ($sortedTransactions as $transaction) {
                    if ($transaction['type'] === 'invoice') {
                        $initialBalance = $transaction['transaction_amount'];
                        // Subtract auto credit memo if present
                        if (isset($transaction['auto_credit_memo']) && $transaction['auto_credit_memo'] > 0) {
                            $initialBalance -= $transaction['auto_credit_memo'];
                        }
                        // Use AR identifier (parent_transaction_id) for invoice balance tracking
                        $invoiceBalances[$transaction['parent_transaction_id']] = $initialBalance;
                    }
                }
                
                // Process transactions and calculate balances
                foreach ($sortedTransactions as $transaction) {
                    if ($transaction['type'] === 'invoice') {
                        // For invoices, show the current balance of that specific invoice
                        $transaction['running_balance'] = $invoiceBalances[$transaction['parent_transaction_id']];
                    } else if ($transaction['type'] === 'payment') {
                        // For payments, subtract from the specific invoice and show the remaining balance
                        $invoiceId = $transaction['parent_transaction_id'];
                        $invoiceBalances[$invoiceId] -= $transaction['payment_amount'];
                        $transaction['running_balance'] = $invoiceBalances[$invoiceId];
                    }
                    
                    $transactionHistory->push($transaction);
                }

            // Sort by date (oldest first) for display
            $sortedTransactionHistory = $transactionHistory->sortBy(function ($item) {
                $dateTime = $item['sort_date'];
                // Add a secondary sort: for same datetime, transactions come first, then payments
                $typePriority = $item['type'] === 'invoice' ? '1' : '2';
                return $dateTime . $typePriority;
            })->values();

            // Calculate totals correctly from the processed transaction history
            foreach ($sortedTransactionHistory as $transaction) {
                if ($transaction['type'] === 'invoice') {
                    $totalDebt += $transaction['transaction_amount'];
                } elseif ($transaction['type'] === 'payment') {
                    $totalPaid += $transaction['payment_amount'];
                }
            }
            
            // Calculate available credit memo for the customer
            // AR tracks generated credits in `credit_generated` and applies them via AUTO-CM payments
            $totalCreditGenerated = floatval($accountsReceivableTransactions->sum('credit_generated') ?? 0);

            // Sum all AUTO-CM payments applied across this customer's AR records
            $arIds = $accountsReceivableTransactions->pluck('id');
            $totalAutoCreditApplied = 0;
            if ($arIds && $arIds->count() > 0) {
                $totalAutoCreditApplied = floatval(\App\Models\Payment::whereIn('accounts_receivable_id', $arIds)
                    ->where('reference_number', 'LIKE', 'AUTO-CM-%')
                    ->sum('payment_amount') ?? 0);
            }

            // Available CM = generated credits minus applied credits
            $totalCreditMemo = max(0, $totalCreditGenerated - $totalAutoCreditApplied);
            
            // Calculate balance
            $balance = $totalDebt - $totalPaid;

            // Get credit limit and credit balance from CustomerCredit table
            $customerCredit = CustomerCredit::where('customer_code', $customerCode)->first();
            $creditLimit = $customerCredit ? $customerCredit->credit_limit : ($customer->CreditLimit ?? 0);
            $creditBalance = $customerCredit ? $customerCredit->credit_balance : (($customer->CreditLimit ?? 0) - $balance);

            $result = [
                'customer' => [
                    'code' => $customer->Customer,
                    'name' => $customer->Name,
                    // Use correct tblCustomer columns: 'Contact' and 'Telephone'
                    'contact_person' => $customer->Contact ?? '-',
                    'contact_number' => $customer->Telephone ?? '-'
                ],
                'transactions' => $sortedTransactionHistory,
                'summary' => [
                    'total_debt' => $totalDebt,
                    'total_paid' => $totalPaid,
                    'balance' => $balance,
                    'credit_memo' => $totalCreditMemo,
                    'credit_limit' => $creditLimit,
                    'credit_balance' => $creditBalance
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Customer transactions retrieved successfully',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving customer transactions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print Statement of Account (all transactions)
     */
    public function printStatement($customerCode)
    {
        try {
            // Get customer basic information
            $customer = Customer::where('Customer', $customerCode)->first();
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }

            // Use similar logic as getCustomerTransactions
            $accountsReceivableTransactions = AccountsReceivable::where('customer_code', $customerCode)
                ->with(['payments' => function($query) {
                    $query->with('check')->orderBy('payment_date', 'desc');
                }])
                ->orderBy('date', 'desc')
                ->get();

            // Create a complete transaction history including original transactions and payments
            $transactionHistory = collect();

            foreach ($accountsReceivableTransactions as $arTransaction) {
                $payments = $arTransaction->payments;
                $totalPaidForTransaction = $payments->sum('payment_amount');
                $currentBalance = $arTransaction->total_amount - $totalPaidForTransaction;

                // Separate regular payments from auto credit memo applications
                $autoCreditMemoPayments = $payments->filter(function($payment) {
                    return strpos($payment->reference_number, 'AUTO-CM-') === 0;
                });
                
                $totalAutoCreditMemo = $autoCreditMemoPayments->sum('payment_amount');

                // Add the original AP transaction
                // For auto credit memo, show it as negative paid amount (like customer credit table)
                $invoicePaymentAmount = 0;
                if ($totalAutoCreditMemo > 0) {
                    $invoicePaymentAmount = -$totalAutoCreditMemo; // Show as negative
                }
                
                $transactionHistory->push([
                    'id' => $arTransaction->id,
                    'type' => 'transaction', // Original transaction
                    'date' => $arTransaction->date->format('Y-m-d'),
                    'reference_number' => $arTransaction->reference_number,
                    'so_number' => $arTransaction->so_number,
                    'description' => 'Invoice - ' . ($arTransaction->reference_number ?? 'N/A'),
                    'amount' => $arTransaction->total_amount, // For print template compatibility
                    'paid' => $invoicePaymentAmount, // Show auto credit memo as negative amount
                    'balance' => $arTransaction->total_amount, // Initial balance
                    'status' => $currentBalance > 0 ? 'Pending' : 'Paid',
                    'terms' => $arTransaction->terms,
                    'remarks' => $arTransaction->remarks,
                    'is_overdue' => $arTransaction->is_overdue ?? false,
                    'parent_transaction_id' => $arTransaction->id,
                    'sort_date' => $arTransaction->created_at ? $arTransaction->created_at->format('Y-m-d H:i:s') : $arTransaction->date->format('Y-m-d H:i:s'),
                    'auto_credit_memo' => $totalAutoCreditMemo
                ]);

                // Add individual payment records (excluding auto credit memo applications)
                $regularPayments = $payments->filter(function($payment) {
                    return strpos($payment->reference_number, 'AUTO-CM-') !== 0;
                });
                
                $runningBalance = $arTransaction->total_amount;
                $totalRegularPayments = $regularPayments->sum('payment_amount');
                $hasOverpayment = $totalRegularPayments > $arTransaction->total_amount;
                $overpaymentAmount = $hasOverpayment ? $totalRegularPayments - $arTransaction->total_amount : 0;
                
                foreach ($regularPayments->sortBy('payment_date') as $index => $payment) {
                    $previousBalance = $runningBalance;
                    $runningBalance -= $payment->payment_amount;
                    
                    // Check if this payment creates an overpayment using the same logic as customer credit table
                    $paymentsBeforeThis = $regularPayments->filter(function($p) use ($payment) {
                        return $p->payment_date <= $payment->payment_date && $p->id < $payment->id;
                    });
                    $balanceBeforePayment = $arTransaction->total_amount - $paymentsBeforeThis->sum('payment_amount');
                    $createsOverpayment = $balanceBeforePayment > 0 && ($balanceBeforePayment - $payment->payment_amount) < 0;
                    // For AR, CM is tracked via AUTO-CM payments rather than a CreditMemo field
                    $shouldShowCreditMemo = $createsOverpayment && ($totalAutoCreditMemo > 0);
                    
                    // If this payment creates a credit memo, the running balance should reflect the negative amount
                    $displayBalance = $runningBalance;
                    if ($shouldShowCreditMemo) {
                        // The balance should show the negative credit memo amount
                        $displayBalance = -$overpaymentAmount;
                    }
                    
                    $paymentRecord = [
                        'id' => $payment->id,
                        'type' => 'payment', // Payment record
                        'date' => $payment->payment_date->format('Y-m-d'),
                        'reference_number' => $payment->reference_number ?? $arTransaction->reference_number,
                        'so_number' => $arTransaction->so_number,
                        'description' => 'Payment - ' . ($payment->payment_type === 'bank' && $payment->check_id ? 'Bank Check' : ucfirst($payment->payment_type ?? 'Cash')),
                        'amount' => 0, // For print template compatibility
                        'paid' => $payment->payment_amount, // For print template compatibility
                        'balance' => $displayBalance,
                        'status' => $runningBalance <= 0 ? 'Fully Paid' : 'Partial Payment',
                        'terms' => $arTransaction->terms,
                        'remarks' => $payment->remarks,
                        'is_overdue' => false, // Payments are never overdue
                        'parent_transaction_id' => $arTransaction->id,
                        'sort_date' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : $payment->payment_date->format('Y-m-d H:i:s'),
                        'payment_type' => $payment->payment_type,
                        'process_by' => $payment->process_by
                    ];
                    
                    // Add credit memo information to the payment record if this payment caused an overpayment
                    if ($shouldShowCreditMemo) {
                        $paymentRecord['credit_memo_amount'] = $overpaymentAmount;
                        $paymentRecord['has_credit_memo'] = true;
                        // Keep the actual payment amount for display (e.g., ₱60,000)
                        // Don't modify payment_amount - show what was actually paid
                        $paymentRecord['status'] = 'Fully Paid with CM'; // Special status for payments with credit memo
                        
                        // Update description to include "with CM" and handle Bank Check case
                        $paymentTypeDisplay = $payment->payment_type === 'bank' && $payment->check_id ? 'Bank Check' : ucfirst($payment->payment_type ?? 'Cash');
                        $paymentRecord['description'] = 'Payment - ' . $paymentTypeDisplay . ' with CM';
                    }
                    
                    $transactionHistory->push($paymentRecord);
                }
            }

            // Sort all transactions by date for processing
            $sortedTransactions = $transactionHistory->sortBy('sort_date');

            // Calculate individual invoice balances (same logic as getCustomerTransactions)
            $invoiceBalances = [];
            
            // First, initialize invoice balances and subtract auto credit memos
            foreach ($sortedTransactions as $transaction) {
                if ($transaction['type'] === 'transaction') {
                    $initialBalance = $transaction['amount'];
                    // Subtract auto credit memo if present
                    if (isset($transaction['auto_credit_memo']) && $transaction['auto_credit_memo'] > 0) {
                        $initialBalance -= $transaction['auto_credit_memo'];
                    }
                    $invoiceBalances[$transaction['parent_transaction_id']] = $initialBalance;
                }
            }
            
            // Process transactions and calculate balances per invoice
            $recalculatedHistory = collect();
            foreach ($sortedTransactions as $transaction) {
                if ($transaction['type'] === 'transaction') {
                     // For invoices, show the current balance of that specific invoice
                     $transaction['balance'] = $invoiceBalances[$transaction['parent_transaction_id']];
                     
                     // Update status based on invoice balance (match customer credit table logic)
                     $balance = $transaction['balance'];
                     $hasAutoCreditMemo = isset($transaction['auto_credit_memo']) && $transaction['auto_credit_memo'] > 0;
                     
                     if ($balance <= 0) {
                         // Check if there's a credit memo involved
                         if ($balance < 0) {
                             $transaction['status'] = 'Fully Paid with CM';
                         } else {
                             $transaction['status'] = 'Fully Paid';
                         }
                     } elseif ($balance < $transaction['amount']) {
                         // If there's an auto credit memo but still has balance, show as Partial
                         if ($hasAutoCreditMemo) {
                             $transaction['status'] = 'Partial';
                         } else {
                             $transaction['status'] = 'Partial Payment';
                         }
                     } else {
                         $transaction['status'] = 'Pending';
                     }
                 } elseif ($transaction['type'] === 'payment') {
                     // For payments, subtract from the specific invoice and show the remaining balance
                     $invoiceId = $transaction['parent_transaction_id'];
                     $invoiceBalances[$invoiceId] -= $transaction['paid'];
                     $transaction['balance'] = $invoiceBalances[$invoiceId];
                     
                     // Update payment status based on remaining invoice balance
                     if (isset($transaction['has_credit_memo']) && $transaction['has_credit_memo']) {
                         $transaction['status'] = 'Fully Paid with CM';
                     } else {
                         $transaction['status'] = 'Payment Made';
                     }
                 }
                
                $recalculatedHistory->push($transaction);
            }

            // Group by SO number and sort within each group to maintain invoice-payment grouping
            $transactions = $recalculatedHistory->sortBy(function ($item) {
                // Primary sort: by SO number to group related transactions
                // Secondary sort: by date within each SO group
                // Tertiary sort: invoices before payments within same SO and date
                $soNumber = $item['so_number'] ?? '';
                $dateTime = $item['sort_date'];
                $typePriority = $item['type'] === 'transaction' ? '1' : '2';
                return $soNumber . '|' . $dateTime . '|' . $typePriority;
            })->values();

            $user = auth()->user();
            $totalRecords = $transactions->count();
            return response()->json([
                'success' => true,
                'message' => 'Customer statement data prepared',
                'data' => [
                    'customer' => [
                        'code' => $customer->Customer,
                        'name' => $customer->Name,
                        'contact_person' => $customer->ContactPerson ?? '-',
                        'contact_number' => $customer->ContactNo ?? '-',
                    ],
                    'transactions' => $transactions,
                    'user' => $user ? ['name' => $user->name] : null,
                    'total_records' => $totalRecords,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating statement: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print Counter Receipt (pending transactions only)
     */
    public function printCounterReceipt($customerCode)
    {
        try {
            // Get customer basic information
            $customer = Customer::where('Customer', $customerCode)->first();
            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found'
                ], 404);
            }
            
            // Build timeline for customer (Accounts Receivable)
            $accountsReceivableTransactions = AccountsReceivable::where('customer_code', $customerCode)
                ->with(['payments' => function($query) {
                    $query->with('check')->orderBy('payment_date', 'asc');
                }])
                ->orderBy('date', 'asc')
                ->get();

            $timeline = collect();
            foreach ($accountsReceivableTransactions as $arTransaction) {
                $payments = $arTransaction->payments;
                $totalPaidForTransaction = $payments->sum('payment_amount');
                $currentBalance = $arTransaction->total_amount - $totalPaidForTransaction;
                
                // Only include if not fully paid (balance > 0)
                if ($currentBalance > 0) {
                    // Check for auto credit memo applications
                    $autoCreditMemoPayments = $payments->filter(function($payment) {
                        return strpos($payment->reference_number, 'AUTO-CM-') === 0;
                    });
                    
                    $totalAutoCreditMemo = $autoCreditMemoPayments->sum('payment_amount');
                    
                    // Create the invoice entry with auto credit memo information if applicable
                    $description = 'Invoice - ' . ($arTransaction->reference_number ?? 'N/A');
                    $paid = 0;
                    $status = $currentBalance > 0 ? 'Pending' : 'Paid';
                    
                    if ($totalAutoCreditMemo > 0) {
                        $description .= ' (CM Applied: ₱' . number_format($totalAutoCreditMemo, 2) . ')';
                        $paid = $totalAutoCreditMemo;
                        $status = $currentBalance > 0 ? 'Credit Applied' : 'Fully Paid';
                    }
                    
                    $timeline->push([
                        'type' => 'transaction',
                        'date' => $arTransaction->date,
                        'reference_number' => $arTransaction->reference_number,
                        'so_number' => $arTransaction->so_number,
                        'description' => $description,
                        'amount' => $arTransaction->total_amount,
                        'paid' => $paid,
                        'balance' => $currentBalance,
                        'status' => $status,
                        'terms' => $arTransaction->terms,
                        'is_overdue' => $arTransaction->is_overdue ?? false
                    ]);

                    // Add individual payment records (excluding auto credit memo applications)
                    $runningBalance = $arTransaction->total_amount;
                    foreach ($payments->sortBy('payment_date') as $payment) {
                        // Skip auto credit memo applications as they're already included in the invoice entry
                        if (strpos($payment->reference_number, 'AUTO-CM-') === 0) {
                            $runningBalance -= $payment->payment_amount;
                            continue;
                        }
                        
                        $runningBalance -= $payment->payment_amount;
                        $timeline->push([
                            'type' => 'payment',
                            'date' => $payment->payment_date,
                            'reference_number' => $payment->reference_number ?? $arTransaction->reference_number,
                            'so_number' => $arTransaction->so_number,
                            'description' => 'Payment - ' . ($payment->payment_type === 'bank' && $payment->check_id ? 'Bank Check' : ucfirst($payment->payment_type ?? 'Cash')),
                            'amount' => 0,
                            'paid' => $payment->payment_amount,
                            'balance' => $runningBalance,
                            'status' => $runningBalance <= 0 ? 'Fully Paid' : 'Partial Payment',
                            'terms' => $arTransaction->terms,
                            'is_overdue' => false
                        ]);
                    }
                    // Note: For AR, credit memo is tracked via AUTO-CM payments; explicit CreditMemo field may not exist
                }
            }
            // Apply three-level sorting: 1) SO Number, 2) Date/Time, 3) Type Priority (invoices before payments)
            $sortedTransactionHistory = $timeline->sortBy(function($item) {
                $dateTime = $item['date'] instanceof \Carbon\Carbon ? $item['date']->format('Y-m-d H:i:s') : $item['date'];
                $soNumber = $item['so_number'] ?? 'ZZZZ'; // Put items without SO at the end
                // Type priority: transactions (invoices) first, then payments, then credit memos
                $typePriority = $item['type'] === 'transaction' ? '1' : ($item['type'] === 'payment' ? '2' : '3');
                
                // Three-level sort: RR Number + DateTime + Type Priority
                return $soNumber . '|' . $dateTime . '|' . $typePriority;
            })->values();

            // Calculate individual invoice balances (same logic as customer credit table)
            // Convert collection to array for modification, then back to collection
            $transactionArray = $sortedTransactionHistory->toArray();
            $invoiceBalances = [];
            
            // First, initialize invoice balances and subtract auto credit memos
            foreach ($transactionArray as $index => $item) {
                if ($item['type'] === 'transaction') {
                    $initialBalance = $item['amount'];
                    // Subtract auto credit memo if present
                    if ($item['paid'] > 0) {
                        $initialBalance -= $item['paid'];
                    }
                    $invoiceBalances[$item['rr_number']] = $initialBalance;
                }
            }
            
            // Process transactions and calculate individual balances
            foreach ($transactionArray as $index => $item) {
                if ($item['type'] === 'transaction') {
                    // For invoices, show the current balance of that specific invoice
                    $transactionArray[$index]['balance'] = $invoiceBalances[$item['so_number']];
                    
                    // Update status based on individual invoice balance
                    if ($item['paid'] > 0) {
                        // Has auto credit memo applied
                        $transactionArray[$index]['status'] = $invoiceBalances[$item['rr_number']] > 0 ? 'Credit Applied' : 'Fully Paid';
                    } else {
                        $transactionArray[$index]['status'] = $invoiceBalances[$item['rr_number']] > 0 ? 'Pending' : 'Paid';
                    }
                } elseif ($item['type'] === 'payment') {
                    // For payments, subtract from the specific invoice and show the remaining balance
                    $invoiceBalances[$item['so_number']] -= $item['paid'];
                    $transactionArray[$index]['balance'] = $invoiceBalances[$item['so_number']];
                    
                    // Update status based on individual invoice balance
                    $transactionArray[$index]['status'] = $invoiceBalances[$item['rr_number']] <= 0 ? 'Fully Paid' : 'Partial Payment';
                } elseif ($item['type'] === 'credit_memo') {
                    // Credit memos show negative balance (credit available)
                    $transactionArray[$index]['balance'] = -$item['credit_memo'];
                }
            }
            
            // Convert back to collection
            $sortedTransactionHistory = collect($transactionArray);

            $pendingTransactions = $sortedTransactionHistory;

            $user = auth()->user();
            $totalRecords = $pendingTransactions->count();
            
            return response()->json([
                'success' => true,
                'message' => 'Customer counter receipt data prepared',
                'data' => [
                    'customer' => [
                        'code' => $customer->Customer,
                        'name' => $customer->Name,
                        'contact_person' => $customer->ContactPerson ?? '-',
                        'contact_number' => $customer->ContactNo ?? '-',
                    ],
                    'transactions' => $pendingTransactions,
                    'user' => $user ? ['name' => $user->name] : null,
                    'total_records' => $totalRecords,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating counter receipt: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Apply auto credit memos to subsequent transactions in chronological order
     */
    private function applyAutoCreditMemos($transactionHistory, $customerCode)
    {
        // Get all available credit memos from the AccountsPayable table
        $availableCredits = DB::select("
            SELECT 
                ap.id as accounts_payable_id,
                ap.reference_number,
                ap.CreditMemo as credit_amount,
                ap.created_at as credit_date
            FROM tblAccountsPayable ap
            WHERE ap.customer_code = ? 
            AND ap.CreditMemo > 0
            ORDER BY ap.created_at ASC
        ", [$customerCode]);

        if (empty($availableCredits)) {
            return $transactionHistory;
        }

        // Convert to array for easier manipulation
        $transactionArray = $transactionHistory->toArray();
        
        // Sort by date to ensure chronological order
        usort($transactionArray, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });

        $totalAvailableCredit = 0;
        foreach ($availableCredits as $credit) {
            $totalAvailableCredit += $credit->credit_amount;
        }

        // Create a list of transaction IDs that generated credit memos (should not receive auto credit)
        $creditGeneratingTransactions = [];
        foreach ($availableCredits as $credit) {
            $creditGeneratingTransactions[] = $credit->accounts_payable_id;
        }

        // Apply credit to subsequent transactions (excluding those that generated the credit)
        foreach ($transactionArray as $index => &$transaction) {
            if ($transaction['type'] === 'transaction' && $totalAvailableCredit > 0) {
                // Skip if this transaction generated a credit memo
                if (in_array($transaction['id'], $creditGeneratingTransactions)) {
                    continue;
                }

                $transactionAmount = $transaction['transaction_amount'];
                $currentRunningBalance = $transaction['running_balance'];
                
                // Only apply credit if there's a remaining balance
                if ($currentRunningBalance > 0) {
                    $creditToApply = min($totalAvailableCredit, $currentRunningBalance);
                    
                    if ($creditToApply > 0) {
                        // Apply the credit by reducing the running balance
                        $transaction['running_balance'] = $currentRunningBalance - $creditToApply;
                        $totalAvailableCredit -= $creditToApply;

                        // Update payment_amount to show the credit applied (negative value)
                        $transaction['payment_amount'] = -$creditToApply;

                        // Update description to show credit memo applied
                        $originalDescription = $transaction['description'];
                        if (strpos($originalDescription, 'CM Applied') === false) {
                            $transaction['description'] = $originalDescription . ' (CM Applied: ₱' . number_format($creditToApply, 2) . ')';
                        }

                        // Update status
                        if ($transaction['running_balance'] <= 0) {
                            $transaction['status'] = 'Fully Paid with CM';
                        } else {
                            $transaction['status'] = 'Credit Applied';
                        }
                    }
                }
            }
        }

        return collect($transactionArray);
    }

    /**
     * Get transaction description based on entry type and AP transaction
     */
    private function getTransactionDescription($entry, $apTransaction)
    {
        switch ($entry->transaction_type) {
            case 'invoice':
                return 'Invoice/Bill - ' . ($apTransaction->reference_number ?? 'N/A');
            case 'payment':
                // Get payment details if available
                $payment = \App\Models\Payment::find($entry->payment_id ?? null);
                $paymentType = $payment ? ucfirst($payment->payment_type ?? 'Cash') : 'Cash';
                return 'Payment - ' . $paymentType;
            case 'credit_memo_applied':
                return 'Credit Memo Applied - ' . ($apTransaction->reference_number ?? 'N/A');
            case 'credit_memo_generated':
                return 'Credit Memo Generated - ' . ($apTransaction->reference_number ?? 'N/A');
            default:
                return 'Transaction - ' . ($apTransaction->reference_number ?? 'N/A');
        }
    }

    /**
     * Get transaction status based on running balance entry
     */
    private function getTransactionStatus($entry)
    {
        switch ($entry->transaction_type) {
            case 'invoice':
                if ($entry->running_balance <= 0) {
                    return 'Fully Paid';
                } elseif ($entry->running_balance < $entry->amount) {
                    return 'Partial Payment';
                } else {
                    return 'Pending';
                }
            case 'payment':
                return 'Payment Made';
            case 'credit_memo_applied':
                return 'Credit Applied';
            case 'credit_memo_generated':
                return 'Credit Generated';
            default:
                return 'Processed';
        }
    }

    /**
     * Get transaction status for AccountsPayable transactions (fallback method)
     */
    private function getAPTransactionStatus($apTransaction)
    {
        $totalPaid = $apTransaction->payments()->sum('payment_amount');
        
        if ($totalPaid >= $apTransaction->total_amount) {
            return 'Fully Paid';
        } elseif ($totalPaid > 0) {
            return 'Partial Payment';
        } else {
            return 'Pending';
        }
    }

    /**
     * Update credit data for a specific customer
     */
    public function updateCustomerCredit($customerCode)
    {
        try {
            $success = CustomerCredit::updateCustomerCredit($customerCode);
            
            if ($success) {
                $customerCredit = CustomerCredit::where('customer_code', $customerCode)->first();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Customer credit data updated successfully',
                    'data' => [
                        'CustomerCode' => $customerCredit->customer_code,
                        'CustomerName' => $customerCredit->customer_name,
                        'total_credit' => $customerCredit->total_credit,
                        'total_paid' => $customerCredit->total_paid,
                        'balance' => $customerCredit->balance,
                        'credit_limit' => $customerCredit->credit_limit,
                        'credit_balance' => $customerCredit->credit_balance
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer not found or no data to update'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error updating customer credit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer credit data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}