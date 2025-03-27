<?php

namespace App\Observers;

use App\Models\SalesOrder\SODetail;

class SOItemsObserver
{
    public function creating(SODetail $sodetail)
    {
        // Get the last SalesOrderLine for this SalesOrder
        $lastLine = SODetail::where('SalesOrder', $sodetail->SalesOrder)
            ->orderByDesc('SalesOrderLine')
            ->value('SalesOrderLine');

        // Set the new SalesOrderLine (incremented)
        $sodetail->SalesOrderLine = $lastLine ? $lastLine + 1 : 1;
        
    }
}
