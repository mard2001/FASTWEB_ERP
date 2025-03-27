<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class ProductPrices extends Model
{
    use HasFactory;

    protected $table = 'tblPrice';

    public function product()
    {
        // return $this->belongsTo(Product::class, 'STOCKCODE', 'StockCode');
        return $this->belongsTo(Product::class, 'STOCKCODE', 'StockCode');


    }

}
