<?php

namespace App\Models\Customer;

use App\Models\Salesman\Salesperson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;
    protected $table = "tblCustomer";

    protected $primaryKey = 'Customer'; 
    public $incrementing = false; 

    public $timestamps = false;

    protected $hidden = ['time_stamp'];

    protected $fillable = [
        'Customer',
        "Name",
        "ShortName",
        "ExemptFinChg",
        "MaintHistory",
        "CreditLimit",
        "InvoiceCount",
        "Salesperson",
        "PriceCode",
        "CustomerClass",
        "Branch",
        "TermsCode",
        "BalanceType",
        "Area",
        "TaxStatus",
        "PriceCategoryTable",
        "DateLastSale",
        "DateLastPay",
        "Telephone",
        "Contact",
        "Currency",
        "UserField1",
        "GstExemptFlag",
        "GstLevel",
        "DetailMoveReqd",
        "InterfaceFlag",
        "ContractPrcReqd",
        "StatementReqd",
        "BackOrdReqd",
        "DateCustAdded",
        "StockInterchange",
        "MaintLastPrcPaid",
        "IbtCustomer",
        "CounterSlsOnly",
        "HighestBalance",
        "CustomerOnHold",
        "SoldToAddr1",
        "SoldToAddr2",
        "SoldToAddr3",
        "ShipToAddr1",
        "ShipToAddr2",
        "ShipToAddr3",
        'ExemptFinChg',
        'MaintHistory',
        'CreditLimit',
        'Branch',
        'TermsCode',
        'BalanceType',
        'TaxStatus',
        'Currency',
        'GstExemptFlag',
        'GstLevel',
        'DetailMoveReqd',
        'InterfaceFlag',
        'ContractPrcReqd',
        'StatementReqd',
        'BackOrdReqd',
        'StockInterchange',
        'MaintLastPrcPaid',
        'IbtCustomer',
        'CounterSlsOnly',
    ];

    protected $attributes = [
        'ExemptFinChg' => 'Y',
        'MaintHistory' => 'Y',
        'CreditLimit' => 50000,
        'Branch' => 'TD',
        'TermsCode' => 'N1',
        'BalanceType' => 'I',
        'TaxStatus' => 'N',
        'Currency' => 'PHP',
        'GstExemptFlag' => 'E',
        'GstLevel' => 'I',
        'DetailMoveReqd' => 'Y',
        'InterfaceFlag' => 'Y',
        'ContractPrcReqd' => 'Y',
        'StatementReqd' => 'Y',
        'BackOrdReqd' => 'Y',
        'StockInterchange' => 'N',
        'MaintLastPrcPaid' => 'N',
        'IbtCustomer' => 'N',
        'CounterSlsOnly' => 'N',
    ];


    public function salesman(){
        return $this->belongsTo(Salesperson::class, "Salesperson", "Salesperson");
    }
}
