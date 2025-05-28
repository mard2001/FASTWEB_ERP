<?php

namespace App\Models\Inventory;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvAdjustmentLogs extends Model
{
    use HasFactory;

    protected $table = 'InvAdjustmentLogs';

    public $timestamps = false;

    protected $fillable = [
        'REFERENCE',
        'STOCKCODE',
        'WAREHOUSE',
        'ENTRY_DATE',
        'PREV_QTY',
        'NEW_QTY',
        'ADJUSTED_QTY',
        'ADJUSTMENT_TYPE',
        'REASON',
        'HANDLED_BY'
    ]; 

    public function productdetails()
    {
        return $this->hasOne(Product::class, 'StockCode', 'STOCKCODE')
            ->select(['StockCode', 'Description']); 
    }
}
