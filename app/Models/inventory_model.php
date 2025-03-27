<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inventory_model extends Model
{
    use HasFactory;

    protected $table = 'inventory_table'; 

    //const UPDATED_AT = 'lastupdated';


    public $timestamps = false;

    protected $fillable = [
        'id',
        'inventoryID',
        'mdCode',
        'stockCode',
        'lastupdated',
        'invstat',
        'syncstat',
        'dates_tamp',
        'time_stamp',
        'quantity'
    ];

}
