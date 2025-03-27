<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pamasterlist_model extends Model
{
    use HasFactory;

    // const CREATED_AT = 'oDateTimeIn';
    protected $table = 'pamasterlist_table';
    
    public $timestamps = true;
    const UPDATED_AT = 'DateUpdated';

    protected $fillable = [
        'PeriodYear',
        'PeriodMonth',
        'BusinessUnit',
        'PAType',
        'CustomerClass',
        'StockCode',
        'DropSize',
        'Points',
        'Amount',
        'BonusPoint',
        'MHCount',
        'UpdatedBy',
        'DateUpdated'
    ];

}
