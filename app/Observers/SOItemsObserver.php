<?php

namespace App\Observers;

use App\Models\SalesOrder\SODetail;
use App\Models\SalesOrder\SOMaster;

class SOItemsObserver
{
    public function creating(SODetail $sodetail)
    {
        // Get the last SalesOrderLine for this SalesOrder
        $lastLine = SODetail::where('SalesOrder', $sodetail->SalesOrder)
            ->orderByDesc('SalesOrderLine')
            ->value('SalesOrderLine');
        $productDetails = optional($sodetail->productdetails()->first());

        $sodetail->SalesOrderLine = $lastLine ? $lastLine + 1 : 1;
        $sodetail->MStockUnitMass = $productDetails->Mass;
        $sodetail->MStockUnitVol = $productDetails->Volume;
        $sodetail->MConvFactUnitQ = $productDetails->ConvFactAltUom;
        $sodetail->MAltUomUnitQ = $productDetails->AlternateUom;
        $sodetail->MDecimalsUnitQ = $this->getNumberOfDigits($productDetails->ConvFactAltUom);
        
        SOMaster::where('SalesOrder', $sodetail->SalesOrder)
        ->update(['NextDetailLine' => $sodetail->SalesOrderLine + 1]);
    }

    private function getNumberOfDigits($number) {
        $number = abs((int) trim($number));
        return $number == 0 ? 1 : floor(log10($number)) + 1;
    }
}
