<?php

namespace App\Http\Controllers\api\PaymentHistory;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\AccountsPayable;
use App\Models\AccountsReceivable;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;

class PaymentHistoryController extends Controller
{
    /**
     * Get all payment history data
     */
    public function index(Request $request)
    {
        try {
            $payments = Payment::with(['accountsPayable.supplier', 'accountsReceivable', 'bank', 'gcash', 'check'])
                ->select(
                    'tblPayments.id',
                    'tblPayments.accounts_payable_id',
                    'tblPayments.accounts_receivable_id',
                    'tblPayments.payment_amount',
                    'tblPayments.payment_type',
                    'tblPayments.payment_date',
                    'tblPayments.payment_status',
                    'tblPayments.reference_number as payment_reference_number',
                    'tblPayments.remarks',
                    'tblPayments.process_by',
                    'tblPayments.bank_id',
                    'tblPayments.gcash_id',
                    'tblPayments.check_id',
                    'tblPayments.created_at',
                    'tblPayments.updated_at',
                    // AP fields
                    'tblAccountsPayable.supplier_code',
                    'tblAccountsPayable.supplier_name',
                    'tblAccountsPayable.rr_number',
                    'tblAccountsPayable.reference_number as ap_reference_number',
                    'tblAccountsPayable.total_amount as ap_invoice_amount',
                    'tblAccountsPayable.terms as ap_terms',
                    'tblAccountsPayable.date as ap_invoice_date',
                    // AR fields
                    'tblAccountsReceivable.customer_code',
                    'tblAccountsReceivable.customer_name',
                    'tblAccountsReceivable.so_number',
                    'tblAccountsReceivable.reference_number as ar_reference_number',
                    'tblAccountsReceivable.total_amount as ar_invoice_amount',
                    'tblAccountsReceivable.terms as ar_terms',
                    'tblAccountsReceivable.date as ar_invoice_date',
                    // Bank/Payment details
                    'tblBank.BankName',
                    'tblGcash.AccountName as GcashAccountName',
                    'tblGcash.AccountNumber as GcashAccountNumber',
                    'tblCheck.Payee as CheckPayee',
                    'tblCheck.CheckDate',
                    'tblCheck.CheckAmount',
                    'tblCheck.CheckNumber',
                    'tblCheck.AmountInWords as CheckAmountInWords'
                )
                ->leftJoin('tblAccountsPayable', 'tblPayments.accounts_payable_id', '=', 'tblAccountsPayable.id')
                ->leftJoin('tblAccountsReceivable', 'tblPayments.accounts_receivable_id', '=', 'tblAccountsReceivable.id')
                ->leftJoin('tblBank', 'tblPayments.bank_id', '=', 'tblBank.BankID')
                ->leftJoin('tblGcash', 'tblPayments.gcash_id', '=', 'tblGcash.GcashID')
                ->leftJoin('tblCheck', 'tblPayments.check_id', '=', 'tblCheck.CheckID')
                ->orderBy('tblPayments.created_at', 'desc')
                ->orderBy('tblPayments.payment_date', 'desc')
                ->orderBy('tblPayments.id', 'desc')
                ->get()
                ->map(function ($payment) {
                    // Determine if this is an AR or AP payment
                    $isAR = !empty($payment->accounts_receivable_id);
                    $isAP = !empty($payment->accounts_payable_id);
                    
                    return [
                        'id' => $payment->id,
                        'payment_date' => $payment->payment_date ? Carbon::parse($payment->payment_date)->format('Y-m-d') : null,
                        // Dynamic supplier/customer field
                        'supplier_customer_code' => $isAR ? ($payment->customer_code ?? 'N/A') : ($payment->supplier_code ?? 'N/A'),
                        'supplier_customer_name' => $isAR ? ($payment->customer_name ?? 'Unknown Customer') : ($payment->supplier_name ?? 'Unknown Supplier'),
                        // Dynamic RR#/SO# field  
                        'rr_so_number' => $isAR ? ($payment->so_number ?? 'N/A') : ($payment->rr_number ?? 'N/A'),
                        'reference_number' => $payment->payment_reference_number ?? 'N/A', // Payment reference number
                        'invoice_reference_number' => $isAR ? ($payment->ar_reference_number ?? 'N/A') : ($payment->ap_reference_number ?? 'N/A'),
                        'invoice_date' => $isAR ? 
                            ($payment->ar_invoice_date ? Carbon::parse($payment->ar_invoice_date)->format('Y-m-d') : null) : 
                            ($payment->ap_invoice_date ? Carbon::parse($payment->ap_invoice_date)->format('Y-m-d') : null),
                        'invoice_amount' => $isAR ? 
                            number_format((float)$payment->ar_invoice_amount, 2, '.', '') : 
                            number_format((float)$payment->ap_invoice_amount, 2, '.', ''),
                        'payment_amount' => number_format((float)$payment->payment_amount, 2, '.', ''),
                        'payment_type' => $payment->payment_type ?? 'cash',
                        'payment_status' => $payment->payment_status ?? 'full',
                        'bank_name' => $payment->BankName ?? null,
                        'gcash_account_name' => $payment->GcashAccountName ?? null,
                        'gcash_account_number' => $payment->GcashAccountNumber ?? null,
                        'check_payee' => $payment->CheckPayee ?? null,
                        'check_date' => $payment->CheckDate ? Carbon::parse($payment->CheckDate)->format('Y-m-d') : null,
                        'check_amount' => $payment->CheckAmount ? number_format((float)$payment->CheckAmount, 2, '.', '') : null,
                        'check_number' => $payment->CheckNumber ?? null,
                        'check_amount_in_words' => $payment->CheckAmountInWords ?? null,
                        'transaction_type' => $isAR ? 'Receivable' : 'Payable', // Dynamic transaction type
                        'terms' => $isAR ? ($payment->ar_terms ?? 'N/A') : ($payment->ap_terms ?? 'N/A'),
                        'payment_reference' => $payment->payment_reference_number ?? 'N/A', // Same as reference_number for compatibility
                        'remarks' => $payment->remarks ?? '',
                        'process_by' => $payment->process_by ?? 'System',
                        'accounts_payable_id' => $payment->accounts_payable_id,
                        'accounts_receivable_id' => $payment->accounts_receivable_id,
                        'bank_id' => $payment->bank_id,
                        'created_at' => $payment->created_at ? Carbon::parse($payment->created_at)->format('Y-m-d H:i:s') : null,
                        'updated_at' => $payment->updated_at ? Carbon::parse($payment->updated_at)->format('Y-m-d H:i:s') : null,
                        'sort_timestamp' => $payment->created_at ? Carbon::parse($payment->created_at)->timestamp : 0,
                        // Legacy fields for backward compatibility
                        'supplier_code' => $isAR ? ($payment->customer_code ?? 'N/A') : ($payment->supplier_code ?? 'N/A'),
                        'supplier_name' => $isAR ? ($payment->customer_name ?? 'Unknown Customer') : ($payment->supplier_name ?? 'Unknown Supplier'),
                        'rr_number' => $isAR ? ($payment->so_number ?? 'N/A') : ($payment->rr_number ?? 'N/A')
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Payment history retrieved successfully',
                'data' => $payments
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payment history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific payment details
     */
    public function show($id)
    {
        try {
            $payment = Payment::with(['accountsPayable.supplier', 'bank', 'gcash', 'check'])
                ->select(
                    'tblPayments.*',
                    'tblAccountsPayable.supplier_code',
                    'tblAccountsPayable.supplier_name',
                    'tblAccountsPayable.rr_number',
                    'tblAccountsPayable.reference_number',
                    'tblAccountsPayable.total_amount as invoice_amount',
                    'tblAccountsPayable.terms',
                    'tblAccountsPayable.date as invoice_date',
                    'tblAccountsPayable.status as invoice_status',
                    'tblAccountsPayable.balance_amount',
                    'tblBank.BankName',
                    'tblGcash.AccountName as GcashAccountName',
                    'tblGcash.AccountNumber as GcashAccountNumber',
                    'tblCheck.Payee as CheckPayee',
                    'tblCheck.CheckDate',
                    'tblCheck.CheckAmount',
                    'tblCheck.CheckNumber',
                    'tblCheck.AmountInWords as CheckAmountInWords'
                )
                ->leftJoin('tblAccountsPayable', 'tblPayments.accounts_payable_id', '=', 'tblAccountsPayable.id')
                ->leftJoin('tblBank', 'tblPayments.bank_id', '=', 'tblBank.BankID')
                ->leftJoin('tblGcash', 'tblPayments.gcash_id', '=', 'tblGcash.GcashID')
                ->leftJoin('tblCheck', 'tblPayments.check_id', '=', 'tblCheck.CheckID')
                ->where('tblPayments.id', $id)
                ->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment record not found'
                ], 404);
            }

            $formattedPayment = [
                'id' => $payment->id,
                'payment_date' => $payment->payment_date ? Carbon::parse($payment->payment_date)->format('Y-m-d') : null,
                'supplier_code' => $payment->supplier_code ?? 'N/A',
                'supplier_name' => $payment->supplier_name ?? 'Unknown Supplier',
                'rr_number' => $payment->rr_number ?? 'N/A',
                'reference_number' => $payment->reference_number ?? 'N/A',
                'invoice_date' => $payment->invoice_date ? Carbon::parse($payment->invoice_date)->format('Y-m-d') : null,
                'invoice_amount' => number_format((float)$payment->invoice_amount, 2, '.', ''),
                'payment_amount' => number_format((float)$payment->payment_amount, 2, '.', ''),
                'payment_type' => $payment->payment_type ?? 'cash',
                'payment_status' => $payment->payment_status ?? 'full',
                'bank_name' => $payment->BankName ?? null,
                'check_payee' => $payment->CheckPayee ?? null,
                'check_date' => $payment->CheckDate ? Carbon::parse($payment->CheckDate)->format('Y-m-d') : null,
                'check_amount' => $payment->CheckAmount ? number_format((float)$payment->CheckAmount, 2, '.', '') : null,
                'check_number' => $payment->CheckNumber ?? null,
                'check_amount_in_words' => $payment->CheckAmountInWords ?? null,
                'transaction_type' => 'Payable', // For now, only payables
                'terms' => $payment->terms ?? 'N/A',
                'payment_reference' => $payment->reference_number ?? 'N/A',
                'remarks' => $payment->remarks ?? '',
                'process_by' => $payment->process_by ?? 'System',
                'accounts_payable_id' => $payment->accounts_payable_id,
                'bank_id' => $payment->bank_id,
                'invoice_status' => $payment->invoice_status ?? 'N/A',
                'balance_amount' => number_format((float)$payment->balance_amount, 2, '.', ''),
                'created_at' => $payment->created_at ? Carbon::parse($payment->created_at)->format('Y-m-d H:i:s') : null,
                'updated_at' => $payment->updated_at ? Carbon::parse($payment->updated_at)->format('Y-m-d H:i:s') : null
            ];

            return response()->json([
                'success' => true,
                'message' => 'Payment details retrieved successfully',
                'data' => $formattedPayment
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payment details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get suppliers and customers for dropdown (unified)
     */
    public function getSuppliers()
    {
        try {
            $data = [];
            
            // Get suppliers from AP payments
            $suppliers = DB::table('tblPayments')
                ->join('tblAccountsPayable', 'tblPayments.accounts_payable_id', '=', 'tblAccountsPayable.id')
                ->select('tblAccountsPayable.supplier_code as Code', 'tblAccountsPayable.supplier_name as Name')
                ->whereNotNull('tblPayments.accounts_payable_id')
                ->groupBy('tblAccountsPayable.supplier_code', 'tblAccountsPayable.supplier_name')
                ->get()
                ->map(function ($item) {
                    return [
                        'SupplierCode' => $item->Code,
                        'SupplierName' => $item->Name,
                        'Type' => 'Supplier'
                    ];
                });
            
            // Get customers from AR payments
            $customers = DB::table('tblPayments')
                ->join('tblAccountsReceivable', 'tblPayments.accounts_receivable_id', '=', 'tblAccountsReceivable.id')
                ->select('tblAccountsReceivable.customer_code as Code', 'tblAccountsReceivable.customer_name as Name')
                ->whereNotNull('tblPayments.accounts_receivable_id')
                ->groupBy('tblAccountsReceivable.customer_code', 'tblAccountsReceivable.customer_name')
                ->get()
                ->map(function ($item) {
                    return [
                        'SupplierCode' => $item->Code, // Keep same field name for compatibility
                        'SupplierName' => $item->Name, // Keep same field name for compatibility
                        'Type' => 'Customer'
                    ];
                });
            
            // Merge and sort
            $data = $suppliers->merge($customers)->sortBy('SupplierName')->values();

            return response()->json([
                'success' => true,
                'message' => 'Suppliers and customers retrieved successfully',
                'data' => $data
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving suppliers/customers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment statistics for dashboard
     */
    public function getStatistics(Request $request)
    {
        try {
            // Get today's date for filtering
            $today = Carbon::today();
            $thisMonth = Carbon::now()->startOfMonth();
            $thisYear = Carbon::now()->startOfYear();

            $stats = [
                'total_payments' => Payment::count(),
                'total_amount_today' => Payment::whereDate('payment_date', $today)->sum('payment_amount'),
                'total_amount_this_month' => Payment::whereDate('payment_date', '>=', $thisMonth)->sum('payment_amount'),
                'total_amount_this_year' => Payment::whereDate('payment_date', '>=', $thisYear)->sum('payment_amount'),
                'full_payments_count' => Payment::where('payment_type', 'full')->count(),
                'partial_payments_count' => Payment::where('payment_type', 'partial')->count()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Payment statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payment statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}
