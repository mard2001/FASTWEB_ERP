<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\BankReconciliation;
use App\Models\Bank;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankReconciliationController extends Controller
{
    /**
     * Display a listing of all banks with their reconciliation data.
     * Auto-populates all banks from tblBank, even if they don't have reconciliation records yet.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            // Get all active banks
            $banks = Bank::where('Status', 'A')
                ->orderBy('BankName', 'asc')
                ->get();

            // Transform the data to include reconciliation info
            $data = $banks->map(function($bank) {
                // Get the latest reconciliation for THIS specific bank
                $latestRecon = BankReconciliation::where('BankID', $bank->BankID)
                    ->orderBy('DateCreated', 'desc')
                    ->first();
                
                // Calculate actual total outflows from payments (real-time)
                $actualTotalOutflows = Payment::where('bank_id', $bank->BankID)->sum('payment_amount') ?? 0;
                
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
                    'BankID' => $bank->BankID,
                    'BankName' => $bank->BankName,
                    'AccountName' => $bank->AccountName,
                    'AccountNumber' => $bank->AccountNumber,
                    'AccountType' => $bank->AccountType,
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
                'message' => 'All banks retrieved successfully',
                'data' => $data
            ], 200);   

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bank data: ' . $e->getMessage(),
                'data' => []
            ], 500);   
        }
    }

    /**
     * Get bank details with reconciliation info and transaction history
     *
     * @param  int  $bankId
     * @return \Illuminate\Http\Response
     */
    public function show($bankId)
    {
        try {
            $bank = Bank::with(['reconciliations' => function($query) {
                $query->orderBy('DateCreated', 'desc')->limit(1);
            }])->where('BankID', $bankId)->first();

            if (is_null($bank)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bank not found',
                    'data' => []
                ], 404);
            }

            $latestRecon = $bank->reconciliations->first();

            // Get transaction history (payments made through this bank)
            $transactions = Payment::where('bank_id', $bankId)
                ->with(['accountsPayable.supplier', 'check'])
                ->orderBy('payment_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'payment_date' => $payment->payment_date,
                        'payment_amount' => $payment->payment_amount,
                        'payment_type' => $payment->payment_type,
                        'payment_status' => $payment->payment_status,
                        'reference_number' => $payment->reference_number,
                        'check_number' => $payment->check ? $payment->check->CheckNumber : null,
                        'check_id' => $payment->check_id,
                        'remarks' => $payment->remarks,
                        'supplier_name' => $payment->accountsPayable->supplier->SupplierName ?? 'N/A',
                        'supplier_code' => $payment->accountsPayable->supplier_code ?? 'N/A',
                        'ap_reference' => $payment->accountsPayable->reference_number ?? 'N/A',
                        'transaction_type' => 'OUT', // Payments are always outflows (Accounts Payable)
                        'created_at' => $payment->created_at
                    ];
                });

            // Calculate total outflows from transactions
            $totalTransactionOutflows = $transactions->sum('payment_amount');

            // Calculate available balance
            $beginningBalance = $latestRecon->BeginningBalance ?? 0;
            $totalInflows = $latestRecon->TotalInflows ?? 0;
            $availableBalance = $beginningBalance + $totalInflows - $totalTransactionOutflows;

            $data = [
                'BankID' => $bank->BankID,
                'BankName' => $bank->BankName,
                'AccountName' => $bank->AccountName,
                'AccountNumber' => $bank->AccountNumber,
                'AccountType' => $bank->AccountType,
                'CardNumber' => $bank->CardNumber,
                'Address' => $bank->Address,
                'ContactNumber' => $bank->ContactNumber,
                'ReconciliationID' => $latestRecon->ReconciliationID ?? null,
                'BeginningBalance' => $beginningBalance,
                'TotalInflows' => $totalInflows,
                'TotalOutflows' => $totalTransactionOutflows, // Use actual transaction total
                'AvailableBalance' => $latestRecon ? $availableBalance : null,
                'ReconciliationDate' => $latestRecon->ReconciliationDate ?? null,
                'Notes' => $latestRecon->Notes ?? null,
                'HasReconciliation' => $latestRecon ? true : false,
                'transactions' => $transactions,
                'transaction_count' => $transactions->count()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Bank details retrieved successfully',
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
     * Set or update beginning balance for a bank
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setBeginningBalance(Request $request)
    {
        try {
            $data = $request->data;
            
            // Check if bank exists
            $bank = Bank::find($data['BankID']);
            if (!$bank) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bank not found',
                ], 404);
            }

            // Get or create reconciliation record for this bank
            $reconciliation = BankReconciliation::where('BankID', $data['BankID'])
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
                $this->recalculateBalance($data['BankID']);

                activity('bank_reconciliation')
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => $request->header('User-Agent'),
                        'bank_name' => $bank->BankName,
                        'old_beginning_balance' => $oldData['BeginningBalance'],
                        'new_beginning_balance' => $data['BeginningBalance'],
                        'event' => 'updated',
                    ])
                    ->log("Updated beginning balance for '{$bank->BankName}' from {$oldData['BeginningBalance']} to {$data['BeginningBalance']}");
                
            } else {
                // Create new reconciliation record
                // Calculate initial available balance (no inflows/outflows yet)
                $availableBalance = $data['BeginningBalance'];
                
                $reconciliation = BankReconciliation::create([
                    'BankID' => $data['BankID'],
                    'BeginningBalance' => $data['BeginningBalance'],
                    'ReconciliationDate' => $data['ReconciliationDate'] ?? now(),
                    'TotalInflows' => 0,
                    'TotalOutflows' => 0,
                    'AvailableBalance' => $availableBalance,
                    'Notes' => $data['Notes'] ?? null,
                ]);

                activity('bank_reconciliation')
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => $request->header('User-Agent'),
                        'bank_name' => $bank->BankName,
                        'beginning_balance' => $data['BeginningBalance'],
                        'event' => 'created',
                    ])
                    ->log("Set beginning balance for '{$bank->BankName}': {$data['BeginningBalance']}");
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
     * Recalculate balance for a bank based on transactions
     *
     * @param  int  $bankId
     * @return void
     */
    private function recalculateBalance($bankId)
    {
        // Get the latest reconciliation record
        $reconciliation = BankReconciliation::where('BankID', $bankId)
            ->orderBy('DateCreated', 'desc')
            ->first();

        if (!$reconciliation) {
            return;
        }

        // Calculate total outflows from payments
        $totalOutflows = Payment::where('bank_id', $bankId)->sum('payment_amount') ?? 0;

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
}
