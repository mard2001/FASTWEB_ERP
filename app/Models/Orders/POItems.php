<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Model;
use App\Models\Orders\PO;
use App\Observers\POItemsObserver;


class POItems extends Model
{
    protected $table = 'tblPODetails';
    public $timestamps = false;

    protected $fillable = [
        'PRD_INDEX',
        'PONumber',
        'StockCode',
        'Decription',
        'Quantity',
        'UOM',
        'DeliveredQuantity',
        'ItemVolume',
        'ItemVolumeUOM',
        'ItemWeight',
        'ItemWeightUOM',
        'TotalPrice',
        'UsedCurrency',
        'PricePerUnit',
        'HeaderParentId'
    ];


    public function POHeader()
    {
        return $this->belongsTo(PO::class, 'PONumber', 'PONumber');
    }

    protected static function boot()    
    {

        parent::boot();
        POItems::observe(POItemsObserver::class);


        // static::creating(function (POItems $poItem) {

        //     $calculatorService = new CalculatorService();
        //     $extractedPieces = $calculatorService->getTotalQtyInPCS($poItem->StockCode, $poItem->Quantity);
        //     $poItem->TotalQtyInPCS = $extractedPieces;

        // });


        


        // static::updated(function (POItems $poItem) {
        //     if ($poItem->isDirty('price')) {
        //         $oldPrice = $poItem->getOriginal('price');
        //         $newPrice = $poItem->price;
        //         $difference = $newPrice - $oldPrice;
        //         $poItem->po->increment('total_price', $difference);
        //     }
        // });
    }
}
