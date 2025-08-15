<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ActivityLoggable;

class AccountsPayable extends Model
{
    use HasFactory, ActivityLoggable;

    protected $table = 'accounts_payable';

    protected $fillable = [
        'branch',
        'opening_balance',
        'invoices',
        'debit_notes',
        'credit_notes',
        'adjustments',
        'disbursements',
        'revaluation',
        'tax_relief',
        'withholding_tax',
        'closing_balance',
        'report_date'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'invoices' => 'decimal:2',
        'debit_notes' => 'decimal:2',
        'credit_notes' => 'decimal:2',
        'adjustments' => 'decimal:2',
        'disbursements' => 'decimal:2',
        'revaluation' => 'decimal:2',
        'tax_relief' => 'decimal:2',
        'withholding_tax' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'report_date' => 'date'
    ];

    /**
     * Get the status of the account based on closing balance
     */
    public function getStatusAttribute()
    {
        if ($this->closing_balance > 0) {
            return 'Outstanding';
        } elseif ($this->closing_balance == 0) {
            return 'Settled';
        } else {
            return 'Credit Balance';
        }
    }

    /**
     * Get formatted closing balance
     */
    public function getFormattedClosingBalanceAttribute()
    {
        return number_format($this->closing_balance, 2);
    }

    /**
     * Get formatted opening balance
     */
    public function getFormattedOpeningBalanceAttribute()
    {
        return number_format($this->opening_balance, 2);
    }

    /**
     * Get formatted invoices amount
     */
    public function getFormattedInvoicesAttribute()
    {
        return number_format($this->invoices, 2);
    }

    /**
     * Scope to exclude total rows
     */
    public function scopeExcludeTotal($query)
    {
        return $query->where('branch', '!=', 'TOTAL');
    }

    /**
     * Scope to get only total rows
     */
    public function scopeOnlyTotal($query)
    {
        return $query->where('branch', '=', 'TOTAL');
    }

    /**
     * Scope to filter by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('report_date', [$startDate, $endDate]);
    }

    /**
     * Scope to get outstanding balances only
     */
    public function scopeOutstanding($query)
    {
        return $query->where('closing_balance', '>', 0);
    }
}