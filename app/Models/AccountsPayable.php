<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ActivityLoggable;

class AccountsPayable extends Model
{
    use HasFactory, ActivityLoggable;

    protected $table = 'tblAccountsPayable';

    protected $fillable = [
        'date',
        'supplier_code',
        'supplier_name',
        'rr_number',
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
        'CreditMemo',
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
        'CreditMemo' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'credit_generated' => 'decimal:2',
        'credit_received' => 'decimal:2',
        'last_balance_update' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relationship with Supplier
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_code', 'SupplierCode');
    }

    /**
     * Relationship with Payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'accounts_payable_id', 'id');
    }

    /**
     * Credit memo applications where this AP is the source (generates credit)
     */
    public function creditMemosGenerated()
    {
        return $this->hasMany(CreditMemoApplication::class, 'source_ap_id', 'id');
    }

    /**
     * Credit memo applications where this AP is the target (receives credit)
     */
    public function creditMemosReceived()
    {
        return $this->hasMany(CreditMemoApplication::class, 'target_ap_id', 'id');
    }

    /**
     * Running balance entries for this AP transaction
     */
    public function runningBalanceEntries()
    {
        return $this->hasMany(SupplierRunningBalance::class, 'ap_transaction_id', 'id');
    }

    /**
     * Get the balance amount (calculated field based on total payments)
     */
    public function getBalanceAmountAttribute()
    {
        $totalPaid = $this->payments()->sum('payment_amount') ?? 0;
        $totalCreditMemo = $this->CreditMemo ?? 0;
        
        // Adjust total paid by subtracting credit memos to get actual amount applied to balance
        $actualTotalPaid = $totalPaid - $totalCreditMemo;
        return $this->total_amount - $actualTotalPaid;
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
     * Scope to filter by supplier
     */
    public function scopeBySupplier($query, $supplierCode)
    {
        return $query->where('supplier_code', $supplierCode);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to get pending payments only
     */
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    /**
     * Scope to get paid payments only
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'Paid');
    }

    /**
     * Scope to get overdue payments
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'Pending')
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
        
        // If full payment or payment equals total amount, mark as paid
        if ($type === 'Full' || $amount >= $this->total_amount) {
            $this->status = 'Paid';
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
        $totalPaid = $this->payments()->sum('payment_amount') ?? 0;
        $this->current_balance = $this->total_amount - $totalPaid;
        $this->last_balance_update = now();
        $this->save();
        
        return $this->current_balance;
    }

    /**
     * Get total credit generated by this AP record
     */
    public function getTotalCreditGeneratedAttribute()
    {
        return $this->credit_generated ?? $this->creditMemosGenerated()->sum('credit_amount');
    }

    /**
     * Get total credit received by this AP record
     */
    public function getTotalCreditReceivedAttribute()
    {
        return $this->credit_received ?? $this->creditMemosReceived()->sum('credit_amount');
    }

    /**
     * Check if this AP record has generated any credit memos
     */
    public function hasGeneratedCredits()
    {
        return $this->total_credit_generated > 0;
    }

    /**
     * Check if this AP record has received any credit memos
     */
    public function hasReceivedCredits()
    {
        return $this->total_credit_received > 0;
    }

    /**
     * Get the latest running balance for this supplier
     */
    public function getSupplierLatestBalance()
    {
        return SupplierRunningBalance::getLatestBalance($this->supplier_code);
    }

    /**
     * Recalculate and update all balance fields
     */
    public function recalculateBalances()
    {
        // Update current balance
        $this->updateCurrentBalance();
        
        // Update credit amounts
        $this->credit_generated = $this->creditMemosGenerated()->sum('credit_amount');
        $this->credit_received = $this->creditMemosReceived()->sum('credit_amount');
        $this->last_balance_update = now();
        
        $this->save();
        
        return $this;
    }
}