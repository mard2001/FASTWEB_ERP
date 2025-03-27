<?php

namespace App\Models\Inventory;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CSDetails extends Model
{
    use HasFactory;

    protected $table = 'TBLINVCOUNT_DETAILS';
    
    public $timestamps = false;
    
    protected $fillable = [
        'STATUS',
    ];

    public function header(){
        return $this->belongsTo(CSHEADER::class,'CNTHEADER_ID','CNTHEADER_ID');
    }

    public function proddetails(){
        return $this->belongsTo(Product::class,'STOCKCODE','StockCode');
    }
}
