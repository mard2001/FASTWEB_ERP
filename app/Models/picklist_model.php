<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class picklist_model extends Model
{
    use HasFactory;

    protected $table = 'invoice_table';
    public $timestamps = false;
    protected $fillable = [
        'custCode',
        'custName',
        'invoiceNumber',
        'invoiceAmount',
        'invoiceDate',
        'driver',
        'vehicle',
        'dateDelivered',
        'address',
        'status'
    ];
}
