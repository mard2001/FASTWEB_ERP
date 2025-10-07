<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ActivityLoggable;
use Illuminate\Support\Facades\DB;

class SupplierCredit extends Model
{
    use HasFactory, ActivityLoggable;

    protected $table = 'tblSupplierCredits';

    protected $fillable = [
        'supplier_code',
        'supplier_name',
        'total_credit',
        'total_paid',
        'balance',
        'credit_limit',
        'credit_balance',
        'last_updated'
    ];

    protected $casts = [
        'total_credit' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'credit_balance' => 'decimal:2',
        'last_updated' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationship with Supplier model
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_code', 'SupplierCode');
    }

    /**
     * Get accounts payable records for this supplier
     */
    public function accountsPayable()
    {
        return $this->hasMany(AccountsPayable::class, 'supplier_code', 'supplier_code');
    }

    /**
     * Scope to get suppliers with outstanding balance
     */
    public function scopeWithBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

    /**
     * Scope to get suppliers with credit
     */
    public function scopeWithCredit($query)
    {
        return $query->where('total_credit', '>', 0);
    }

    /**
     * Calculate and update the credit summary for a specific supplier
     */
    public static function updateSupplierCredit($supplierCode)
    {
        // Get supplier information
        $supplier = Supplier::where('SupplierCode', $supplierCode)->first();
        
        if (!$supplier) {
            return false;
        }

        // Calculate totals using the same logic as the controller
        $creditData = DB::select("
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
                WHERE supplier_code = ?
                GROUP BY supplier_code
            ) credit_summary ON s.SupplierCode = credit_summary.supplier_code
            LEFT JOIN (
                SELECT 
                    ap.supplier_code,
                    SUM(p.payment_amount) as total_paid
                FROM tblAccountsPayable ap
                INNER JOIN tblPayments p ON ap.id = p.accounts_payable_id
                WHERE ap.supplier_code = ? 
                    AND (p.reference_number NOT LIKE 'AUTO-CM-%' OR p.reference_number IS NULL)
                GROUP BY ap.supplier_code
            ) payment_summary ON s.SupplierCode = payment_summary.supplier_code
            WHERE s.SupplierCode = ?
        ", [$supplierCode, $supplierCode, $supplierCode]);

        if (!empty($creditData)) {
            $data = $creditData[0];
            
            // Calculate credit balance (Credit Limit - Balance)
            $creditLimit = $supplier->CreditLimit ?? 0;
            $creditBalance = $creditLimit - $data->balance;
            
            // Update or create the supplier credit record
            self::updateOrCreate(
                ['supplier_code' => $supplierCode],
                [
                    'supplier_name' => $data->SupplierName,
                    'total_credit' => $data->total_credit,
                    'total_paid' => $data->total_paid,
                    'balance' => $data->balance,
                    'credit_limit' => $creditLimit,
                    'credit_balance' => $creditBalance,
                    'last_updated' => now()
                ]
            );
            
            return true;
        }
        
        return false;
    }

    /**
     * Refresh all supplier credit data
     */
    public static function refreshAllSupplierCredits()
    {
        // Get all suppliers with their credit calculations
        $suppliersData = DB::select("
            SELECT 
                s.SupplierCode,
                s.SupplierName,
                ISNULL(s.CreditLimit, 0) as credit_limit,
                ISNULL(credit_summary.total_credit, 0) as total_credit,
                ISNULL(payment_summary.total_paid, 0) as total_paid,
                (ISNULL(credit_summary.total_credit, 0) - ISNULL(payment_summary.total_paid, 0)) as balance,
                (ISNULL(s.CreditLimit, 0) - (ISNULL(credit_summary.total_credit, 0) - ISNULL(payment_summary.total_paid, 0))) as credit_balance
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
            WHERE s.SupplierCode IS NOT NULL
            ORDER BY s.SupplierName
        ");

        // Clear existing data and insert fresh data
        self::truncate();
        
        foreach ($suppliersData as $data) {
            self::create([
                'supplier_code' => $data->SupplierCode,
                'supplier_name' => $data->SupplierName,
                'total_credit' => $data->total_credit,
                'total_paid' => $data->total_paid,
                'balance' => $data->balance,
                'credit_limit' => $data->credit_limit,
                'credit_balance' => $data->credit_balance,
                'last_updated' => now()
            ]);
        }
        
        return count($suppliersData);
    }

    /**
     * Get formatted total credit
     */
    public function getFormattedTotalCreditAttribute()
    {
        return '₱' . number_format($this->total_credit, 2);
    }

    /**
     * Get formatted total paid
     */
    public function getFormattedTotalPaidAttribute()
    {
        return '₱' . number_format($this->total_paid, 2);
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute()
    {
        return '₱' . number_format($this->balance, 2);
    }
}