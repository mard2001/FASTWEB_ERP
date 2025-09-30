<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\AccountsPayable;
use Illuminate\Support\Facades\DB;

class SupplierCreditController extends Controller
{
    /**
     * Get suppliers with their credit summary information.
     */
    public function getSuppliersWithCredits()
    {
        try {
            // Get suppliers with their total credit, paid amounts, and balance
            $suppliers = DB::select("
                SELECT 
                    s.SupplierCode,
                    s.SupplierName,
                    ISNULL(credit_summary.total_credit, 0) as total_credit,
                    ISNULL(payment_summary.total_paid, 0) as total_paid,
                    (ISNULL(credit_summary.total_credit, 0) - ISNULL(payment_summary.total_paid, 0)) as balance
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
                    GROUP BY ap.supplier_code
                ) payment_summary ON s.SupplierCode = payment_summary.supplier_code
                ORDER BY s.SupplierName
            ");

            return response()->json([
                'success' => true,
                'data' => $suppliers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load supplier credit data',
                'error' => $e->getMessage()
            ], 500);
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
                ->with(['payments' => function($query) {
                    $query->orderBy('payment_date', 'desc');
                }])
                ->orderBy('date', 'desc')
                ->get();

            // Calculate total credit/debt owed to supplier
            $totalDebt = $accountsPayableTransactions->sum('total_amount');
            
            // Get total paid from all related payments
            $totalPaid = 0;
            foreach ($accountsPayableTransactions as $transaction) {
                $totalPaid += $transaction->payments()->sum('payment_amount');
            }
            
            // Calculate total credit memo (accumulated overpayments)
            $totalOriginalCreditMemo = $accountsPayableTransactions->sum('CreditMemo') ?? 0;
            
            // Get total applied credit memos (from AUTO-CM- payment records)
            $appliedCreditMemos = \App\Models\Payment::whereHas('accountsPayable', function($query) use ($supplierCode) {
                $query->where('supplier_code', trim($supplierCode));
            })
            ->where('reference_number', 'LIKE', 'AUTO-CM-%')
            ->sum('payment_amount') ?? 0;
            
            // Available credit memo = Original credit memos - Applied credit memos
            $totalCreditMemo = $totalOriginalCreditMemo - $appliedCreditMemos;
            $totalCreditMemo = max(0, $totalCreditMemo); // Ensure non-negative
            
            $balance = $totalDebt - $totalPaid;

            // Create a complete transaction history including original transactions and payments
            $transactionHistory = collect();

            foreach ($accountsPayableTransactions as $apTransaction) {
                $payments = $apTransaction->payments;
                $totalPaidForTransaction = $payments->sum('payment_amount');
                $currentBalance = $apTransaction->total_amount - $totalPaidForTransaction;

                // Check for auto credit memo applications
                $autoCreditMemoPayments = $payments->filter(function($payment) {
                    return strpos($payment->reference_number, 'AUTO-CM-') === 0;
                });
                $autoCreditMemoAmount = $autoCreditMemoPayments->sum('payment_amount');
                
                // Calculate the effective transaction amount after auto credit memo application
                $effectiveTransactionAmount = $apTransaction->total_amount - $autoCreditMemoAmount;
                $hasAutoCreditMemo = $autoCreditMemoAmount > 0;

                // Add the original AP transaction with auto credit memo applied
                $transactionRecord = [
                    'id' => $apTransaction->id,
                    'type' => 'transaction', // Original transaction
                    'date' => $apTransaction->date->format('Y-m-d'),
                    'reference_number' => $apTransaction->reference_number,
                    'rr_number' => $apTransaction->rr_number,
                    'description' => 'Invoice/Bill - ' . ($apTransaction->reference_number ?? 'N/A'),
                    'transaction_amount' => $apTransaction->total_amount, // Original amount
                    'payment_amount' => 0, // Not a payment
                    'running_balance' => $effectiveTransactionAmount, // Balance after auto credit memo
                    'status' => $currentBalance > 0 ? 'Pending' : 'Paid',
                    'terms' => $apTransaction->terms,
                    'remarks' => $apTransaction->remarks,
                    'is_overdue' => $apTransaction->is_overdue ?? false,
                    'parent_transaction_id' => $apTransaction->id,
                    'sort_date' => $apTransaction->created_at ? $apTransaction->created_at->format('Y-m-d H:i:s') : $apTransaction->date->format('Y-m-d H:i:s')
                ];
                
                // Add auto credit memo information if applicable
                if ($hasAutoCreditMemo) {
                    $transactionRecord['auto_credit_memo_amount'] = $autoCreditMemoAmount;
                    $transactionRecord['has_auto_credit_memo'] = true;
                    $transactionRecord['description'] = 'Invoice/Bill - ' . ($apTransaction->reference_number ?? 'N/A') . ' (CM Applied: ₱' . number_format($autoCreditMemoAmount, 2) . ')';
                    $transactionRecord['status'] = $effectiveTransactionAmount > 0 ? 'Credit Applied' : 'Fully Paid by CM';
                    // Set payment_amount to negative for auto credit memo to display with parentheses
                    $transactionRecord['payment_amount'] = -$autoCreditMemoAmount;
                }
                
                $transactionHistory->push($transactionRecord);

                // Add individual payment records (excluding auto credit memo applications)
                $regularPayments = $payments->filter(function($payment) {
                    return strpos($payment->reference_number, 'AUTO-CM-') !== 0;
                });
                
                $runningBalance = $effectiveTransactionAmount; // Start with balance after auto credit memo
                $totalRegularPayments = $regularPayments->sum('payment_amount');
                $hasOverpayment = $totalRegularPayments > $effectiveTransactionAmount;
                $overpaymentAmount = $hasOverpayment ? $totalRegularPayments - $effectiveTransactionAmount : 0;
                
                foreach ($regularPayments->sortBy('payment_date') as $index => $payment) {
                    $previousBalance = $runningBalance;
                    $runningBalance -= $payment->payment_amount;
                    
                    // Check if this payment creates an overpayment (balance goes negative)
                    $createsOverpayment = $previousBalance > 0 && $runningBalance < 0;
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
                        'description' => 'Payment - ' . ucfirst($payment->payment_type ?? 'Cash'),
                        'transaction_amount' => 0, // Not an original transaction
                        'payment_amount' => $payment->payment_amount,
                        'running_balance' => $displayBalance,
                        'status' => $runningBalance <= 0 ? 'Fully Paid' : 'Partial Payment',
                        'terms' => $apTransaction->terms,
                        'remarks' => $payment->remarks,
                        'is_overdue' => false, // Payments are never overdue
                        'parent_transaction_id' => $apTransaction->id,
                        'sort_date' => $payment->created_at ? $payment->created_at->format('Y-m-d H:i:s') : $payment->payment_date->format('Y-m-d H:i:s'),
                        'payment_type' => $payment->payment_type,
                        'process_by' => $payment->process_by,
                        // Debug information
                        'debug_original_payment_amount' => $payment->payment_amount,
                        'debug_previous_balance' => $previousBalance,
                        'debug_creates_overpayment' => $createsOverpayment,
                        'debug_should_show_cm' => $shouldShowCreditMemo
                    ];
                    
                    // Add credit memo information to the payment record if this payment caused an overpayment
                    if ($shouldShowCreditMemo) {
                        $paymentRecord['credit_memo_amount'] = $apTransaction->CreditMemo;
                        $paymentRecord['has_credit_memo'] = true;
                        // Keep the actual payment amount for display (e.g., ₱60,000)
                        // Don't modify payment_amount - show what was actually paid
                        $paymentRecord['status'] = 'Fully Paid with CM'; // Special status for payments with credit memo
                        $paymentRecord['description'] = 'Payment - ' . ucfirst($payment->payment_type ?? 'Cash') . ' with CM';
                    }
                    
                    $transactionHistory->push($paymentRecord);
                }
            }

            // First, sort chronologically (oldest first) to recalculate running balances correctly
            $chronologicalHistory = $transactionHistory->sortBy(function ($item) {
                $dateTime = $item['sort_date'];
                // For same datetime, transactions come before payments
                $typePriority = $item['type'] === 'transaction' ? '1' : '2';
                return $dateTime . $typePriority;
            });

            // Recalculate running balances in chronological order
            $runningBalance = 0;
            $recalculatedHistory = $chronologicalHistory->map(function ($item) use (&$runningBalance) {
                if ($item['type'] === 'transaction') {
                    // For transactions with auto credit memo, handle credit consumption properly
                    if (isset($item['has_auto_credit_memo']) && $item['has_auto_credit_memo']) {
                        // First, consume available credit (if running balance is negative)
                        $availableCredit = $runningBalance < 0 ? abs($runningBalance) : 0;
                        $creditUsed = min($availableCredit, $item['auto_credit_memo_amount']);
                        
                        // Adjust running balance by consuming the credit
                        $runningBalance += $creditUsed;
                        
                        // Add the remaining transaction amount after auto credit memo
                        $remainingTransactionAmount = $item['transaction_amount'] - $item['auto_credit_memo_amount'];
                        $runningBalance += $remainingTransactionAmount;
                        
                        $item['running_balance'] = $runningBalance;
                    } else {
                        // Add the transaction amount to the balance
                        $runningBalance += $item['transaction_amount'];
                        $item['running_balance'] = $runningBalance;
                    }
                } elseif ($item['type'] === 'payment') {
                    // Always subtract the full payment amount from balance
                    // This will show the credit available (negative balance) for future orders
                    $runningBalance -= $item['payment_amount'];
                    
                    // If this payment has a credit memo, ensure the balance shows the negative credit amount
                    if (isset($item['has_credit_memo']) && $item['has_credit_memo'] && isset($item['credit_memo_amount'])) {
                        $item['running_balance'] = -$item['credit_memo_amount'];
                        // Update the running balance to match the credit memo amount for subsequent calculations
                        $runningBalance = -$item['credit_memo_amount'];
                    } else {
                        $item['running_balance'] = $runningBalance;
                    }
                }
                
                // Update status based on current balance
                if ($item['type'] === 'transaction') {
                    if (isset($item['has_auto_credit_memo']) && $item['has_auto_credit_memo']) {
                        // Status is already set during transaction creation
                    } else {
                        $item['status'] = $runningBalance <= 0 ? 'Paid' : ($runningBalance < $item['transaction_amount'] ? 'Partial' : 'Pending');
                    }
                } elseif ($item['type'] === 'payment') {
                    if (isset($item['has_credit_memo']) && $item['has_credit_memo']) {
                        $item['status'] = 'Fully Paid with CM';
                    } else {
                        $item['status'] = $runningBalance <= 0 ? 'Fully Paid' : 'Payment Made';
                    }
                }
                
                return $item;
            });

            // Now sort by date (oldest first) for display
            $sortedTransactionHistory = $recalculatedHistory->sortBy(function ($item) {
                $dateTime = $item['sort_date'];
                // Add a secondary sort: for same datetime, transactions come first, then payments
                $typePriority = $item['type'] === 'transaction' ? '1' : '2';
                return $dateTime . $typePriority;
            })->values();

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
                    $query->orderBy('payment_date', 'desc');
                }])
                ->orderBy('date', 'desc')
                ->get();

            // Create a complete transaction history including original transactions and payments
            $transactionHistory = collect();

            foreach ($accountsPayableTransactions as $apTransaction) {
                $payments = $apTransaction->payments;
                $totalPaidForTransaction = $payments->sum('payment_amount');
                $currentBalance = $apTransaction->total_amount - $totalPaidForTransaction;

                // Add the original AP transaction
                $transactionHistory->push([
                    'id' => $apTransaction->id,
                    'type' => 'transaction', // Original transaction
                    'date' => $apTransaction->date->format('Y-m-d'),
                    'reference_number' => $apTransaction->reference_number,
                    'rr_number' => $apTransaction->rr_number,
                    'description' => 'Invoice/Bill - ' . ($apTransaction->reference_number ?? 'N/A'),
                    'amount' => $apTransaction->total_amount, // For print template compatibility
                    'paid' => 0, // For print template compatibility
                    'balance' => $apTransaction->total_amount, // Initial balance
                    'status' => $currentBalance > 0 ? 'Pending' : 'Paid',
                    'terms' => $apTransaction->terms,
                    'remarks' => $apTransaction->remarks,
                    'is_overdue' => $apTransaction->is_overdue ?? false,
                    'parent_transaction_id' => $apTransaction->id,
                    'sort_date' => $apTransaction->created_at ? $apTransaction->created_at->format('Y-m-d H:i:s') : $apTransaction->date->format('Y-m-d H:i:s')
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
                    
                    // Check if this payment creates an overpayment (balance goes negative)
                    $createsOverpayment = $previousBalance > 0 && $runningBalance < 0;
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
                        'description' => 'Payment - ' . ucfirst($payment->payment_type ?? 'Cash'),
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
                        $paymentRecord['description'] = 'Payment - ' . ucfirst($payment->payment_type ?? 'Cash') . ' with CM';
                    }
                    
                    $transactionHistory->push($paymentRecord);
                }
            }

            // First, sort chronologically (oldest first) to recalculate running balances correctly
            $chronologicalHistory = $transactionHistory->sortBy(function ($item) {
                $dateTime = $item['sort_date'];
                // For same datetime, transactions come before payments
                $typePriority = $item['type'] === 'transaction' ? '1' : '2';
                return $dateTime . $typePriority;
            });

            // Recalculate running balances in chronological order
            $runningBalance = 0;
            $recalculatedHistory = $chronologicalHistory->map(function ($item) use (&$runningBalance) {
                if ($item['type'] === 'transaction') {
                    // Add the transaction amount to the balance
                    $runningBalance += $item['amount'];
                    // Update the running balance for this item
                    $item['balance'] = $runningBalance;
                } elseif ($item['type'] === 'payment') {
                    // Always subtract the full payment amount from balance
                    // This will show the credit available (negative balance) for future orders
                    $runningBalance -= $item['paid'];
                    
                    // If this payment has a credit memo, ensure the balance shows the negative credit amount
                    if (isset($item['has_credit_memo']) && $item['has_credit_memo'] && isset($item['credit_memo_amount'])) {
                        $item['balance'] = -$item['credit_memo_amount'];
                        // Update the running balance to match the credit memo amount for subsequent calculations
                        $runningBalance = -$item['credit_memo_amount'];
                    } else {
                        $item['balance'] = $runningBalance;
                    }
                }
                
                // Update status based on current balance
                if ($item['type'] === 'transaction') {
                    $item['status'] = $runningBalance <= 0 ? 'Paid' : ($runningBalance < $item['amount'] ? 'Partial' : 'Pending');
                } elseif ($item['type'] === 'payment') {
                    if (isset($item['has_credit_memo']) && $item['has_credit_memo']) {
                        $item['status'] = 'Fully Paid with CM';
                    } else {
                        $item['status'] = $runningBalance <= 0 ? 'Fully Paid' : 'Payment Made';
                    }
                }
                
                return $item;
            });

            // Now sort by date (oldest first) for display - same as getSupplierTransactions
            $transactions = $recalculatedHistory->sortBy(function ($item) {
                $dateTime = $item['sort_date'];
                // Add a secondary sort: for same datetime, transactions come first, then payments
                $typePriority = $item['type'] === 'transaction' ? '1' : '2';
                return $dateTime . $typePriority;
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
                    $query->orderBy('payment_date', 'desc');
                }])
                ->orderBy('date', 'desc')
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
                            'description' => 'Payment - ' . ucfirst($payment->payment_type ?? 'Cash'),
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
            // Sort chronologically first to recalculate running balances correctly
            $sortedTransactionHistory = $timeline->sortBy(function($item) {
                $dateTime = $item['date'] instanceof \Carbon\Carbon ? $item['date']->format('Y-m-d H:i:s') : $item['date'];
                // Add a secondary sort: for same datetime, transactions come first, then payments, then credit memos
                $typePriority = $item['type'] === 'transaction' ? '1' : ($item['type'] === 'payment' ? '2' : '3');
                return $dateTime . $typePriority;
            })->values();

            // Recalculate running balances and update statuses
            // Convert collection to array for modification, then back to collection
            $transactionArray = $sortedTransactionHistory->toArray();
            $runningBalance = 0;
            
            foreach ($transactionArray as $index => $item) {
                if ($item['type'] === 'transaction') {
                    $runningBalance += $item['amount'];
                    // Subtract any auto credit memo applications that were already included
                    if ($item['paid'] > 0) {
                        $runningBalance -= $item['paid'];
                    }
                } elseif ($item['type'] === 'payment') {
                    $runningBalance -= $item['paid'];
                }
                // Credit memos don't change the running balance as they represent available credit

                // Update the balance for transactions and payments
                if ($item['type'] !== 'credit_memo') {
                    $transactionArray[$index]['balance'] = $runningBalance;
                }
                
                // Update status based on current running balance
                if ($item['type'] === 'transaction') {
                    if ($item['paid'] > 0) {
                        // Has auto credit memo applied
                        $transactionArray[$index]['status'] = $runningBalance > 0 ? 'Credit Applied' : 'Fully Paid';
                    } else {
                        $transactionArray[$index]['status'] = $runningBalance > 0 ? 'Pending' : 'Paid';
                    }
                } elseif ($item['type'] === 'payment') {
                    $transactionArray[$index]['status'] = $runningBalance <= 0 ? 'Fully Paid' : 'Partial Payment';
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
}