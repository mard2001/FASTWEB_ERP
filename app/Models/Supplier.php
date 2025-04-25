<?php

namespace App\Models;

use App\Models\Orders\PO;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'tblSupplier';
    protected $primaryKey = 'SupplierCode';
    public $incrementing = false;
    public $timestamps = false;
    const UPDATED_AT = null;
    const CREATED_AT = 'lastUpdated';

    protected $fillable = [
        'SupplierCode' ,
        'SupplierName' ,
        'SupplierType' ,
        'TermsCode' ,
        'ContactPerson' ,
        'ContactNo' ,
        'CompleteAddress' ,
        'Region' ,
        'Province' ,
        'City' ,
        'Municipality' ,
        'Barangay' ,
        'PriceCode',
        'holdStatus'
    ];
    
    protected $attributes = [
        'Barangay' => ' ',
    ];
    public function purchaseOrders()
    {
        return $this->hasMany(PO::class, 'SupplierCode', 'SupplierCode');
    }
}
