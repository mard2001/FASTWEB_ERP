<?php

namespace App\Http\Controllers\api\MasterData;

use Illuminate\Http\Request;
use App\Models\customer_model;

class CustomerController
{
    public function index(Request $request)
    {
        try {
            
            $data = customer_model::on($request->connectionName)->orderBy('id')->get();

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

    public function getCustomersNames(Request $request)
    {
        try {
            
            $data = customer_model::on($request->connectionName)->select(['id', 'custname', 'address'])->orderBy('id')->get();

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
    public function store(Request $request)
    {
        $responseMessage = array();
        // return $request['data'];
        try {

            // Prepare data for insertion
            //convert formData into json
            
            
            $data = $request['data'];
            $successfulInserts = 0;

            $responseMessage = [
                'response' => 'Items inserted succesfully!',
                'status_response' => 1,
                'total_inserted' => count($data),
                'tatal_entry' => count($data)
            ];

            $errorMsg = '';

            foreach ($data as $perRow) {

                try {
                    customer_model::on($request->connectionName)->insert($perRow);

                    $successfulInserts++;
                } catch (\Exception $e) {
                    $errorMsg += '\n' + $e->getMessage();
                    $responseMessage = [
                        'response' => $errorMsg,
                        'status_response' => 0,
                        'total_inserted' => 0,
                        'tatal_entry' => count($data)
                    ];
                }
            }
        } catch (\Exception $e) {
            $responseMessage = [
                'response' => $e->getMessage(),
                'status_response' => 0,
                'total_inserted' => 0,
                'tatal_entry' => 0
            ];
        }

        return response()->json($responseMessage);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $response = array();
        try {
            
            $data = $request['data'];
            $found = customer_model::on($request->connectionName)->find($id);

            if (!$found) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];

                //break to reserve server resouces
                return response()->json($response);
            }

            //return response()->json($data);

            $found->update($data);

            $response = [
                'response' => 'Items updated succesfully!',
                'status_response' => 1,
            ];
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
            
            $data = customer_model::on($request->connectionName)->where('id', $id)->delete();

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

}
