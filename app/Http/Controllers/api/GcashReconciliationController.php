<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\GcashReconciliation;
use App\Models\Gcash;
use App\Models\Payment;
use App\Models\GcashManualTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class GcashReconciliationController extends Controller
{
    /**
     * Display a listing of all gcash accounts with their reconciliation data.
     * Auto-populates all gcash accounts from tblGcash, even if they don't have reconciliation records yet.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            // Get all active gcash accounts
            $gcashAccounts = Gcash::where('Status', 'A')
                ->orderBy('AccountName', 'asc')
                ->get();

            // Transform the data to include reconciliation info
            $data = $gcashAccounts->map(function($gcash) {
                // Get the latest reconciliation for THIS specific gcash account
                $latestRecon = GcashReconciliation::where('GcashID', $gcash->GcashID)
                    ->orderBy('DateCreated', 'desc')
                    ->first();
                
                // Calculate actual total outflows from payments (real-time)
                // Note: Assuming payments can be made through Gcash - adjust based on your payment system
                $actualTotalOutflows = Payment::where('gcash_id', $gcash->GcashID)->sum('payment_amount') ?? 0;
                
                // Calculate available balance using actual transaction data
                $beginningBalance = $latestRecon->BeginningBalance ?? 0;
                $totalInflows = $latestRecon->TotalInflows ?? 0;
                $availableBalance = $beginningBalance + $totalInflows - $actualTotalOutflows;
                
                // Update the reconciliation record with actual outflows if it exists
                if ($latestRecon && $latestRecon->TotalOutflows != $actualTotalOutflows) {
                    $latestRecon->update([
                        'TotalOutflows' => $actualTotalOutflows,
                        'AvailableBalance' => $availableBalance
                    ]);
                }
                
                return [
                    'GcashID' => $gcash->GcashID,
                    'AccountName' => $gcash->AccountName,
                    'AccountNumber' => $gcash->AccountNumber,
                    'ReconciliationID' => $latestRecon->ReconciliationID ?? null,
                    'BeginningBalance' => $beginningBalance,
                    'TotalInflows' => $totalInflows,
                    'TotalOutflows' => $actualTotalOutflows,
                    'AvailableBalance' => $latestRecon ? $availableBalance : null,
                    'LastReconciliationDate' => $latestRecon->ReconciliationDate ?? null,
                    'DateCreated' => $latestRecon->DateCreated ?? null,
                    'HasReconciliation' => $latestRecon ? true : false,
                    'Notes' => $latestRecon->Notes ?? null
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'All Gcash accounts retrieved successfully',
                'data' => $data
            ], 200);   

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving Gcash data: ' . $e->getMessage(),
                'data' => []
            ], 500);   
        }
    }

    /**
     * Get gcash details with reconciliation info and transaction history
     *
     * @param  int  $gcashId
     * @return \Illuminate\Http\Response
     */
    public function show($gcashId)
    {
        try {
            $gcash = Gcash::where('GcashID', $gcashId)->first();

            if (is_null($gcash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gcash account not found',
                    'data' => []
                ], 404);
            }

            $latestRecon = GcashReconciliation::where('GcashID', $gcashId)
                ->orderBy('DateCreated', 'desc')
                ->first();

            // Get transaction history (payments made through this gcash account)
            // Note: Adjust this based on your payment system structure
            $transactions = Payment::where('gcash_id', $gcashId)
                ->with(['accountsPayable.supplier'])
                ->orderBy('payment_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'payment_date' => $payment->payment_date,
                        'payment_amount' => $payment->payment_amount,
                        'payment_type' => $payment->payment_type,
                        'payment_status' => $payment->payment_status,
                        'reference_number' => $payment->reference_number,
                        'remarks' => $payment->remarks,
                        'supplier_name' => $payment->accountsPayable->supplier->SupplierName ?? 'N/A',
                        'supplier_code' => $payment->accountsPayable->supplier_code ?? 'N/A',
                        'ap_reference' => $payment->accountsPayable->reference_number ?? 'N/A',
                        'transaction_type' => 'OUT', // Payments are always outflows (Accounts Payable)
                        'created_at' => $payment->created_at
                    ];
                })
                ->toArray();

            // Get manual transactions for this gcash account
            $manualTransactions = GcashManualTransaction::where('GcashID', $gcashId)
                ->orderBy('TransactionDate', 'asc')
                ->orderBy('DateCreated', 'asc')
                ->get()
                ->map(function($manual) {
                    return [
                        'id' => 'MT-' . $manual->ManualTransactionID,
                        'payment_date' => $manual->TransactionDate,
                        'payment_amount' => $manual->Amount,
                        'payment_type' => 'Manual ' . ($manual->TransactionType === 'IN' ? 'Deposit' : 'Withdrawal'),
                        'payment_status' => 'Completed',
                        'reference_number' => $manual->ReferenceNumber ?? 'MT-' . str_pad($manual->ManualTransactionID, 6, '0', STR_PAD_LEFT),
                        'remarks' => $manual->Remarks,
                        'supplier_name' => 'N/A',
                        'supplier_code' => 'N/A',
                        'ap_reference' => 'N/A',
                        'transaction_type' => $manual->TransactionType,
                        'created_at' => $manual->DateCreated
                    ];
                })
                ->toArray();

            // Merge manual transactions with payment transactions
            $transactions = array_merge($transactions, $manualTransactions);

            // Add beginning balance as a deposit transaction if it exists
            if ($latestRecon && $latestRecon->BeginningBalance > 0) {
                $beginningBalanceTransaction = [
                    'id' => 'BB-' . $latestRecon->ReconciliationID,
                    'payment_date' => $latestRecon->ReconciliationDate,
                    'payment_amount' => $latestRecon->BeginningBalance,
                    'payment_type' => 'Beginning Balance',
                    'payment_status' => 'Completed',
                    'reference_number' => 'BB-' . str_pad($latestRecon->ReconciliationID, 6, '0', STR_PAD_LEFT),
                    'remarks' => $latestRecon->Notes ?? 'Initial beginning balance',
                    'supplier_name' => 'N/A',
                    'supplier_code' => 'N/A',
                    'ap_reference' => 'N/A',
                    'transaction_type' => 'IN', // Beginning balance is an inflow (Deposit)
                    'created_at' => $latestRecon->DateCreated,
                    'is_beginning_balance' => true // Flag to identify beginning balance
                ];
                
                // Add beginning balance transaction to the array
                $transactions[] = $beginningBalanceTransaction;
            }

            // Sort transactions: Beginning balance first, then by date ascending, then by created_at ascending
            usort($transactions, function($a, $b) {
                // Beginning balance always comes first
                $aIsBeginning = isset($a['is_beginning_balance']) && $a['is_beginning_balance'];
                $bIsBeginning = isset($b['is_beginning_balance']) && $b['is_beginning_balance'];
                
                if ($aIsBeginning && !$bIsBeginning) return -1;
                if (!$aIsBeginning && $bIsBeginning) return 1;
                
                // For other transactions, sort by date ascending
                $dateCompare = strtotime($a['payment_date']) - strtotime($b['payment_date']);
                if ($dateCompare === 0) {
                    // If dates are the same, sort by creation time ascending
                    return strtotime($a['created_at']) - strtotime($b['created_at']);
                }
                return $dateCompare;
            });

            // Calculate total outflows from transactions (excluding beginning balance)
            $totalTransactionOutflows = collect($transactions)
                ->where('transaction_type', 'OUT')
                ->sum('payment_amount');

            // Calculate available balance
            $beginningBalance = $latestRecon->BeginningBalance ?? 0;
            $totalInflows = $latestRecon->TotalInflows ?? 0;
            $availableBalance = $beginningBalance + $totalInflows - $totalTransactionOutflows;

            $data = [
                'GcashID' => $gcash->GcashID,
                'AccountName' => $gcash->AccountName,
                'AccountNumber' => $gcash->AccountNumber,
                'ReconciliationID' => $latestRecon->ReconciliationID ?? null,
                'BeginningBalance' => $beginningBalance,
                'TotalInflows' => $totalInflows,
                'TotalOutflows' => $totalTransactionOutflows, // Use actual transaction total
                'AvailableBalance' => $latestRecon ? $availableBalance : null,
                'ReconciliationDate' => $latestRecon->ReconciliationDate ?? null,
                'Notes' => $latestRecon->Notes ?? null,
                'HasReconciliation' => $latestRecon ? true : false,
                'transactions' => $transactions,
                'transaction_count' => count($transactions)
            ];

            return response()->json([
                'success' => true,
                'message' => 'Gcash details retrieved successfully',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Set or update beginning balance for a gcash account
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setBeginningBalance(Request $request)
    {
        try {
            $data = $request->data;
            
            // Check if gcash account exists
            $gcash = Gcash::find($data['GcashID']);
            if (!$gcash) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gcash account not found',
                ], 404);
            }

            // Get or create reconciliation record for this gcash account
            $reconciliation = GcashReconciliation::where('GcashID', $data['GcashID'])
                ->orderBy('DateCreated', 'desc')
                ->first();

            if ($reconciliation) {
                // Update existing reconciliation
                $oldData = $reconciliation->toArray();
                
                // Calculate available balance
                $availableBalance = $data['BeginningBalance'] + ($reconciliation->TotalInflows ?? 0) - ($reconciliation->TotalOutflows ?? 0);
                
                $reconciliation->update([
                    'BeginningBalance' => $data['BeginningBalance'],
                    'ReconciliationDate' => $data['ReconciliationDate'] ?? now(),
                    'AvailableBalance' => $availableBalance,
                    'Notes' => $data['Notes'] ?? $reconciliation->Notes,
                ]);

                // Recalculate balances
                $this->recalculateBalance($data['GcashID']);

                activity('gcash_reconciliation')
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => $request->header('User-Agent'),
                        'account_name' => $gcash->AccountName,
                        'old_beginning_balance' => $oldData['BeginningBalance'],
                        'new_beginning_balance' => $data['BeginningBalance'],
                        'event' => 'updated',
                    ])
                    ->log("Updated beginning balance for Gcash '{$gcash->AccountName}' from {$oldData['BeginningBalance']} to {$data['BeginningBalance']}");
                
            } else {
                // Create new reconciliation record
                // Calculate initial available balance (no inflows/outflows yet)
                $availableBalance = $data['BeginningBalance'];
                
                $reconciliation = GcashReconciliation::create([
                    'GcashID' => $data['GcashID'],
                    'BeginningBalance' => $data['BeginningBalance'],
                    'ReconciliationDate' => $data['ReconciliationDate'] ?? now(),
                    'TotalInflows' => 0,
                    'TotalOutflows' => 0,
                    'AvailableBalance' => $availableBalance,
                    'Notes' => $data['Notes'] ?? null,
                ]);

                activity('gcash_reconciliation')
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => $request->header('User-Agent'),
                        'account_name' => $gcash->AccountName,
                        'beginning_balance' => $data['BeginningBalance'],
                        'event' => 'created',
                    ])
                    ->log("Set beginning balance for Gcash '{$gcash->AccountName}': {$data['BeginningBalance']}");
            }

            return response()->json([
                'success' => true,
                'message' => 'Beginning balance set successfully',
                'data' => $reconciliation
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recalculate balance for a gcash account based on transactions
     *
     * @param  int  $gcashId
     * @return void
     */
    private function recalculateBalance($gcashId)
    {
        // Get the latest reconciliation record
        $reconciliation = GcashReconciliation::where('GcashID', $gcashId)
            ->orderBy('DateCreated', 'desc')
            ->first();

        if (!$reconciliation) {
            return;
        }

        // Calculate total outflows from payments (if payments can be made through Gcash)
        $totalOutflows = Payment::where('gcash_id', $gcashId)->sum('payment_amount') ?? 0;

        // Calculate available balance: BeginningBalance + TotalInflows - TotalOutflows
        $availableBalance = ($reconciliation->BeginningBalance ?? 0) 
                          + ($reconciliation->TotalInflows ?? 0) 
                          - $totalOutflows;

        // Update TotalOutflows and AvailableBalance
        $reconciliation->update([
            'TotalOutflows' => $totalOutflows,
            'AvailableBalance' => $availableBalance,
        ]);
    }

    /**
     * Store a manual transaction (deposit or withdrawal)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeManualTransaction(Request $request)
    {
        try {
            $data = $request->data;
            
            // Check if gcash account exists
            $gcash = Gcash::find($data['GcashID']);
            if (!$gcash) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gcash account not found',
                ], 404);
            }

            // Validate transaction type
            if (!in_array($data['TransactionType'], ['IN', 'OUT'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid transaction type. Must be IN or OUT.',
                ], 400);
            }

            // Get current user ID (if authentication is enabled)
            $userId = Auth::id() ?? null;

            // Create manual transaction record
            $manualTransaction = GcashManualTransaction::create([
                'GcashID' => $data['GcashID'],
                'TransactionType' => $data['TransactionType'],
                'Amount' => $data['Amount'],
                'TransactionDate' => $data['TransactionDate'],
                'ReferenceNumber' => $data['ReferenceNumber'] ?? null,
                'Remarks' => $data['Remarks'],
                'CreatedBy' => $userId,
            ]);

            // Update gcash reconciliation totals
            $reconciliation = GcashReconciliation::where('GcashID', $data['GcashID'])
                ->orderBy('DateCreated', 'desc')
                ->first();

            if ($reconciliation) {
                if ($data['TransactionType'] === 'IN') {
                    // Deposit: increase TotalInflows
                    $newTotalInflows = ($reconciliation->TotalInflows ?? 0) + $data['Amount'];
                    $reconciliation->TotalInflows = $newTotalInflows;
                } else {
                    // Withdrawal: increase TotalOutflows
                    $newTotalOutflows = ($reconciliation->TotalOutflows ?? 0) + $data['Amount'];
                    $reconciliation->TotalOutflows = $newTotalOutflows;
                }

                // Recalculate available balance
                $reconciliation->AvailableBalance = $reconciliation->calculateAvailableBalance();
                $reconciliation->save();
            }

            // Log activity
            $transactionTypeLabel = $data['TransactionType'] === 'IN' ? 'deposit' : 'withdrawal';
            activity('gcash_reconciliation')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'account_name' => $gcash->AccountName,
                    'transaction_type' => $data['TransactionType'],
                    'amount' => $data['Amount'],
                    'reference' => $data['ReferenceNumber'] ?? 'N/A',
                    'event' => 'manual_transaction_created',
                ])
                ->log("Created manual {$transactionTypeLabel} for Gcash '{$gcash->AccountName}': ₱" . number_format($data['Amount'], 2));

            return response()->json([
                'success' => true,
                'message' => 'Manual transaction saved successfully',
                'data' => $manualTransaction
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}