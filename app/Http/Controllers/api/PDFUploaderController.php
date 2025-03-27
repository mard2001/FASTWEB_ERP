<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Http\Controllers\Helpers\ShellInvoicePdfParser;
use App\Http\Controllers\Helpers\ShellPOPdfParser;
use App\Http\Controllers\Helpers\ShellRRPdfParser;

use App\Http\Controllers\api\shell\RRController;
use App\Http\Controllers\api\shell\InvoiceController;
use App\Http\Controllers\api\shell\POController;


use App\Http\Controllers\Helpers\DynamicSQLHelper;



class PDFUploaderController extends DynamicSQLHelper
{
    protected $RRController;
    protected $InvoiceController;
    protected $POController;    

    public function __construct(RRController $RRController, InvoiceController $InvoiceController, POController $POController)
    {
        $this->RRController = $RRController;
        $this->InvoiceController = $InvoiceController;
        $this->POController = $POController;        
    }

    public function store(Request $request)
    {
        $explode = explode("\n", $request->extractedString);
        $identifier = strtolower($explode[0]);

        if (Str::startsWith($identifier, 'receiving')) {
            //validate receving report extracted data
            $data = ShellRRPdfParser::FUIRRHeader($request);
            $request->merge(['data' =>  $data]);
            return $this->RRController->insertWholeData($request);

        } else if (Str::startsWith($identifier, 'order')) {
            //validate purchase order extracted data
            $data = ShellPOPdfParser::POHeader($request);
            $request->merge(['data' =>  $data]);
            return $this->POController->insertWholeData($request);

        } else {
            //validate invoice extracted data
            $data = ShellInvoicePdfParser::invoiceHeader($request);
            $request->merge(['data' =>  $data]);
            return $this->InvoiceController->insertWholeData($request);

        }
    }
}
