<?php

namespace App\Http\Controllers\api\PaymentHistory;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\AccountsPayable;
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
            $payments = Payment::with(['accountsPayable.supplier', 'bank', 'gcash'])
                ->select(
                    'tblPayments.id',
                    'tblPayments.accounts_payable_id',
                    'tblPayments.payment_amount',
                    'tblPayments.payment_type',
                    'tblPayments.payment_date',
                    'tblPayments.payment_status',
                    'tblPayments.reference_number as payment_reference_number',
                    'tblPayments.remarks',
                    'tblPayments.process_by',
                    'tblPayments.bank_id',
                    'tblPayments.gcash_id',
                    'tblPayments.created_at',
                    'tblPayments.updated_at',
                    'tblAccountsPayable.supplier_code',
                    'tblAccountsPayable.supplier_name',
                    'tblAccountsPayable.rr_number',
                    'tblAccountsPayable.reference_number as ap_reference_number',
                    'tblAccountsPayable.total_amount as invoice_amount',
                    'tblAccountsPayable.terms',
                    'tblAccountsPayable.date as invoice_date',
                    'tblBank.BankName',
                    'tblGcash.AccountName as GcashAccountName',
                    'tblGcash.AccountNumber as GcashAccountNumber'
                )
                ->leftJoin('tblAccountsPayable', 'tblPayments.accounts_payable_id', '=', 'tblAccountsPayable.id')
                ->leftJoin('tblBank', 'tblPayments.bank_id', '=', 'tblBank.BankID')
                ->leftJoin('tblGcash', 'tblPayments.gcash_id', '=', 'tblGcash.GcashID')
                ->orderBy('tblPayments.created_at', 'desc')
                ->orderBy('tblPayments.payment_date', 'desc')
                ->orderBy('tblPayments.id', 'desc')
                ->get()
                ->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'payment_date' => $payment->payment_date ? Carbon::parse($payment->payment_date)->format('Y-m-d') : null,
                        'supplier_code' => $payment->supplier_code ?? 'N/A',
                        'supplier_name' => $payment->supplier_name ?? 'Unknown Supplier',
                        'rr_number' => $payment->rr_number ?? 'N/A',
                        'reference_number' => $payment->payment_reference_number ?? 'N/A', // Payment reference number
                        'ap_reference_number' => $payment->ap_reference_number ?? 'N/A', // AP reference number (for RR)
                        'invoice_date' => $payment->invoice_date ? Carbon::parse($payment->invoice_date)->format('Y-m-d') : null,
                        'invoice_amount' => number_format((float)$payment->invoice_amount, 2, '.', ''),
                        'payment_amount' => number_format((float)$payment->payment_amount, 2, '.', ''),
                        'payment_type' => $payment->payment_type ?? 'cash',
                        'payment_status' => $payment->payment_status ?? 'full',
                        'bank_name' => $payment->BankName ?? null,
                        'gcash_account_name' => $payment->GcashAccountName ?? null,
                        'gcash_account_number' => $payment->GcashAccountNumber ?? null,
                        'transaction_type' => 'Payable', // For now, only payables
                        'terms' => $payment->terms ?? 'N/A',
                        'payment_reference' => $payment->payment_reference_number ?? 'N/A', // Same as reference_number for compatibility
                        'remarks' => $payment->remarks ?? '',
                        'process_by' => $payment->process_by ?? 'System',
                        'accounts_payable_id' => $payment->accounts_payable_id,
                        'bank_id' => $payment->bank_id,
                        'created_at' => $payment->created_at ? Carbon::parse($payment->created_at)->format('Y-m-d H:i:s') : null,
                        'updated_at' => $payment->updated_at ? Carbon::parse($payment->updated_at)->format('Y-m-d H:i:s') : null,
                        'sort_timestamp' => $payment->created_at ? Carbon::parse($payment->created_at)->timestamp : 0
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
            $payment = Payment::with(['accountsPayable.supplier', 'bank', 'gcash'])
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
                    'tblGcash.AccountNumber as GcashAccountNumber'
                )
                ->leftJoin('tblAccountsPayable', 'tblPayments.accounts_payable_id', '=', 'tblAccountsPayable.id')
                ->leftJoin('tblBank', 'tblPayments.bank_id', '=', 'tblBank.BankID')
                ->leftJoin('tblGcash', 'tblPayments.gcash_id', '=', 'tblGcash.GcashID')
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
     * Get suppliers for dropdown (similar to AP)
     */
    public function getSuppliers()
    {
        try {
            $suppliers = Supplier::select('SupplierCode', 'SupplierName')
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
