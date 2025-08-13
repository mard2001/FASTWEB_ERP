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

    /**
     * Get the identifier for activity logging
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        $salesmanName = $this->Name ?? $this->Salesperson ?? 'Unknown';
        $salesmanCode = $this->Salesperson ?? 'N/A';
        
        return match($eventName) {
            'created' => "created new salesman: {$salesmanName} (Code: {$salesmanCode})",
            'updated' => "updated salesman: {$salesmanName} (Code: {$salesmanCode})",
            'deleted' => "deleted salesman: {$salesmanName} (Code: {$salesmanCode})",
            default => "{$eventName} salesman: {$salesmanName} (Code: {$salesmanCode})"
        };
    }

    // protected static function boot()
    // {
    //     parent::boot();
    //     Salesperson::observe(SalespersonObserver::class);

    // }

}
