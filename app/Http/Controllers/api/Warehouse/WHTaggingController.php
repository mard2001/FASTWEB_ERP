<?php

namespace App\Http\Controllers\api\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Warehouse\WHTagging;
use Illuminate\Http\Request;

class WHTaggingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = WHTagging::get();

            if (count($data) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Warehouse details found',
                    'data' => []
                ], 404);   
            }

            return response()->json([
                'success' => true,
                'message' => 'All warehouse details retrieved successfully',
                'data' => $data
            ], 200);   

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);   
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        try {
            $data = $request->data;
            $res = WHTagging::create($data);

            return response()->json( [
                'message' => 'Warehouse inserted succesfully!',
                'success' => true,
            ]);

           
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $data = $request->data;
            WHTagging::create($data);
            
            return response()->json([
                'success' => true,
                'message' => 'New warehouse created successfully',
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $Warehouse = WHTagging::where('Warehouse', $id)->first();

            if (is_null($Warehouse)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Warehouse details found',
                    'data' => []
                ], 404);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Warehouse details retrieved successfully',
                'data' => $Warehouse
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $Warehouse)
    {
        try {
            $data = $request['data'];
            $found = WHTagging::where('Warehouse', $Warehouse)->first();

            if (!$found) {
                return response()->json([
                    'message' => 'data not found',
                    'success' => false
                ]);
            }

            $found->update($data);
            $response = [
                'message' => 'Warehouse details updated succesfully!',
                'success' => true,
                "data"=> $found
            ];

        } catch (\Exception $e) {
            $response = [
                'message' => $e->getMessage(),
                'success' => false
            ];
        }

        return response()->json($response);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            
            $data = WHTagging::where('Warehouse', $id)->delete();

            if (!$data) {
                $response = [
                    'message' => 'Warehouse data not found',
                    'success' => false
                ];

                //break to reserve server resouces
                return response()->json($response);
            }
            $response = [
                'message' => 'Warehouse deleted succesfully!',
                'success' => true
            ];
        } catch (\Exception $e) {

            $response = [
                'message' => $e->getMessage(),
                'success' => 0
            ];
        }

        return response()->json($response);
    }

    public function getAllWarehouse(){
        try {
            $data = WHTagging::select('Warehouse')->get();

            if (count($data) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Warehouse found',
                    'data' => []
                ], 404);   
            }

            return response()->json([
                'success' => true,
                'message' => 'All warehouse retrieved successfully',
                'data' => $data
            ], 200);   

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], 500);   
        }
    }
}
