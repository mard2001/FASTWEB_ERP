<?php

namespace App\Models\Salesman;

use App\Observers\SalespersonObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Salesperson extends Model
{
    use HasFactory;

    protected $table = "tblSalesperson";

    protected $primaryKey = 'Salesperson'; 
    public $incrementing = false; 
    protected $keyType = 'string'; 

    public $timestamps = false;

    protected $fillable = [
        "EmployeeID",
        "Branch",
        "Type",
        "Salesperson",
        "Name",
        "Warehouse",
        "SourceWarehouse",
        "ContactNo",
        "ContactHP",
        "ContacteMail",
        "Addr1",
        "Addr2",
        "Addr3",
        "Group1",
        "Group2",
        "Group3",
        "mdCode",
        "lastUpdated",
    ];

    // protected static function boot()
    // {
    //     parent::boot();
    //     Salesperson::observe(SalespersonObserver::class);

    // }

}
