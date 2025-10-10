<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gcash extends Model
{
    use HasFactory;

    protected $table = 'tblGcash';
    protected $primaryKey = 'GcashID';
    public $incrementing = true;

    const UPDATED_AT = 'DateUpdated';
    const CREATED_AT = 'DateCreated';

    protected $fillable = [
        'AccountNumber',
        'AccountName',
        'Status',
    ];

    protected $dates = [
        'DateCreated',
        'DateUpdated',
    ];

    // Relationship with GcashReconciliation
    public function reconciliations()
    {
        return $this->hasMany(GcashReconciliation::class, 'GcashID', 'GcashID');
    }

    // Relationship with GcashManualTransaction
    public function manualTransactions()
    {
        return $this->hasMany(GcashManualTransaction::class, 'GcashID', 'GcashID');
    }
}
