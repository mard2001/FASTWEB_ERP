<?php

namespace App\Events\Inventory;

use App\Models\Inventory\InvWarehouse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryMovement
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $movementType;

    public $data;

    public $rrHeaderDetails;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($rrHeaderDetails, $data, $movementType)
    {
        $this->rrHeaderDetails = $rrHeaderDetails;
        $this->data = $data;
        $this->movementType = $movementType;
    }
}
