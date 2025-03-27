<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class patarget_model extends Model
{
    use HasFactory;

    protected $table = 'patarget_table';
    public $timestamps = false;
    protected $fillable = [
        'Type',
        'PeriodYear',
        'PeriodMonth',
        'StockCode',
        'Description',
        'OutletClassCode',
        'Target',
        'PAType',
        'NewProduct',
        'BusinessUnit',
        'DropSize',
        'Points',
        'Amount',
        'BonusPoint',
        'MHCount',
        'Teir',
        'Activity_Type',
        'Start_date',
        'End_Date',   
    ];
}
