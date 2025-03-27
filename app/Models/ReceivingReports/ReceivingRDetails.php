<?php

namespace App\Models\ReceivingReports;

use App\Models\Product;
use App\Models\ReceivingReports\ReceivingRHeader;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReceivingRDetails extends Model
{
    use HasFactory;

    protected $table = 'tblInvRRDetails';
    protected $primaryKey = 'id';

    protected $fillable = [
        'SKU',
        'Description',
        'Quantity',
        'UOM',
        'WhsCode',
        'UnitPrice',
        'NetVat',
        'Vat',
        'Gross',
        'RRNo',
        'LOCALID_DETAILS',
        'PO_NUMBER'
    ]; 

    protected $casts = [
        'SKU' => 'string',
        'StockCode' => 'string',
    ];

    public function rrheader()
{
    return $this->belongsTo(ReceivingRHeader::class, 'RRNo', 'RRNo')
        ->select(['RRNo', 'Total', 'RRDATE', 'RECEIVEDBY', 'DATECREATED', 'Reference', 'Status']);
}

    public function product()
    {
        return $this->belongsTo(Product::class, 'SKU', 'StockCode')
            ->select('StockCode', 'Description', 'StockUom', 'AlternateUom','OtherUom','ConvFactAltUom', 'ConvFactOthUom');
    }

    

}
