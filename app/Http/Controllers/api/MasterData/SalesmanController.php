<?php

namespace App\Http\Controllers\api\MasterData;

use Illuminate\Http\Request;
use App\Models\salesman_model;
use App\Http\Controllers\helpers\DynamicSQLHelper;

class SalesmanController extends DynamicSQLHelper
{
    public function index(Request $request)
    {

        try {

            $data = salesman_model::on($request->connectionName)->orderBy('id')->get();

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

            // Prepare data for insertion
            $filePath = null;

            $responseMessage = [
                'response' => 'Items inserted succesfully!',
                'status_response' => 1
            ];

            $datas = $this->convertFormData($request);

            // Handle file upload
            if ($request->hasFile('image_file')) {
                // Store the file
                $filePath = $request->file('image_file')->store('images', 'public');
                $datas['uploaded_image'] = $filePath; // Store the file path in the data array
            }

            salesman_model::on($request->connectionName)->insert($datas);
        } catch (\Exception $e) {
            $responseMessage = [
                'response' => $e->getMessage(),
                'status_response' => 0
            ];
        } finally {
            return response()->json($responseMessage);
        }
    }

    public function storebulk(Request $request)
    {
        $responseMessage = array();

        try {

            // Prepare data for insertion
            //convert formData into json
            $bulkdata = $request['data'];

            // $bulkdata = json_decode($request['data'], true);
            // dd($request['data']);

            $successfulInserts = 0;

            $responseMessage = [
                'response' => 'Items inserted succesfully!',
                'status_response' => 1,
                'total_inserted' => count($bulkdata),
                'tatal_entry' => count($bulkdata)
            ];

            foreach ($bulkdata as $perRow) {

                try {
                    // Attempt to insert the row
                    salesman_model::on($request->connectionName)->insert($perRow);
                    $successfulInserts++;
                } catch (\Exception $e) {
                    $responseMessage = [
                        'response' => $e->getMessage(),
                        'status_response' => 0,
                        'total_inserted' => 0,
                        'tatal_entry' => count($bulkdata)
                    ];
                }
            }
        } catch (\Exception $e) {
            $responseMessage = [
                'response' => $e->getMessage(),
                'status_response' => 0,
                'total_inserted' => 0,
                'tatal_entry' => count($bulkdata)
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
            

            $found = salesman_model::on($request->connectionName)->find($id);

            if (!$found) {
                $response = [
                    'response' => 'data not found',
                    'status_response' => 0
                ];

                //break to reserve server resouces
                return response()->json($response);
            }

            $data = $this->convertFormData($request);

            // Handle file upload
            if ($request->hasFile('image_file')) {
                // Store the file
                $filePath = $request->file('image_file')->store('images', 'public');

                $data['uploaded_image'] = $filePath; // Store the file path in the data array
            }
            //return response()->json($data);

            $found->update($data);

            $response = [
                'response' => 'Items updated succesfully!',
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

            $data = salesman_model::on($request->connectionName)->where('id', $id)->delete();

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
