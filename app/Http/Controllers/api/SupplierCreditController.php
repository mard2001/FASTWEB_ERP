<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\SupplierCredit;
use App\Models\AccountsPayable;
use App\Models\CreditMemoApplication;
use App\Models\SupplierRunningBalance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierCreditController extends Controller
{
    /**
     * Get suppliers with their credit summary information.
     */
    public function getSuppliersWithCredits()
    {
        try {
            // First, try to get data from the new SupplierCredit table with supplier relationship
            $suppliers = SupplierCredit::with('supplier')->orderBy('supplier_name')->get();
            
            // If no data exists in the table, refresh it from the source data
            if ($suppliers->isEmpty()) {
                Log::info('SupplierCredit table is empty, refreshing data...');
                SupplierCredit::refreshAllSupplierCredits();
                $suppliers = SupplierCredit::with('supplier')->orderBy('supplier_name')->get();
            }
            
            // Convert to array format expected by the frontend
            $suppliersArray = $suppliers->map(function ($supplier) {
                return [
                    'SupplierCode' => $supplier->supplier_code,
                    'SupplierName' => $supplier->supplier_name,
                    'total_credit' => $supplier->total_credit,
                    'total_paid' => $supplier->total_paid,
                    'balance' => $supplier->balance,
                    'credit_limit' => $supplier->credit_limit,
                    'credit_balance' => $supplier->credit_balance
                ];
            })->toArray();

            return response()->json([
                'success' => true,
                'data' => $suppliersArray
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getSuppliersWithCredits: ' . $e->getMessage());
            
            // Fallback to original query if there's an issue with the new table
            try {
                $suppliers = DB::select("
                    SELECT 
                        s.SupplierCode,
                        s.SupplierName,
                        s.CreditLimit as credit_limit,
                        ISNULL(credit_summary.total_credit, 0) as total_credit,
                        ISNULL(payment_summary.total_paid, 0) as total_paid,
                        (ISNULL(credit_summary.total_credit, 0) - ISNULL(payment_summary.total_paid, 0)) as balance,
                        (s.CreditLimit - (ISNULL(credit_summary.total_credit, 0) - ISNULL(payment_summary.total_paid, 0))) as credit_balance
                    FROM tblSupplier s
                    LEFT JOIN (
                        SELECT 
                            supplier_code,
                            SUM(total_amount) as total_credit
                        FROM tblAccountsPayable 
                        GROUP BY supplier_code
                    ) credit_summary ON s.SupplierCode = credit_summary.supplier_code
                    LEFT JOIN (
                        SELECT 
                            ap.supplier_code,
                            SUM(p.payment_amount) as total_paid
                        FROM tblAccountsPayable ap
                        INNER JOIN tblPayments p ON ap.id = p.accounts_payable_id
                        WHERE p.reference_number NOT LIKE 'AUTO-CM-%' OR p.reference_number IS NULL
                        GROUP BY ap.supplier_code
                    ) payment_summary ON s.SupplierCode = payment_summary.supplier_code
                    ORDER BY s.SupplierName
                ");

                return response()->json([
                    'success' => true,
                    'data' => $suppliers
                ]);
            } catch (\Exception $fallbackError) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load supplier credit data',
                    'error' => $fallbackError->getMessage()
                ], 500);
            }
        }
    }

    /**
     * Get supplier transactions and credit details
     */
    public function getSupplierTransactions($supplierCode)
    {
        try {
            // Get supplier basic information
            $supplier = Supplier::where('SupplierCode', $supplierCode)->first();
            
            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier not found'
                ], 404);
            }

            // Get accounts payable transactions for this supplier with payments
            $accountsPayableTransactions = AccountsPayable::where('supplier_code', $supplierCode)
                ->with([
                    'payments' => function($query) {
                        $query->with('check')->orderBy('payment_date', 'desc');
                    }
                ])
                ->orderBy('date', 'desc')
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
                foreach ($accountsPayableTransactions as $apTransaction) {
                    // Separate regular payments from auto credit memo applications
                    $regularPayments = $apTransaction->payments->filter(function($payment) {
                        return strpos($payment->reference_number, 'AUTO-CM-') !== 0;
                    });
                    
                    $autoCreditMemoPayments = $apTransaction->payments->filter(function($payment) {
                        return strpos($payment->reference_number, 'AUTO-CM-') === 0;
                    });
                    
                    $totalAutoCreditMemo = $autoCreditMemoPayments->sum('payment_amount');
                    
                    // Create invoice record with auto credit memo information if applicable
                    $invoiceDescription = 'Invoice/Bill - ' . ($apTransaction->reference_number ?? 'N/A');
                    $invoicePaymentAmount = 0;
                    
                    if ($totalAutoCreditMemo > 0) {
                        $invoicePaymentAmount = $totalAutoCreditMemo;
                    }
                    
                    $allTransactions->push([
                        'id' => $apTransaction->id,
                        'ap_transaction_id' => $apTransaction->id,
                        'type' => 'invoice',
                        'date' => $apTransaction->date->format('Y-m-d'),
                        'reference_number' => $apTransaction->reference_number,
                        'rr_number' => $apTransaction->rr_number,
                        'description' => $invoiceDescription,
                        'transaction_amount' => $apTransaction->total_amount,
                        'payment_amount' => $invoicePaymentAmount,
                        'auto_credit_memo' => $totalAutoCreditMemo,
                        'status' => $this->getAPTransactionStatus($apTransaction),
                        'terms' => $apTransaction->terms,
                        'remarks' => $apTransaction->remarks,
                        'is_overdue' => $apTransaction->is_overdue ?? false,
                        'parent_transaction_id' => $apTransaction->id,
                        'sort_date' => $apTransaction->created_at ? $apTransaction->created_at->format('Y-m-d H:i:s') : $apTransaction->date->format('Y-m-d H:i:s')
                    ]);

                    // Add regular payment records (excluding auto credit memo applications)
                    foreach ($regularPayments as $payment) {
                        // Check if this payment creates an overpayment
                        $totalRegularPayments = $regularPayments->sum('payment_amount');
                        $invoiceBalance = $apTransaction->total_amount - $totalAutoCreditMemo;
                        $hasOverpayment = $totalRegularPayments > $invoiceBalance;
                        
                        // Determine if this specific payment creates the overpayment
                        $paymentsBeforeThis = $regularPayments->filter(function($p) use ($payment) {
                            return $p->payment_date <= $payment->payment_date && $p->id < $payment->id;
                        });
                        $balanceBeforePayment = $invoiceBalance - $paymentsBeforeThis->sum('payment_amount');
                        $createsOverpayment = $balanceBeforePayment > 0 && ($balanceBeforePayment - $payment->payment_amount) < 0;
                        
                        // Determine payment type display text
                        $paymentTypeDisplay = ucfirst($payment->payment_type ?? 'Cash');
                        if ($payment->payment_type === 'bank' && $payment->check_id) {
                            $paymentTypeDisplay = 'Bank Check';
                        }
                        
                        $paymentDescription = 'Payment - ' . $paymentTypeDisplay;
                        if ($createsOverpayment && $apTransaction->CreditMemo && $apTransaction->CreditMemo > 0) {
                            $paymentDescription .= ' with CM';
                        }
                        
                        $allTransactions->push([
                            'id' => 'payment_' . $payment->id,
                            'ap_transaction_id' => $apTransaction->id,
                            'type' => 'payment',
                            'date' => $payment->payment_date->format('Y-m-d'),
                            'reference_number' => $payment->reference_number ?? $apTransaction->reference_number,
                            'rr_number' => $apTransaction->rr_number,
                            'description' => $paymentDescription,
                            'transaction_amount' => 0,
                            'payment_amount' => $payment->payment_amount,
                            'payment_type' => $paymentTypeDisplay,
                            'has_credit_memo' => $createsOverpayment && $apTransaction->CreditMemo && $apTransaction->CreditMemo > 0,
                            'credit_memo_amount' => $createsOverpayment ? $apTransaction->CreditMemo : 0,
                            'status' => ($createsOverpayment && $apTransaction->CreditMemo && $apTransaction->CreditMemo > 0) ? 'Fully Paid with CM' : 'Payment Made',
                            'terms' => $apTransaction->terms,
                            'remarks' => $payment->remarks ?? $apTransaction->remarks,
                            'is_overdue' => false,
                            'parent_transaction_id' => $apTransaction->id,
                            'sort_date' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : $payment->payment_date->format('Y-m-d H:i:s')
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
                        $invoiceBalances[$transaction['ap_transaction_id']] = $initialBalance;
                    }
                }
                
                // Process transactions and calculate balances
                foreach ($sortedTransactions as $transaction) {
                    if ($transaction['type'] === 'invoice') {
                        // For invoices, show the current balance of that specific invoice
                        $transaction['running_balance'] = $invoiceBalances[$transaction['ap_transaction_id']];
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
            
            // Calculate available credit memo
            $totalCreditMemo = $accountsPayableTransactions->sum('CreditMemo') ?? 0;
            
            // Calculate balance
            $balance = $totalDebt - $totalPaid;

            $result = [
                'supplier' => [
                    'code' => $supplier->SupplierCode,
                    'name' => $supplier->SupplierName,
                    'contact_person' => $supplier->ContactPerson ?? '-',
                    'contact_number' => $supplier->ContactNo ?? '-'
                ],
                'transactions' => $sortedTransactionHistory,
                'summary' => [
                    'total_debt' => $totalDebt,
                    'total_paid' => $totalPaid,
                    'balance' => $balance,
                    'credit_memo' => $totalCreditMemo
                ]
            ];

            return response()->json([
                'success' => true,
                'message' => 'Supplier transactions retrieved successfully',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving supplier transactions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Print Statement of Account (all transactions)
     */
    public function printStatement($supplierCode)
    {
        try {
            // Get supplier basic information
            $supplier = Supplier::where('SupplierCode', $supplierCode)->first();
            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier not found'
                ], 404);
            }

            // Use the exact same logic as getSupplierTransactions
            $accountsPayableTransactions = AccountsPayable::where('supplier_code', $supplierCode)
                ->with(['payments' => function($query) {
                    $query->with('check')->orderBy('payment_date', 'desc');
                }])
                ->orderBy('date', 'desc')
                ->get();

            // Create a complete transaction history including original transactions and payments
            $transactionHistory = collect();

            foreach ($accountsPayableTransactions as $apTransaction) {
                $payments = $apTransaction->payments;
                $totalPaidForTransaction = $payments->sum('payment_amount');
                $currentBalance = $apTransaction->total_amount - $totalPaidForTransaction;

                // Separate regular payments from auto credit memo applications
                $autoCreditMemoPayments = $payments->filter(function($payment) {
                    return strpos($payment->reference_number, 'AUTO-CM-') === 0;
                });
                
                $totalAutoCreditMemo = $autoCreditMemoPayments->sum('payment_amount');

                // Add the original AP transaction
                // For auto credit memo, show it as negative paid amount (like supplier credit table)
                $invoicePaymentAmount = 0;
                if ($totalAutoCreditMemo > 0) {
                    $invoicePaymentAmount = -$totalAutoCreditMemo; // Show as negative
                }
                
                $transactionHistory->push([
                    'id' => $apTransaction->id,
                    'type' => 'transaction', // Original transaction
                    'date' => $apTransaction->date->format('Y-m-d'),
                    'reference_number' => $apTransaction->reference_number,
                    'rr_number' => $apTransaction->rr_number,
                    'description' => 'Invoice/Bill - ' . ($apTransaction->reference_number ?? 'N/A'),
                    'amount' => $apTransaction->total_amount, // For print template compatibility
                    'paid' => $invoicePaymentAmount, // Show auto credit memo as negative amount
                    'balance' => $apTransaction->total_amount, // Initial balance
                    'status' => $currentBalance > 0 ? 'Pending' : 'Paid',
                    'terms' => $apTransaction->terms,
                    'remarks' => $apTransaction->remarks,
                    'is_overdue' => $apTransaction->is_overdue ?? false,
                    'parent_transaction_id' => $apTransaction->id,
                    'sort_date' => $apTransaction->created_at ? $apTransaction->created_at->format('Y-m-d H:i:s') : $apTransaction->date->format('Y-m-d H:i:s'),
                    'auto_credit_memo' => $totalAutoCreditMemo
                ]);

                // Add individual payment records (excluding auto credit memo applications)
                $regularPayments = $payments->filter(function($payment) {
                    return strpos($payment->reference_number, 'AUTO-CM-') !== 0;
                });
                
                $runningBalance = $apTransaction->total_amount;
                $totalRegularPayments = $regularPayments->sum('payment_amount');
                $hasOverpayment = $totalRegularPayments > $apTransaction->total_amount;
                $overpaymentAmount = $hasOverpayment ? $totalRegularPayments - $apTransaction->total_amount : 0;
                
                foreach ($regularPayments->sortBy('payment_date') as $index => $payment) {
                    $previousBalance = $runningBalance;
                    $runningBalance -= $payment->payment_amount;
                    
                    // Check if this payment creates an overpayment using the same logic as supplier credit table
                    $paymentsBeforeThis = $regularPayments->filter(function($p) use ($payment) {
                        return $p->payment_date <= $payment->payment_date && $p->id < $payment->id;
                    });
                    $balanceBeforePayment = $apTransaction->total_amount - $paymentsBeforeThis->sum('payment_amount');
                    $createsOverpayment = $balanceBeforePayment > 0 && ($balanceBeforePayment - $payment->payment_amount) < 0;
                    $shouldShowCreditMemo = $createsOverpayment && $apTransaction->CreditMemo && $apTransaction->CreditMemo > 0;
                    
                    // If this payment creates a credit memo, the running balance should reflect the negative amount
                    $displayBalance = $runningBalance;
                    if ($shouldShowCreditMemo) {
                        // The balance should show the negative credit memo amount
                        $displayBalance = -$apTransaction->CreditMemo;
                    }
                    
                    $paymentRecord = [
                        'id' => $payment->id,
                        'type' => 'payment', // Payment record
                        'date' => $payment->payment_date->format('Y-m-d'),
                        'reference_number' => $payment->reference_number ?? $apTransaction->reference_number,
                        'rr_number' => $apTransaction->rr_number,
                        'description' => 'Payment - ' . ($payment->payment_type === 'bank' && $payment->check_id ? 'Bank Check' : ucfirst($payment->payment_type ?? 'Cash')),
                        'amount' => 0, // For print template compatibility
                        'paid' => $payment->payment_amount, // For print template compatibility
                        'balance' => $displayBalance,
                        'status' => $runningBalance <= 0 ? 'Fully Paid' : 'Partial Payment',
                        'terms' => $apTransaction->terms,
                        'remarks' => $payment->remarks,
                        'is_overdue' => false, // Payments are never overdue
                        'parent_transaction_id' => $apTransaction->id,
                        'sort_date' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : $payment->payment_date->format('Y-m-d H:i:s'),
                        'payment_type' => $payment->payment_type,
                        'process_by' => $payment->process_by
                    ];
                    
                    // Add credit memo information to the payment record if this payment caused an overpayment
                    if ($shouldShowCreditMemo) {
                        $paymentRecord['credit_memo_amount'] = $apTransaction->CreditMemo;
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

            // Calculate individual invoice balances (same logic as getSupplierTransactions)
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
                     
                     // Update status based on invoice balance (match supplier credit table logic)
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

            // Group by RR number and sort within each group to maintain invoice-payment grouping
            $transactions = $recalculatedHistory->sortBy(function ($item) {
                // Primary sort: by RR number to group related transactions
                // Secondary sort: by date within each RR group
                // Tertiary sort: invoices before payments within same RR and date
                $rrNumber = $item['rr_number'] ?? '';
                $dateTime = $item['sort_date'];
                $typePriority = $item['type'] === 'transaction' ? '1' : '2';
                return $rrNumber . '|' . $dateTime . '|' . $typePriority;
            })->values();

            $user = auth()->user();
            $totalRecords = $transactions->count();
            return view('Pages.Printing.supplier_statement_print', compact('supplier', 'transactions', 'user', 'totalRecords'));
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
    public function printCounterReceipt($supplierCode)
    {
        try {
            // Get supplier basic information
            $supplier = Supplier::where('SupplierCode', $supplierCode)->first();
            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier not found'
                ], 404);
            }

            // Build full timeline with same logic as statement but filter for non-fully-paid invoices
            $accountsPayableTransactions = AccountsPayable::where('supplier_code', $supplierCode)
                ->with(['payments' => function($query) {
                    $query->with('check')->orderBy('payment_date', 'asc');
                }])
                ->orderBy('date', 'asc')
                ->get();

            $timeline = collect();
            foreach ($accountsPayableTransactions as $apTransaction) {
                $payments = $apTransaction->payments;
                $totalPaidForTransaction = $payments->sum('payment_amount');
                $currentBalance = $apTransaction->total_amount - $totalPaidForTransaction;
                
                // Only include if not fully paid (balance > 0)
                if ($currentBalance > 0) {
                    // Check for auto credit memo applications
                    $autoCreditMemoPayments = $payments->filter(function($payment) {
                        return strpos($payment->reference_number, 'AUTO-CM-') === 0;
                    });
                    
                    $totalAutoCreditMemo = $autoCreditMemoPayments->sum('payment_amount');
                    
                    // Create the invoice entry with auto credit memo information if applicable
                    $description = 'Invoice/Bill - ' . ($apTransaction->reference_number ?? 'N/A');
                    $paid = 0;
                    $status = $currentBalance > 0 ? 'Pending' : 'Paid';
                    
                    if ($totalAutoCreditMemo > 0) {
                        $description .= ' (CM Applied: ₱' . number_format($totalAutoCreditMemo, 2) . ')';
                        $paid = $totalAutoCreditMemo;
                        $status = $currentBalance > 0 ? 'Credit Applied' : 'Fully Paid';
                    }
                    
                    $timeline->push([
                        'type' => 'transaction',
                        'date' => $apTransaction->date,
                        'reference_number' => $apTransaction->reference_number,
                        'rr_number' => $apTransaction->rr_number,
                        'description' => $description,
                        'amount' => $apTransaction->total_amount,
                        'paid' => $paid,
                        'balance' => $currentBalance,
                        'status' => $status,
                        'terms' => $apTransaction->terms,
                        'is_overdue' => $apTransaction->is_overdue ?? false
                    ]);

                    // Add individual payment records (excluding auto credit memo applications)
                    $runningBalance = $apTransaction->total_amount;
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
                            'reference_number' => $payment->reference_number ?? $apTransaction->reference_number,
                            'rr_number' => $apTransaction->rr_number,
                            'description' => 'Payment - ' . ($payment->payment_type === 'bank' && $payment->check_id ? 'Bank Check' : ucfirst($payment->payment_type ?? 'Cash')),
                            'amount' => 0,
                            'paid' => $payment->payment_amount,
                            'balance' => $runningBalance,
                            'status' => $runningBalance <= 0 ? 'Fully Paid' : 'Partial Payment',
                            'terms' => $apTransaction->terms,
                            'is_overdue' => false
                        ]);
                    }

                    // Add credit memo entry if there's an overpayment for this transaction
                    if ($apTransaction->CreditMemo && $apTransaction->CreditMemo > 0) {
                        $timeline->push([
                            'type' => 'credit_memo',
                            'date' => $apTransaction->updated_at ?? $apTransaction->date,
                            'reference_number' => $apTransaction->reference_number,
                            'rr_number' => $apTransaction->rr_number,
                            'description' => 'Credit Memo - Overpayment',
                            'amount' => 0,
                            'paid' => 0,
                            'credit_memo' => $apTransaction->CreditMemo,
                            'balance' => -$apTransaction->CreditMemo, // Negative balance to show credit available
                            'status' => 'Credit Available',
                            'terms' => $apTransaction->terms,
                            'is_overdue' => false
                        ]);
                    }
                }
            }
            // Apply three-level sorting: 1) RR Number, 2) Date/Time, 3) Type Priority (invoices before payments)
            $sortedTransactionHistory = $timeline->sortBy(function($item) {
                $dateTime = $item['date'] instanceof \Carbon\Carbon ? $item['date']->format('Y-m-d H:i:s') : $item['date'];
                $rrNumber = $item['rr_number'] ?? 'ZZZZ'; // Put items without RR at the end
                // Type priority: transactions (invoices) first, then payments, then credit memos
                $typePriority = $item['type'] === 'transaction' ? '1' : ($item['type'] === 'payment' ? '2' : '3');
                
                // Three-level sort: RR Number + DateTime + Type Priority
                return $rrNumber . '|' . $dateTime . '|' . $typePriority;
            })->values();

            // Calculate individual invoice balances (same logic as supplier credit table)
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
                    $transactionArray[$index]['balance'] = $invoiceBalances[$item['rr_number']];
                    
                    // Update status based on individual invoice balance
                    if ($item['paid'] > 0) {
                        // Has auto credit memo applied
                        $transactionArray[$index]['status'] = $invoiceBalances[$item['rr_number']] > 0 ? 'Credit Applied' : 'Fully Paid';
                    } else {
                        $transactionArray[$index]['status'] = $invoiceBalances[$item['rr_number']] > 0 ? 'Pending' : 'Paid';
                    }
                } elseif ($item['type'] === 'payment') {
                    // For payments, subtract from the specific invoice and show the remaining balance
                    $invoiceBalances[$item['rr_number']] -= $item['paid'];
                    $transactionArray[$index]['balance'] = $invoiceBalances[$item['rr_number']];
                    
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
            
            // Always pass the collection, even if empty - the template will handle the display
            return view('Pages.Printing.supplier_counter_receipt_print', compact('supplier', 'pendingTransactions', 'user', 'totalRecords'));
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
    private function applyAutoCreditMemos($transactionHistory, $supplierCode)
    {
        // Get all available credit memos from the AccountsPayable table
        $availableCredits = DB::select("
            SELECT 
                ap.id as accounts_payable_id,
                ap.reference_number,
                ap.CreditMemo as credit_amount,
                ap.created_at as credit_date
            FROM tblAccountsPayable ap
            WHERE ap.supplier_code = ? 
            AND ap.CreditMemo > 0
            ORDER BY ap.created_at ASC
        ", [$supplierCode]);

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
     * Refresh all supplier credit data in the table
     */
    public function refreshSupplierCredits()
    {
        try {
            $count = SupplierCredit::refreshAllSupplierCredits();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully refreshed {$count} supplier credit records",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error refreshing supplier credits: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh supplier credit data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update credit data for a specific supplier
     */
    public function updateSupplierCredit($supplierCode)
    {
        try {
            $success = SupplierCredit::updateSupplierCredit($supplierCode);
            
            if ($success) {
                $supplierCredit = SupplierCredit::where('supplier_code', $supplierCode)->first();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Supplier credit data updated successfully',
                    'data' => [
                        'SupplierCode' => $supplierCredit->supplier_code,
                        'SupplierName' => $supplierCredit->supplier_name,
                        'total_credit' => $supplierCredit->total_credit,
                        'total_paid' => $supplierCredit->total_paid,
                        'balance' => $supplierCredit->balance,
                        'credit_limit' => $supplierCredit->credit_limit,
                        'credit_balance' => $supplierCredit->credit_balance
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier not found or no data to update'
                ], 404);
            }
        } catch (\Exception $e) {
            Log::error('Error updating supplier credit: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update supplier credit data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}