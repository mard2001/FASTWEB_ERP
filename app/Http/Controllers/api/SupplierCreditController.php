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
            
            $balance = $totalDebt - $totalPaid;

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
                    'transaction_amount' => $apTransaction->total_amount, // Original amount
                    'payment_amount' => 0, // Not a payment
                    'running_balance' => $apTransaction->total_amount, // Initial balance
                    'status' => $currentBalance > 0 ? 'Pending' : 'Paid',
                    'terms' => $apTransaction->terms,
                    'remarks' => $apTransaction->remarks,
                    'is_overdue' => $apTransaction->is_overdue ?? false,
                    'parent_transaction_id' => $apTransaction->id,
                    'sort_date' => $apTransaction->date->format('Y-m-d H:i:s')
                ]);

                // Add individual payment records
                $runningBalance = $apTransaction->total_amount;
                foreach ($payments->sortBy('payment_date') as $payment) {
                    $runningBalance -= $payment->payment_amount;
                    
                    $transactionHistory->push([
                        'id' => $payment->id,
                        'type' => 'payment', // Payment record
                        'date' => $payment->payment_date->format('Y-m-d'),
                        'reference_number' => $payment->reference_number ?? $apTransaction->reference_number,
                        'rr_number' => $apTransaction->rr_number,
                        'description' => 'Payment - ' . ucfirst($payment->payment_type ?? 'Cash'),
                        'transaction_amount' => 0, // Not an original transaction
                        'payment_amount' => $payment->payment_amount,
                        'running_balance' => $runningBalance,
                        'status' => $runningBalance <= 0 ? 'Fully Paid' : 'Partial Payment',
                        'terms' => $apTransaction->terms,
                        'remarks' => $payment->remarks,
                        'is_overdue' => false, // Payments are never overdue
                        'parent_transaction_id' => $apTransaction->id,
                        'sort_date' => $payment->payment_date->format('Y-m-d H:i:s'),
                        'payment_type' => $payment->payment_type,
                        'process_by' => $payment->process_by
                    ]);
                }
            }

            // Sort by date (most recent first) and then by type (transactions first, then payments)
            $sortedTransactionHistory = $transactionHistory->sortByDesc(function ($item) {
                return $item['sort_date'] . ($item['type'] === 'transaction' ? '0' : '1');
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
                    'balance' => $balance
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

            // Get all transactions for this supplier
            $transactions = AccountsPayable::where('supplier_code', $supplierCode)
                ->orderBy('date', 'desc')
                ->get()
                ->map(function($transaction) {
                    $totalPaid = $transaction->payments()->sum('payment_amount');
                    return (object)[
                        'date' => $transaction->date,
                        'reference_number' => $transaction->reference_number,
                        'rr_number' => $transaction->rr_number,
                        'total_amount' => $transaction->total_amount,
                        'total_paid' => $totalPaid,
                        'balance' => $transaction->total_amount - $totalPaid,
                        'status' => $transaction->status,
                        'terms' => $transaction->terms,
                        'is_overdue' => $transaction->is_overdue ?? false
                    ];
                });

            $user = auth()->user();

            return view('Pages.Printing.supplier_statement_print', compact('supplier', 'transactions', 'user'));

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

            // Get only pending/unpaid transactions for this supplier
            $allTransactions = AccountsPayable::where('supplier_code', $supplierCode)
                ->orderBy('date', 'desc')
                ->get();

            $pendingTransactions = collect();
            
            foreach ($allTransactions as $transaction) {
                $totalPaid = $transaction->payments()->sum('payment_amount');
                $balance = $transaction->total_amount - $totalPaid;
                
                // Only include transactions with remaining balance
                if ($balance > 0) {
                    $pendingTransactions->push((object)[
                        'date' => $transaction->date,
                        'reference_number' => $transaction->reference_number,
                        'rr_number' => $transaction->rr_number,
                        'total_amount' => $transaction->total_amount,
                        'total_paid' => $totalPaid,
                        'balance' => $balance,
                        'status' => $transaction->status,
                        'terms' => $transaction->terms,
                        'is_overdue' => $transaction->is_overdue ?? false
                    ]);
                }
            }

            $user = auth()->user();

            return view('Pages.Printing.supplier_counter_receipt_print', compact('supplier', 'pendingTransactions', 'user'));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating counter receipt: ' . $e->getMessage(),
            ], 500);
        }
    }
}