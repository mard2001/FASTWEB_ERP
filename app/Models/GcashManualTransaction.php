<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GcashManualTransaction extends Model
{
    use HasFactory;

    protected $table = 'tblGcashManualTransaction';
    protected $primaryKey = 'ManualTransactionID';
    public $incrementing = true;

    const UPDATED_AT = 'DateUpdated';
    const CREATED_AT = 'DateCreated';

    protected $fillable = [
        'GcashID',
        'TransactionType', // 'IN' for deposits, 'OUT' for withdrawals
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

    // Relationship with Gcash
    public function gcash()
    {
        return $this->belongsTo(Gcash::class, 'GcashID', 'GcashID');
    }

    // Relationship with user who created the transaction
    public function creator()
    {
        return $this->belongsTo(ERPUser::class, 'CreatedBy', 'id');
    }
}