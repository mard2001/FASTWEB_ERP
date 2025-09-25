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
        'CreditMemo'
    ];

    protected $casts = [
        'date' => 'date',
        'total_amount' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'payment_date' => 'date',
        'CreditMemo' => 'decimal:2',
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
}