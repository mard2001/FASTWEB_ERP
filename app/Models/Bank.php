<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    use HasFactory;

    protected $table = 'tblBank';
    protected $primaryKey = 'BankID';
    public $incrementing = true;

    const UPDATED_AT = 'DateUpdated';
    const CREATED_AT = 'DateCreated';

    protected $fillable = [
        'BankName',
        'AccountName',
        'AccountNumber',
        'CardNumber',
        'ExpirationDate',
        'CCV',
        'Address',
        'ContactNumber',
        'AccountType',
        'Status',
    ];

    protected $dates = [
        'ExpirationDate',
        'DateCreated',
        'DateUpdated',
    ];

    // Relationship with BankReconciliation
    public function reconciliations()
    {
        return $this->hasMany(BankReconciliation::class, 'BankID', 'BankID');
    }
}
