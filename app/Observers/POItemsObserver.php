<?php

namespace App\Observers;

use App\Models\Orders\POItems;
use App\Services\ProductCalculator;

use App\Models\Supplier;
use App\Models\ProductPrices;

class POItemsObserver
{
    protected $productCalculator;

    public function __construct(ProductCalculator $productCalculator)
    {
        $this->productCalculator = $productCalculator;
    }

    public function creating(POItems $poItem)
    {
        $poItem = $this->validateAndCheckItemPrice($poItem);
    }

    public function updating(POItems $poItem)
    {
        $poItem = $this->validateAndCheckItemPrice($poItem);
    }

    public function updated(POItems $poItem)
    {
        $poItem->POHeader->updateTotalCost();
    }

    public function created(POItems $poItem)
    {
        $poItem->POHeader->updateTotalCost();
    }

    public function deleted(POItems $poItem)
    {
        $poItem->POHeader->updateTotalCost();
    }

    private function validateAndCheckItemPrice(POItems $poItem)
    {
        $convertionResult = $this->productCalculator->getTotalQtyInPCS($poItem->StockCode, $poItem->Quantity, $poItem->UOM);

        if ($convertionResult['success']) {
            $poItem->TotalQtyInPCS = $convertionResult['result'];
        }

        $supplier = Supplier::where('SupplierCode', trim($poItem->POHeader->SupplierCode))->firstOrFail();

        $getProductPrice = ProductPrices::where('STOCKCODE', $poItem->StockCode)->where('PRICECODE', $supplier->PriceCode)->firstOrFail();

        $poItem->PricePerUnit = $getProductPrice->UNITPRICE;
        $poItem->TotalPrice = $poItem->PricePerUnit * $poItem->Quantity;
        
        return $poItem;
    }
}
