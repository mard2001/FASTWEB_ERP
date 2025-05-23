<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvAdjustmentLogs extends Model
{
    use HasFactory;

    protected $table = 'InvAdjustmentLogs';

    public $timestamps = false;

    protected $fillable = [
        'REFERENCE',
        'STOCKCODE',
        'WAREHOUSE',
        'ENTRY_DATE',
        'PREV_QTY',
        'NEW_QTY',
        'ADJUSTED_QTY',
        'ADJUSTMENT_TYPE',
        'REASON',
        'HANDLED_BY'
    ]; 
}
