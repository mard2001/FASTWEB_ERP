<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ActivityLoggable;
use Illuminate\Support\Facades\DB;
use App\Models\Customer\Customer;

class CustomerCredit extends Model
{
    use HasFactory, ActivityLoggable;

    // NOTE: User requested table name 'tblCsutomerCredits' (intentionally spelled)
    protected $table = 'tblCustomerCredits';

    protected $fillable = [
        'customer_code',
        'customer_name',
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
     * Relationship with Customer model
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'Customer');
    }

    /**
     * Get accounts receivable records for this customer
     */
    public function accountsReceivable()
    {
        return $this->hasMany(AccountsReceivable::class, 'customer_code', 'customer_code');
    }

    /**
     * Scope to get customers with outstanding balance
     */
    public function scopeWithBalance($query)
    {
        return $query->where('balance', '>', 0);
    }

    /**
     * Scope to get customers with credit
     */
    public function scopeWithCredit($query)
    {
        return $query->where('total_credit', '>', 0);
    }

    /**
     * Calculate and update the credit summary for a specific customer
     */
    public static function updateCustomerCredit($customerCode)
    {
        // Get customer information
        $customer = Customer::where('Customer', $customerCode)->first();

        if (!$customer) {
            return false;
        }

        // Calculate totals using AR tables
        $creditData = DB::select("
            SELECT 
                c.Customer as CustomerCode,
                c.Name as CustomerName,
                ISNULL(credit_summary.total_credit, 0) as total_credit,
                ISNULL(payment_summary.total_paid, 0) as total_paid,
                (ISNULL(credit_summary.total_credit, 0) - ISNULL(payment_summary.total_paid, 0)) as balance
            FROM tblCustomer c
            LEFT JOIN (
                SELECT 
                    customer_code,
                    SUM(total_amount) as total_credit
                FROM tblAccountsReceivable 
                WHERE customer_code = ?
                GROUP BY customer_code
            ) credit_summary ON c.Customer = credit_summary.customer_code
            LEFT JOIN (
                SELECT 
                    ar.customer_code,
                    SUM(p.payment_amount) as total_paid
                FROM tblAccountsReceivable ar
                INNER JOIN tblPayments p ON ar.id = p.accounts_receivable_id
                WHERE ar.customer_code = ?
                GROUP BY ar.customer_code
            ) payment_summary ON c.Customer = payment_summary.customer_code
            WHERE c.Customer = ?
        ", [$customerCode, $customerCode, $customerCode]);

        if (!empty($creditData)) {
            $data = $creditData[0];

            // Calculate credit balance (Credit Limit - Balance)
            $creditLimit = $customer->CreditLimit ?? 0;
            $creditBalance = $creditLimit - $data->balance;

            // Update or create the customer credit record
            self::updateOrCreate(
                ['customer_code' => $customerCode],
                [
                    'customer_name' => $data->CustomerName,
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
     * Refresh all customer credit data
     */
    public static function refreshAllCustomerCredits()
    {
        // Get all customers with their credit calculations
        $customersData = DB::select("
            SELECT 
                c.Customer as CustomerCode,
                c.Name as CustomerName,
                ISNULL(c.CreditLimit, 0) as credit_limit,
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
                GROUP BY ar.customer_code
            ) payment_summary ON c.Customer = payment_summary.customer_code
            WHERE c.Customer IS NOT NULL
            ORDER BY c.Name
        ");

        // Clear existing data and insert fresh data WITHOUT triggering model events/logs
        DB::table('tblCustomerCredits')->truncate();

        $rows = [];
        $now = now();
        foreach ($customersData as $data) {
            $rows[] = [
                'customer_code' => $data->CustomerCode,
                'customer_name' => $data->CustomerName,
                'total_credit' => $data->total_credit,
                'total_paid' => $data->total_paid,
                'balance' => $data->balance,
                'credit_limit' => $data->credit_limit,
                'credit_balance' => $data->credit_balance,
                'last_updated' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        if (!empty($rows)) {
            DB::table('tblCustomerCredits')->insert($rows);
        }

        return count($customersData);
    }

    /**
     * Get formatted values
     */
    public function getFormattedTotalCreditAttribute()
    {
        return '₱' . number_format($this->total_credit, 2);
    }

    public function getFormattedTotalPaidAttribute()
    {
        return '₱' . number_format($this->total_paid, 2);
    }

    public function getFormattedBalanceAttribute()
    {
        return '₱' . number_format($this->balance, 2);
    }
}