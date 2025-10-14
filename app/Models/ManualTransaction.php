<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ActivityLoggable;

class ManualTransaction extends Model
{
    use HasFactory, ActivityLoggable;

    protected $table = 'tblManualTransactions';
    protected $primaryKey = 'ManualTransactionID';
    public $incrementing = true;

    const UPDATED_AT = 'DateUpdated';
    const CREATED_AT = 'DateCreated';

    protected $fillable = [
        'BankID',
        'TransactionType',
        'Amount',
        'TransactionDate',
        'ReferenceNumber',
        'Remarks',
        'CreatedBy',
    ];

    protected $dates = [
        'TransactionDate',
        'DateCreated',
        'DateUpdated',
    ];

    // Relationship with Bank
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'BankID', 'BankID');
    }

    /**
     * Get the log name for this model.
     */
    protected function getLogName(): string
    {
        return 'bank_reconciliation';
    }

    /**
     * Get the log description for different events.
     */
    protected function getLogDescription(string $eventName): string
    {
        $transactionType = $this->TransactionType === 'IN' ? 'deposit' : 'withdrawal';
        $bankName = $this->bank ? $this->bank->BankName : 'Unknown Bank';
        $amount = $this->Amount ? '₱' . number_format($this->Amount, 2) : '';
        
        $descriptions = [
            'created' => "Created manual {$transactionType} for '{$bankName}': {$amount}",
            'updated' => "Updated manual {$transactionType} for '{$bankName}': {$amount}", 
            'deleted' => "Deleted manual {$transactionType} for '{$bankName}': {$amount}",
        ];

        return $descriptions[$eventName] ?? "{$eventName} manual transaction";
    }
}
