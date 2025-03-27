<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class shell_rr_items extends Model
{
    protected $table = 'TBLSHELL_RR_ITEMS';
    public $timestamps = false;
    
    protected $fillable = [
        'PRD_INDEX',
        'SKU',
        'Decription',
        'Quantity',
        'UOM',
        'WhsCode',
        'UnitPrice',
        'NetVat',
        'Vat',
        'Gross',
        'RRNo',
    ];
}
