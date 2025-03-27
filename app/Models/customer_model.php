<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class customer_model extends Model
{
    use HasFactory;
    protected $table = 'erp_customer';

    public $timestamps = false;
    protected $fillable = [
        'id',
        'customerID',
        'mdCode',
        'custCode',
        'custName',
        'contactCellNumber',
        'contactPerson',
        'contactLandline',
        'address',
        'frequencyCategory',
        'mcpDay',
        'mcpSchedule',
        'geolocation',
        'lastUpdated',
        'lastPurchase',
        'latitude',
        'longitude',
        'storeImage',
        'syncstat',
        'dates_tamp',
        'time_stamp',
        'isLockOn',
        'priceCode',
        'storeImage2',
        'custType',
        'isVisit',
        'DefaultOrdType',
        'CityMunCode',
        'REGION',
        'PROVINCE',
        'MUNICIPALITY',
        'BARANGAY',
        'Area',
        'warehouse',
        'KASOSYO',
        'baseGPSLat'
    ];
    
}
