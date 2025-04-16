<?php

namespace App\Models\Inventory;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    public function productdetails(){
        return $this->hasOne(Product::class, 'StockCode', 'StockCode')
            ->select('StockCode', 'Description', 'LongDesc', 'StockUom', 'AlternateUom', 'OtherUom', 'ConvFactAltUom', 'ConvFactOthUom', 'ProductClass', 'Brand');
    }

    public function prodname(){
        return $this->hasOne(Product::class, 'StockCode', 'StockCode')
            ->select('StockCode','Description');
    }
}
