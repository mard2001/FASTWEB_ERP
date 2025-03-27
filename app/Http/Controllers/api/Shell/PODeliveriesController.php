<?php

namespace App\Http\Controllers\api\shell;

use App\Http\Requests\Shell\StorePODeliveriesRequest;
use Illuminate\Http\Request;
use App\models\shell_po_deliveries;

class PODeliveriesController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $data = shell_po_deliveries::on($request->connectionName)->orderBy('id')->get();

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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePODeliveriesRequest $request)
    {
        try {

            shell_po_deliveries::on($request->connectionName)->insert($request->data);
            return response()->json([
                'response' => 'Items inserted succesfully!',
                'status_response' => 1
            ]);
        } catch (\Exception  $e) {

            return response()->json([
                'response' => $e->getMessage(),
                'status_response' => 0
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $response = array();
        try {

            $data = shell_po_deliveries::on($request->connectionName)->find($id);

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

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePODeliveriesRequest $request, string $id)
    {
        $response = [
            'response' => 'Items updated succesfully!',
            'status_response' => 1
        ];

        try {
            $data = $request['data'];
            $found = shell_po_deliveries::on($request->connectionName)->find($id);

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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $response = array();
        try {
            
            $data = shell_po_deliveries::on($request->connectionName)->where('id', $id)->delete();

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

    public function getDeliveries(Request $request, string $po)
    {
        $response = array();
        try {

            $data = shell_po_deliveries::on($request->connectionName)->where('PONumber', $po)->get();

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
}
