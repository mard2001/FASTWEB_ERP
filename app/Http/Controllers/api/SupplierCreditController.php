<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
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
                ->with([
                    'payments' => function($query) {
                        $query->orderBy('payment_date', 'desc');
                    }
                ])
                ->orderBy('date', 'desc')
                ->get();

            // Calculate totals
            $totalDebt = $accountsPayableTransactions->sum('total_amount');
            $totalPaid = $accountsPayableTransactions->sum(function($transaction) {
                return $transaction->payments()->sum('payment_amount');
            });
            
            // Calculate balance
            $balance = $totalDebt - $totalPaid;

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
                        
                        $paymentDescription = 'Payment - ' . ucfirst($payment->payment_type ?? 'Cash');
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
                            'has_credit_memo' => $createsOverpayment && $apTransaction->CreditMemo && $apTransaction->CreditMemo > 0,
                            'credit_memo_amount' => $createsOverpayment ? $apTransaction->CreditMemo : 0,
                            'status' => 'Payment Made',
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
                    'credit_memo' => 0
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
}