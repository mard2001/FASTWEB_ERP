<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WHTagging extends Model
{
    use HasFactory;

    protected $table = 'InvWarehouseTypeTagging';
    protected $primaryKey = 'Warehouse';
    public $incrementing = false;

    const UPDATED_AT = 'DateUpdated';
    const CREATED_AT = 'DateUpdated';

    protected $fillable = [
        'Warehouse',
        'WHType',
        'WHGroupCode',
        'WHGroupDesc',
        'Municipality',
        'Status',
    ];
}
