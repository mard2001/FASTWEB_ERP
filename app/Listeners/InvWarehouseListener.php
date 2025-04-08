<?php

namespace App\Listeners;

use App\Models\Inventory\InvWarehouse;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\Inventory\InventoryWarehouse;

class InvWarehouseListener
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\Inventory\InventoryWarehouse  $event
     * @return void
     */
    public function handle(InventoryWarehouse $event)
    {
        $sku = $event->sku;
        $warehouse = $event->warehouse;
        $qty = $event->qty;

        $existing = InvWarehouse::where('StockCode', $sku)
            ->where('Warehouse', $warehouse)
            ->first();

        if($existing){
            InvWarehouse::where('StockCode', $sku)
            ->where('Warehouse', $warehouse)
            ->update([
                'QtyOnHand' => $existing->QtyOnHand + $qty,
                'DateLastStockMove' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
            ]);
        } else{
            InvWarehouse::create([
                'StockCode' => $sku,
                'Warehouse' => $warehouse,
                'QtyOnHand' => $qty,
                'DateLastStockMove' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                'DateWhAdded' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
            ]);
        }
    }
}
