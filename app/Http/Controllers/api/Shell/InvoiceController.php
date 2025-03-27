<?php

namespace App\Http\Controllers\api\shell;

use Illuminate\Http\Request;
use App\Models\shell_invoice;
use App\Http\Requests\Shell\StoreInvoicesItemsRequest;
use App\Http\Requests\Shell\StoreInvoicesHeaderRequest;

use App\http\Controllers\api\shell\InvoiceItemsController;

class InvoiceController
{
    protected $InvoiceItemsController;

    public function __construct(InvoiceItemsController $InvoiceItemsController)
    {
        $this->InvoiceItemsController = $InvoiceItemsController;
    }

    public function store(Request $request)
    {
        return $this->insertWholeData($request);
    }

    public function insertWholeData(Request $request)
    {
		
        try {
            $request->validate([
                'pdf_file' => 'file|mimes:pdf|max:10240', // Validate each file
            ]);

            $requestDataHeader = $request->data;
            $requestDataItems = $request->data['Items'];
            // return  $requestDataHeader;

            if ($request->hasFile('pdf_file')) {
                $pdfFile = $request->file('pdf_file');
                $fileName = $pdfFile->getClientOriginalName();
                $requestDataHeader['FileName'] = $fileName;
            }


            $validateHeader = StoreInvoicesHeaderRequest::validate($requestDataHeader);
            $validateItems = StoreInvoicesItemsRequest::validate($requestDataItems);

            if ($validateHeader->fails() || $validateItems->fails()) {

                $errors = array_merge(
                    $validateHeader->errors()->toArray(),
                    $validateItems->errors()->toArray()
                );

                // Return detailed error messages
                return response()->json([
                    'message' => 'Validation errors occurred',
                    'errors' => $errors, // Returns the full array of errors
                ], 422);
            }

            $items = new StoreInvoicesItemsRequest();
            $items->merge(['data' =>  $requestDataItems]);
            $items->merge(['dynamicConnection' => $request->connectionName]);

            unset($requestDataHeader['Items']);

            $headerInsertResult = shell_invoice::on($request->connectionName)->create($requestDataHeader);

            if ($headerInsertResult) {

                $itemInsertResult = $this->InvoiceItemsController->store($items);
                $itemInsertResult =  $itemInsertResult->getData(true);

                if ($itemInsertResult['status_response'] == '0') {
                    $headerInsertResult->delete();
                    return response()->json($itemInsertResult);
                }
            }

            return response()->json([
                'response' => 'PDF data inserted succesfully!',
                'status_response' => 1
            ]);
        } catch (\Exception  $e) {

            return response()->json([
                'response' => $e->getMessage(),
                'status_response' => 0
            ]);
        }
    }


    public function index(Request $request)
    {
        try {

            $data = shell_invoice::on($request->connectionName)->orderBy('id')->get();

            if (!$data) {
                return response()->json([
                    'response' => 'data not found',
                    'status_response' => 0
                ]);
            } else {
                return response()->json([
                    'response' => $data,
                    'status_response' => 1
                ]);
            }
        } catch (\Exception $e) {

            return response()->json([
                'response' => $e->getMessage(),
                'status_response' => 0
            ]);
        }
    }

    public function show(Request $request, string $id)
    {

        $response = array();
        try {

            $data = shell_invoice::on($request->connectionName)->find($id);

            if (!$data) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];
            } else {
                $data['Items'] = $data->invoicesItems();

                $response = [
                    'response' => $data,
                    'status_response' => 1
                ];
            }
        } catch (\Exception $e) {

            $response = [
                'response' => $e->getMessage(),
                'status_response' => 0
            ];
        }

        return response()->json($response);
    }

    public function destroy(Request $request, string $id)
    {
        $response = array();
        try {
            $data = shell_invoice::on($request->connectionName)->find($id);


            if (!$data) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];

                //break to reserve server resouces
                return response()->json($response);
            }

            $data->deleteInvoicesItems();
            $data->delete();


            $response = [
                'response' => 'Item deleted succesfully!',
                'status_response' => 1
            ];
        } catch (\Exception $e) {

            $response = [
                'response' => $e->getMessage(),
                'status_response' => 0
            ];
        }

        return response()->json($response);
    }

    public function update(StoreInvoicesHeaderRequest $request, string $id)
    {
        $response = [
            'response' => 'Items updated succesfully!',
            'status_response' => 1
        ];

        try {
            $data = $request['data']; 

            $found = shell_invoice::on($request->connectionName)->find($id);

            if (!$found) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];
            }

            $found->update($data);
        } catch (\Exception $e) {

            $response = [
                'response' => $e->getMessage(),
                'status_response' => 0
            ];
        }

        return response()->json($response);
    }
}
