<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class salesman_model extends Model
{
    use HasFactory;

    protected $table = 'salesman_table'; 
    const created_at = 'mdUserCreated';
    const UPDATED_AT = null;

    public $timestamps = true;
    protected $fillable = [
        'id',
        'mdCode',
        'mdPassword',
        'mdLevel',
        'mdSalesmancode',
        'mdName',
        'siteCode',
        'eodNumber1',
        'eodNumber2',
        'contactCellNumber',
        'mdColor',
        'priceCode',
        'StockTakeCL',
        'EOD',
        'DefaultOrdType',
        'stkRequired',
        'calltime',
        'loadingCap',
        'isActive',
        'PhoneSN',
        'verNumber',
        'ImmediateHead',
        'SalesmanType',
        'WarehouseCode',
        'uploaded_image'
    ];


}
