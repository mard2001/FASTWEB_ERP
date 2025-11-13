<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ActivityLoggable;
use App\Models\Customer\Customer;
use App\Models\Payment;

class AccountsReceivable extends Model
{
    use HasFactory, ActivityLoggable;

    protected $table = 'tblAccountsReceivable';

    protected $fillable = [
        'date',
        'customer_code',
        'customer_name',
        'so_number',
        'reference_number',
        'total_amount',
        'terms',
        'status',
        'remarks',
        'process_by',
        'payment_type',
        'payment_amount',
        'payment_date',
        'payment_remarks',
        'current_balance',
        'credit_generated',
        'credit_received',
        'last_balance_update'
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'payment_date' => 'date',
        'current_balance' => 'decimal:2',
        'credit_generated' => 'decimal:2',
        'credit_received' => 'decimal:2',
        'last_balance_update' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationship with Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'Customer');
    }

    /**
     * Relationship with Payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'accounts_receivable_id', 'id');
    }

    /**
     * AR credit memo applications where this AR is the source (generated credit).
     */
    public function arCreditMemoApplicationsAsSource()
    {
        return $this->hasMany(\App\Models\ARCreditMemoApplication::class, 'source_ar_id', 'id');
    }

    /**
     * AR credit memo applications where this AR is the target (received credit).
     */
    public function arCreditMemoApplicationsAsTarget()
    {
        return $this->hasMany(\App\Models\ARCreditMemoApplication::class, 'target_ar_id', 'id');
    }

    /**
     * Get the balance amount (calculated field based on total payments)
     */
    public function getBalanceAmountAttribute()
    {
        $totalPaid = $this->payments()->sum('payment_amount') ?? 0;
        return $this->total_amount - $totalPaid;
    }

    /**
     * Get total paid amount
     */
    public function getTotalPaidAmountAttribute()
    {
        return $this->payments()->sum('payment_amount') ?? 0;
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAmountAttribute()
    {
        return number_format($this->total_amount, 2);
    }

    /**
     * Get formatted payment amount
     */
    public function getFormattedPaymentAmountAttribute()
    {
        return number_format($this->payment_amount ?? 0, 2);
    }

    /**
     * Get formatted balance amount
     */
    public function getFormattedBalanceAmountAttribute()
    {
        return number_format($this->balance_amount, 2);
    }

    /**
     * Check if payment is overdue
     */
    public function getIsOverdueAttribute()
    {
        if ($this->status === 'Paid') {
            return false;
        }

        // Extract days from terms (e.g., "30 Days" -> 30)
        preg_match('/(\d+)/', $this->terms ?? '0', $matches);
        $termDays = isset($matches[1]) ? (int)$matches[1] : 30;
        
        $dueDate = $this->date->addDays($termDays);
        return now()->gt($dueDate);
    }

    /**
     * Get the due date
     */
    public function getDueDateAttribute()
    {
        preg_match('/(\d+)/', $this->terms ?? '0', $matches);
        $termDays = isset($matches[1]) ? (int)$matches[1] : 30;
        
        return $this->date->addDays($termDays);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by customer
     */
    public function scopeByCustomer($query, $customerCode)
    {
        return $query->where('customer_code', $customerCode);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get outstanding payments only
     */
    public function scopeOutstanding($query)
    {
        return $query->where('status', 'Outstanding');
    }

    /**
     * Scope to get settled payments only
     */
    public function scopeSettled($query)
    {
        return $query->where('status', 'Settled');
    }

    /**
     * Scope to get overdue payments
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'Outstanding')
                    ->whereRaw("DATEADD(day, CAST(SUBSTRING(ISNULL(terms, '30'), PATINDEX('%[0-9]%', ISNULL(terms, '30')), PATINDEX('%[^0-9]%', SUBSTRING(ISNULL(terms, '30'), PATINDEX('%[0-9]%', ISNULL(terms, '30')), LEN(ISNULL(terms, '30')))) - 1) AS INT), date) < GETDATE()");
    }

    /**
     * Update payment information
     */
    public function updatePayment($amount, $type = 'Full', $remarks = null)
    {
        $this->payment_amount = $amount;
        $this->payment_type = $type;
        $this->payment_date = now();
        $this->payment_remarks = $remarks;
        
        // If full payment or payment equals total amount, mark as settled
        if ($type === 'Full' || $amount >= $this->total_amount) {
            $this->status = 'Settled';
            $this->payment_amount = $this->total_amount;
        }
        
        $this->save();
        return $this;
    }

    /**
     * Get the current balance using the new current_balance column
     */
    public function getCurrentBalanceAttribute()
    {
        return $this->current_balance ?? $this->balance_amount;
    }

    /**
     * Update the current balance and last update timestamp
     */
    public function updateCurrentBalance()
    {
        // $totalPaid = $this->payments()->sum('payment_amount') ?? 0;
        $totalPaid = $this->payment_amount ?? 0;
        $this->current_balance = $this->total_amount - $totalPaid;
        $this->last_balance_update = now();
        $this->save();
        
        return $this->current_balance;
    }

    /**
     * Recalculate and update all balance fields
     */
    public function recalculateBalances()
    {
        // Update current balance
        $this->updateCurrentBalance();
        
        // Update credit amounts if needed
        $this->last_balance_update = now();
        
        $this->save();
        
        return $this;
    }
}