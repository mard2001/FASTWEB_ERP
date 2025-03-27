<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CSLog extends Model
{
    use HasFactory;

    protected $table = 'TBLINVCOUNT_LOGS';

    public $timestamps = false;
    
    protected $fillable = [
        'PROCESSID',
        'PROCESSEDBY',
        'ACTION',
        'DATECREATED',
        'STATUS',
    ];
}
