<?php

namespace App\Models\Inventory;

use App\Models\Product;
use App\Models\Salesman\Salesperson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    
    public function productdetails(){
        return $this->hasOne(Product::class, 'StockCode', 'StockCode')
                    ->select('StockCode', 'Description', 'StockUom', 'AlternateUom', 'OtherUom', 'ConvFactAltUom', 'ConvFactOthUom', 'ProductClass', 'Brand');
    }

    public function salesmandetails(){
        return $this->hasOne(Salesperson::class, 'Salesperson', 'Salesperson')
                    ->select('EmployeeID','Name','Salesperson');
    }

    public function prodname(){
        return $this->hasOne(Product::class, 'StockCode', 'StockCode')
            ->select('Description')->first();
    }
}
