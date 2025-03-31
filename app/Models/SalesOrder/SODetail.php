<?php

namespace App\Models\SalesOrder;

use App\Models\Product;
use App\Observers\SOItemsObserver;
use App\Models\SalesOrder\SOMaster;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SODetail extends Model
{
    use HasFactory;

    protected $table = "SorDetail";

    public $timestamps = false; 

    // protected $casts = [
    //     'SalesOrder' => 'string',  
    //     'SalesOrderLine' => 'integer',  
    // ];

    protected $fillable = [
        'SalesOrder',
        'SalesOrderLine',
        'LineType',
        'MStockCode',
        'MStockDes',
        'MWarehouse',
        'MOrderQty',
        'MDecimales',
        'MOrderUom',
        'MStockQtyToShp',
        'MStockingUom',
        'MConvFactOrdUm',
        'MMulDivPrcFct',
        'MPrice',
        'MPriceUom',
        'MUnitCost',
        'MProductClass',
        'MLineShipDate',
        'MStockUnitMass',
        'MStockUnitVol',
        'MPriceCode',
        'MConvFactAlloc',
        'MConvFactUnitQ',
        'MAltUomUnitQ',
        'MDecimalUnitQ',
        'MParentKitType',
        'MPrintComponent',
        'MMulDivQtyFct',
        'MTraceableType',
        'MMpsFlag',
        'MMovementReqd',
        'MSerialMethod',
        'MMpsGrossReqd',
        'MSupplementaryUn',
        'MUnitQuantity',
        'QTYinPCS'
    ];
    
    protected $attributes = [
        'LineType' => 1,
        'MWarehouse' => 'M1',
        'MParentKitType' => 'N',
        'MPrintComponent' => 'Y',
        'MMulDivPrcFct' => 'M',
        'MMulDivQtyFct' => 'M',
        'MTraceableType' => 'N',
        'MMpsFlag' => 'N',
        'MMovementReqd' => 'Y',
        'MSerialMethod' => 'N',
        'MMpsGrossReqd' => 'I',
        'MSupplementaryUn' => 'N',
        'MUnitQuantity' => 'Y',

    ];

    // protected static function booted()
    // {
    //     static::observe(\App\Observers\SOItemsObserver::class);
    // }

    public function somaster()
    {
        return $this->belongsTo(SOMaster::class, 'SalesOrder', 'SalesOrder')
                ->select(['SalesOrder', 'NextDetailLine', 'OrderStatus', 'DocumentType', 'Customer', 'CustomerName', 'Salesperson', 'CustomerPoNumber', 'OrderDate', 'EntrySystemDate', 'ReqShipDate', 'DateLastDocPrt', 'InvoiceCount', 'Branch', 'Warehouse', 'ShipAddress1', 'ShipToGpsLat', 'ShipToGpsLong',]);
    }

    public function productdetails()
    {
        return $this->belongsTo(Product::class, 'MStockCode', 'StockCode');
    }
}
