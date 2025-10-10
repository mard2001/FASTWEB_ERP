<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GcashReconciliation extends Model
{
    use HasFactory;

    protected $table = 'tblGcashReconciliation';
    protected $primaryKey = 'ReconciliationID';
    public $incrementing = true;

    const UPDATED_AT = 'DateUpdated';
    const CREATED_AT = 'DateCreated';

    protected $fillable = [
        'GcashID',
        'ReconciliationDate',
        'BeginningBalance',
        'TotalInflows',
        'TotalOutflows',
        'AvailableBalance', // Now calculated in code
        'Notes',
    ];

    protected $dates = [
        'ReconciliationDate',
        'DateCreated',
        'DateUpdated',
    ];

    // Relationship with Gcash
    public function gcash()
    {
        return $this->belongsTo(Gcash::class, 'GcashID', 'GcashID');
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