<?php

namespace App\Models;

use App\Models\ReceivingReports\ReceivingRDetails;
use App\Models\ProductPrices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\FfgModelFactory> */
    use HasFactory;

    protected $table = 'InvMaster';
    protected $primaryKey = 'StockCode'; 
    public $incrementing = false; 

    public $timestamps = false;
    protected $hidden = ['TimeStamp'];

    // // Specify the primary key column name
    // protected $primaryKey = 'productID';

    // protected $fillable = [
    //     'StockCode',
    //     'Description',
    //     'Brand',
    //     'StockUom',
    //     'priceWithVat',
    //     'time_stamp',
    //     'buyingAccounts',
    // ];

    

    protected $fillable = [
        'StockCode',
        'Description',
        'LongDesc',
        'AlternateKey1',
        // 'AlternateKey2',
        // 'EccUser',
        'StockUom',
        'AlternateUom',
        'OtherUom',
        'ConvFactAltUom',
        'ConvFactOthUom',
        'Mass',
        'Volume',
        'Supplier',
        // 'OtherTaxCode',
        // 'Buyer',
        // 'Planner',
        // 'SupercessionDate',
        'MinPricePct',
        'LabourCost',
        'MaterialCost',
        'FixOverhead',
        'VariableOverhead',
        // 'DrawOfficeNum',
        'WarehouseToUse',
        'SpecificGravity',
        'ImplosionNum',
        'ComponentCount',
        'PanSize',
        'DockToStock',
        'ShelfLife',
        // 'Version',
        // 'Release',
        'DemandTimeFence',
        'ManufLeadTime',
        'AbcPreProd',
        'AbcManufacturing',
        'AbcSales',
        'AbcCumPreProd',
        'AbcCumManuf',
        // 'WipCtlGlCode',
        // 'ResourceCode',
        'SerEntryAtSale',
        // 'StpSelection',
        // 'UserField1',
        'UserField2',
        'UserField4',
        // 'UserField5',
        // 'TariffCode',
        // 'StdLandedCost',
        'StdLctRoute',
        'StdLabCostsBill',
        // 'PhantomIfComp',
        // 'CountryOfOrigin',
        // 'StockOnHold',
        // 'StockOnHoldReason',
        // 'JobsOnHold',
        // 'JobHoldAllocs',
        // 'PurchOnHold',
        // 'SalesOnHold',
        // 'MaintOnHold',
        // 'BlanketPoExists',
        // 'CallOffBpoExists',
        // 'DistWarehouseToUse',
        // 'JobClassification',
        'SubContractCost',
        // 'DateStkAdded',
        // 'InspectionFlag',
        // 'SerialPrefix',
        // 'SerialSuffix',
        // 'ReturnableItem',
        // 'ProductGroup',
        // 'PriceType',
        // 'Basis',
        // 'ManualCostFlag',
        // 'ManufactureUom',
        'ConvFactMuM',
        // 'ManMulDiv',
        'LookAheadWin',
        'LoadingFactor',
        // 'SupplUnitCode',
        // 'StorageSecurity',
        // 'StorageHazard',
        // 'StorageCondition',
        'ProductShelfLife',
        'InternalShelfLife',
        // 'AltMethodFlag',
        // 'AltSisoFlag',
        // 'AltReductionFlag',
        // 'WithTaxExpenseType',
        // 'UsesPrefSupplier',
        // 'PrdRecallFlag',
        // 'OnHoldReason',
        'SpecificGravity6',
        'SuppUnitFactor',
        // 'SuppUnitsMulDiv',
        // 'QmInspectionReq',
        'Brand',
    ];

    protected $attributes = [
        'ConvMulDiv' => 'D',
        'MulDiv' => 'D',
        'Decimals' => 6,
        'PriceCategory' => 'A',
        'PriceMethod' => 'C',
        // 'CycleCount' => 0,
        'ProductClass' => 'GROC',
        'TaxCode' => 'D',
        'ListPriceCode' => 1,
        'SerialMethod' => 'N',
        'InterfaceFlag' => 'Y',
        'KitType' => 'N',
        // 'LowLevelCode' => 0,
        'TraceableType' => 'N',
        'MpsFlag' => 'N',
        'BulkIssueFlag' => 0,
        'AbcClass' => 0,
        // 'LeadTime' => 0,
        'StockMovementReq' => 'Y',
        'ClearingFlag' => 'N',
        'AbcAnalysisReq' => 'Y',
        'AbcCostingReq' => 'N',
        'CostUom' => 'CS',
        'PartCategory' => 'B',
        'BuyingRule' => 'A',
        'Ebq' => 1,
        'FixTimePeriod' => 1,
        'OutputMassFlag' => 'F',
        'MakeToOrderFlag' => 'N',
        'GrossReqRule' => 'I',
        'PercentageYield' => 100,
        'GstTaxCode' => 'A',
        'PrcInclGst' => 'N',
        // 'UserField3' => 0,
        'SupplementaryUnit' => 'N',
        'EbqPan' => 'E',
        'LctRequired' => 'N',
        'IssMultLotsFlag' => 'Y',
        'InclInStrValid' => 'Y',
        'EccFlag' => 'N',
        'StockAndAltUm' => 'Y',
        // 'AltUnitChar' => 0,
        'BatchBill' => 'N',
    ];

    protected $casts = [
        'SKU' => 'string',
        'StockCode' => 'string',
    ];

    public function rrdetails()
    {
        return $this->hasMany(ReceivingRDetails::class, 'SKU', 'StockCode');
    }

    public function Prices()
    {
        return $this->hasMany(ProductPrices::class, 'STOCKCODE', 'StockCode');
    }


}

