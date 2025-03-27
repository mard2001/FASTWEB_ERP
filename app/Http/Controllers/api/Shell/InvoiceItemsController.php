<?php

namespace App\Http\Controllers\api\shell;

use Illuminate\Http\Request;

use App\Models\shell_invoice_items;
use App\http\Requests\Shell\StoreInvoicesItemsRequest;

class InvoiceItemsController
{
    public function store(StoreInvoicesItemsRequest $request)
    {
        // return $request;
        try {
            
            shell_invoice_items::on($request->connectionName)->insert($request->data);

            return response()->json([
                'response' => 'Items inserted succesfully!',
                'status_response' => 1,
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

            $data = shell_invoice_items::on($request->connectionName)->orderBy('id')->get();

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

            $data = shell_invoice_items::on($request->connectionName)->find($id);

            if (!$data) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];
            } else {
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

    public function searchByInvoiceNumber(Request $request, string $invoice)
    {
        $response = array();
        try {

            $data = shell_invoice_items::on($request->connectionName)->where('invoiceNumber', $invoice)->get();

            if (!$data) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];
            } else {
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
            
            $data = shell_invoice_items::on($request->connectionName)->where('id', $id)->delete();

            if (!$data) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];

                //break to reserve server resouces
                return response()->json($response);
            }
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

    public function update(StoreInvoicesItemsRequest $request, string $id)
    {
        $response = [
            'response' => 'Items updated succesfully!',
            'status_response' => 1
        ];

        try {
            $data = $request['data'];
            $found = shell_invoice_items::on($request->connectionName)->find($id);

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
