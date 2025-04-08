<?php

namespace App\Events\Inventory;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryWarehouse
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $sku;
    public $warehouse;
    public $qty;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($sku, $warehouse, $qty)
    {
        $this->sku = $sku;
        $this->warehouse = $warehouse;
        $this->qty = $qty;
    }
}
