<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'tblPayments';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    // Override timestamp column names to match SQL Server convention
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'accounts_payable_id',
        'payment_amount',
        'payment_type',
        'payment_date',
        'payment_status',
        'reference_number',
        'remarks',
        'process_by',
        'bank_id',
        'gcash_id',
        'check_id'
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'payment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $dates = [
        'payment_date',
        'created_at',
        'updated_at'
    ];

    /**
     * Relationship: Payment belongs to AccountsPayable
     */
    public function accountsPayable()
    {
        return $this->belongsTo(AccountsPayable::class, 'accounts_payable_id', 'id');
    }

    /**
     * Relationship: Payment belongs to Bank
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'BankID');
    }

    /**
     * Relationship: Payment belongs to GCash
     */
    public function gcash()
    {
        return $this->belongsTo(Gcash::class, 'gcash_id', 'GcashID');
    }

    /**
     * Relationship: Payment belongs to Check
     */
    public function check()
    {
        return $this->belongsTo(Check::class, 'check_id', 'CheckID');
    }

    /**
     * Scope: Get payments for a specific accounts payable record
     */
    public function scopeForAccountsPayable($query, $apId)
    {
        return $query->where('accounts_payable_id', $apId);
    }

    /**
     * Scope: Get payments by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('payment_type', $type);
    }

    /**
     * Scope: Get payments within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Get formatted payment amount
     */
    public function getFormattedAmountAttribute()
    {
        return number_format($this->payment_amount, 2);
    }

    /**
     * Get payment type label (method of payment)
     */
    public function getPaymentTypeLabel()
    {
        return ucfirst($this->payment_type) . ' Payment';
    }

    /**
     * Get payment status label (full/partial)
     */
    public function getPaymentStatusLabel()
    {
        return ucfirst($this->payment_status) . ' Payment';
    }

    /**
     * Check if payment is full payment
     */
    public function isFullPayment()
    {
        return $this->payment_status === 'full';
    }

    /**
     * Check if payment is partial payment
     */
    public function isPartialPayment()
    {
        return $this->payment_status === 'partial';
    }
}
