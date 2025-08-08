<?php

namespace App\Models\Orders;

use Illuminate\Support\Str;
use App\Observers\POObserver;
use App\Models\ReceivingReports\ReceivingRHeader;
use App\Models\Supplier;
use App\Traits\ActivityLoggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PO extends Model
{
    use ActivityLoggable;

    const CREATED_AT = 'DateUploaded';
    const UPDATED_AT = null;

    protected $table = 'tblPOHeader';
    // protected $primaryKey = 'PONumber';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'PONumber',
        'OrderNumber',
        'PODate',
        'SupplierCode',
        'SupplierName',
        'productType',
        'orderPlacer',
        'FOB',
        'orderPlacerEmail',
        'deliveryAddress',
        'contactNumber',
        'contactPerson',
        'deliveryMethod',
        'totalNetVol',
        'volumeUOM',
        'totalNetWeight',
        'totalGrossWeight',
        'weightUOM',
        'subTotal',
        'totalDiscount',
        'totalTax',
        'totalCost',
        'usedCurrency',
        'SpecialInstruction',
        'EncoderID',
        'FileName',
        'TermsCode',
        'ConfirmedBy',
        'EditedBy',
        'DateUpdated'
    ];

    protected static function boot()
    {
        parent::boot();
        PO::observe(POObserver::class);
    }

    /**
     * Get the log name for activity logging.
     */
    protected function getLogName(): string
    {
        return 'purchase_order';
    }

    /**
     * Get the attributes to log for activity.
     */
    protected function getLogAttributes(): array
    {
        return [
            'PONumber',
            'PODate',
            'SupplierCode',
            'SupplierName',
            'orderPlacer',
            'totalCost',
            'POStatus',
            'ConfirmedBy',
            'EditedBy'
        ];
    }

    /**
     * Get the log description for different events.
     */
    protected function getLogDescription(string $eventName): string
    {
        $descriptions = [
            'created' => "Created Purchase Order #{$this->PONumber}",
            'updated' => "Updated Purchase Order #{$this->PONumber}",
            'deleted' => "Deleted Purchase Order #{$this->PONumber}",
            'confirmed' => "Confirmed Purchase Order #{$this->PONumber}",
        ];

        return $descriptions[$eventName] ?? "{$eventName} Purchase Order #{$this->PONumber}";
    }

    public function POItems()
    {
        return $this->hasMany(POItems::class, 'PONumber', 'PONumber');
    }

    // Method to update totalCost
    public function updateTotalCost()
    {
        $subTotal = $this->POItems()->sum('TotalPrice');
        $this->subTotal = $subTotal;
        
        // comment as of now because there is no discount on po items
        // $totalDiscount = $this->POItems()->sum('TotalDiscount');
        $this->totalCost = $subTotal;
        
        // Save without triggering events to prevent activity logging for internal calculations
        $this->saveQuietly();
    }

    public function posupplier()
    {
        return $this->hasOne(Supplier::class, 'SupplierCode', 'SupplierCode');
    }

    public function receivingHeader()
    {
        return $this->belongsTo(ReceivingRHeader::class, 'RRNo', 'RRNo');
    }
}
