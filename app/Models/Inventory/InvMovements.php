<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvMovements extends Model
{
    use HasFactory;

    protected $table = 'InvMovements';

    public $timestamps = false;

    protected $fillable = [
        'StockCode',
        'Warehouse',
        'TrnYear',
        'TrnMonth',
        'EntryDate',
        'MovementType',
        'TrnQty',
        'TrnValue',
        'Reference',
        'UnitCost',
        'SalesOrder',
        'Customer',
        'Branch',
        'Salesperson',
        'CustomerPoNumber',
        'OrderType',
        'ProductClass',
        'DetailLine',
    ]; 
}
