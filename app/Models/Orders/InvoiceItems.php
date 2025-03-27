<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class shell_invoice_items extends Model
{
    protected $table = 'TBLSHELL_INVOICE_ITEMS';
    public $timestamps = false;

    protected $fillable = [
        'PRD_INDEX',
        'totalPrice',
        'pricePerUnit',
        'UOM',
        'subUOM',
        'quantity',
        'totalQuantityInUOM',
        'orderDate',
        'deliveryDate',
        'productCode',
        'deliveryNumber',
        'orderNumber',
        'discountPerUnit',
        'totalDiscountPerUnit',
        'netPricePerUnit',
        'totalNetPrice',
        'itemDescription',
        'invoiceNumber'
    ];

    // public function invoice()
    // {
    //     return $this->belongsTo(shell_invoice::class, 'invoiceNumber'); // foreign key
    // }


}
