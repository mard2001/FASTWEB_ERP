<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvWarehouse extends Model
{
    use HasFactory;

    public $incrementing = false;
    
    protected $primaryKey = ['StockCode', 'Warehouse'];

    protected $table = 'InvWarehouse';
    
    public $timestamps = false;

    protected $fillable = [
        'StockCode',
        'Warehouse',
        'QtyOnHand',
        'DateLastStockMove',
        'DateWhAdded',
    ]; 

    protected $casts = [
        'QtyOnHand' => 'float',
    ];
}
