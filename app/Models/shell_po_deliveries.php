<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class shell_po_deliveries extends Model
{
    protected $table = 'TBLSHELL_PO_DELIVERIES';
    public $timestamps = false;

    protected $fillable = [
        'PRD_INDEX',
        'PONumber',
        'MaterialCode',
        'Decription',
        'Quantity',
        'UOM',
        'ItemVolume',
        'ItemVolumeUOM',
        'ItemWeight',
        'ItemWeightUOM',
        'ShippingDate',
        'DeliveryDate',
        'DeliveryNumber'
    ];
}
