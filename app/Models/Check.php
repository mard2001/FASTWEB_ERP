<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Check extends Model
{
    use HasFactory;

    protected $table = 'tblCheck';
    protected $primaryKey = 'CheckID';
    public $incrementing = true;

    const UPDATED_AT = 'DateUpdated';
    const CREATED_AT = 'DateCreated';

    protected $fillable = [
        'BankID',
        'Payee',
        'AmountInWords',
        'CheckDate',
        'CheckAmount',
        'CheckNumber',
        'Status',
        'CreatedBy',
        'Remarks'
    ];

    protected $dates = [
        'CheckDate',
        'DateCreated',
        'DateUpdated',
    ];

    protected $casts = [
        'CheckDate' => 'date',
        'CheckAmount' => 'decimal:2'
    ];
}