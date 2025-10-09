<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankReconciliation extends Model
{
    use HasFactory;

    protected $table = 'tblBankReconciliation';
    protected $primaryKey = 'ReconciliationID';
    public $incrementing = true;

    const UPDATED_AT = 'DateUpdated';
    const CREATED_AT = 'DateCreated';

    protected $fillable = [
        'BankID',
        'ReconciliationDate',
        'BeginningBalance',
        'TotalInflows',
        'TotalOutflows',
        'AvailableBalance', // Now calculated in code, not SQL Server
        'Notes',
    ];

    protected $dates = [
        'ReconciliationDate',
        'DateCreated',
        'DateUpdated',
    ];

    // Relationship with Bank
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'BankID', 'BankID');
    }

    /**
     * Calculate available balance
     * Formula: BeginningBalance + TotalInflows - TotalOutflows
     *
     * @return float
     */
    public function calculateAvailableBalance()
    {
        return ($this->BeginningBalance ?? 0) + ($this->TotalInflows ?? 0) - ($this->TotalOutflows ?? 0);
    }

    /**
     * Accessor for AvailableBalance - calculates if not set
     *
     * @param  mixed  $value
     * @return float
     */
    public function getAvailableBalanceAttribute($value)
    {
        // If value exists in database, return it; otherwise calculate
        return $value ?? $this->calculateAvailableBalance();
    }
}
