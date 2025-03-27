<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class shell_rr extends Model
{

    const CREATED_AT = 'DateUploaded';
    protected $table = 'TBLSHELL_RR';

    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'SupplierCode',
        'SupplierName',
        'SupplierTIN',
        'RRNo',
        'Date',
        'Reference',
        'Status',
        'Total',
        'ApprovedBy',
        'CheckedBy',
        'UpdatedBy',
        'PreparedBy',
        'DateUpdated',
        'PrintedBy',
        'Address',
        'FileName'
    ]; 

    public function rrItems()
    {
        return shell_rr_items::where('RRNo', $this->RRNo)->get();
    }

    public function deleteItems()
    {
        return shell_rr_items::where('RRNo', $this->RRNo)->delete();
    }
}
