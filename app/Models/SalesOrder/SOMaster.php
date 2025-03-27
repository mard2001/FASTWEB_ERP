<?php

namespace App\Models\SalesOrder;

use App\Observers\SOObserver;
use App\Models\SalesOrder\SODetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SOMaster extends Model
{
    use HasFactory;

    protected $table = "SorMaster";
    protected $primaryKey = 'SalesOrder';
    public $incrementing = false;  
    protected $keyType = 'string'; 
    public $timestamps = false;

    protected $casts = [
        'SalesOrder' => 'string', 
    ];

    protected $fillable = [
        'NextDetailLine',
        'OrderStatus',
        'DocumentType',
        'Customer',
        'Salesperson',
        'CustomerPoNumber',
        'OrderDate',
        'EntrySystemDate',
        'ReqShipDate',
        'DateLastDocPrt',
        'AltShipAddrFlag',
        'InvoiceCount',
        'InvTermsOverride',
        'Branch',
        'OrderType',
        'CashCredit',
        'Warehouse',
        'Currency',
        'OrderStatusFail',
        'LastOperator',
        'CustomerName',
        'ShipAddress1',
        'ShipAddress2',
        'ShipAddress3',
        'ShipToGpsLat',
        'ShipToGpsLong',
        'CompanyTaxNo',
        'ApproveDate',
        'Approver'
    ];

    protected $attributes = [
        'DocumentType' => 'B',
        'OrderStatus' => '2',
        'AltShipAddrFlag' => 'Y',
        'OrderType' => 'N',
        'TaxExemptFlag' => 0,
        'CashCredit' => 'CR',
        'GstExemptFlag' => 'E',
        'GstExemptORide' => 'E',
        'OrdAckNwPrinted' => 'N',
        'DetCustMvmtReqd' => 'Y',
        'DocumentFormat' => 0,
        'FixExchangeRate' => 'N',
        'ExchangeRate' => 1,
        'MulDiv' => 'M',
        'Currency' => 'PHP',
        'GstDeduction' => 'I',
        'OrderStatusFail' => 0,
        'SerialisedFlag' => 'N',


    ];

    protected static function boot()
    {
        parent::boot();
        SOMaster::observe(SOObserver::class);

    }

    public function sodetails()
    {
        return $this->hasMany(SODetail::class, 'SalesOrder', 'SalesOrder');
    }
    // public function sodetails()
    // {
    //     return $this->hasMany(SODetail::class, 'SalesOrder', 'SalesOrder');
    // }
}
