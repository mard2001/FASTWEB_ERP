<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class shell_invoice extends Model
{
    const CREATED_AT = 'DateUploaded';
    protected $table = 'TBLSHELL_INVOICE';

    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'SoldTo',
        'totalAmount',
        'totalVat',
        'amountDue',
        'invoiceNumber',
        'accountNumber',
        'dueDate',
        'invoiceDate',
        'paymentTerms',
        'paymentMethod',
        'deliveryMethod',
        'yourTIN',
        'paymentCollectedBy',
        'bankDetails',
        'shippedTo',
        'plant',
        'PONumber',
        'FileName'
    ];

    public function invoicesItems()
    {
        return shell_invoice_items::where('invoiceNumber', $this->invoiceNumber)->get();
    }

    public function deleteInvoicesItems()
    {
        return shell_invoice_items::where('invoiceNumber', $this->invoiceNumber)->delete();
    }
}
