<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualTransaction extends Model
{
    use HasFactory;

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
}
