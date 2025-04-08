<?php

namespace App\Listeners;

use App\Models\Inventory\InvMovements;
use App\Models\Inventory\InvWarehouse;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\Inventory\InventoryMovement;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvMovement
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\Inventory\InventoryMovement  $event
     * @return void
     */
    public function handle(InventoryMovement $event)
    {
        $movementType = $event->movementType;
        $productData = $event->data;
        $headerData =$event->rrHeaderDetails['rrData'];

        if($movementType == 'I'){
            InvMovements::create([
                'StockCode' => $productData['SKU'],
                'Warehouse' => $productData['warehouse'],
                'TrnYear' => now()->setTimezone('Asia/Manila')->format('Y'),
                'TrnMonth' => now()->setTimezone('Asia/Manila')->format('n'),
                'EntryDate' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                'MovementType' => 'I',
                'TrnQty' => $productData['convertedQuantity']['convertedToLargestUnit'],
                'Reference' => $headerData['Reference'],
                'UnitCost' => $productData['UnitPrice'],
            ]);
        }
    }
}
