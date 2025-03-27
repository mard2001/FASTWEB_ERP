<?php

namespace App\Models;

use App\Models\Orders\PO;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    const CREATED_AT = 'lastUpdated';
    protected $table = 'tblSupplier';

    public $timestamps = true;
    const UPDATED_AT = null;

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
    ];
    
    public function purchaseOrders()
    {
        return $this->hasMany(PO::class, 'SupplierCode', 'SupplierCode');
    }
}
