<?php

namespace App\Http\Controllers\api\MasterData;

use Illuminate\Http\Request;
use App\Models\picklist_model;

class PickListController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $data = picklist_model::on($request->connectionName)->orderBy('id')->get();

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
        try {

            $data = $request['data'];

            if (!count($data) > 0) {
                $responseMessage = [
                    'response' => 'No data in csv',
                    'status_response' => 2,
                    'total_inserted' => 0,
                    'tatal_entry' => 0
                ];
                return response()->json($responseMessage);
            }

            $successfulInserts = 0;
            //inventory_model::insert($data);

            foreach ($data as $perRow) {

                try {
                    // Attempt to insert the row
                    picklist_model::on($request->connectionName)->insert($perRow);
                    $successfulInserts++;
                } catch (\Exception $e) {
                    return response()->json([
                        'response' =>  $e->getMessage(),
                        'status_response' => 2,
                        'total_inserted' => $successfulInserts,
                        'tatal_entry' => count($data)
                    ]);
                }
            }

            if (count($data) != $successfulInserts) {
                $responseMessage = [
                    'response' =>  $successfulInserts . ' out of ' . count($data) . ' records inserted successfully.',
                    'status_response' => 2,
                    'total_inserted' => $successfulInserts,
                    'tatal_entry' => count($data)
                ];
            } else {
                $responseMessage = [
                    'response' => 'Items inserted succesfully!',
                    'status_response' => 1,
                    'total_inserted' => $successfulInserts,
                    'tatal_entry' => count($data)
                ];
            }
        } catch (\Exception $e) {
            $responseMessage = [
                'response' => $e->getMessage(),
                'status_response' => 0,
                'total_inserted' => 0,
                'tatal_entry' => count($data)
            ];
        }

        return response()->json($responseMessage);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $response = array();
        try {

            $data = $request['data'];
            $data = picklist_model::on($request->connectionName)->find($id);

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
    public function update(Request $request, string $id)
    {
        $response = [
            'response' => 'Items updated succesfully!',
            'status_response' => 1
        ];

        try {


            $data = $request['data'];
            // dd($data);

            $found = picklist_model::on($request->connectionName)->find($id);

            if (!$found) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];

                //break to reserve server resouces
                //return response()->json($response);
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
        // return $request;
        $response = [
            'response' => 'Item deleted succesfully!',
            'status_response' => 1
        ];
        try {

            $data = picklist_model::on($request->connectionName)->where('id', $id)->delete();

            if (!$data) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];

                //break to reserve server resouces
                return response()->json($response);
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
