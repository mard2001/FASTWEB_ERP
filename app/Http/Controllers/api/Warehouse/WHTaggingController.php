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
            
            // Log the activity
            activity('warehouse')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'warehouse_code' => $data['Warehouse'],
                    'warehouse_type' => $data['WHType'],
                    'group_code' => $data['WHGroupCode'],
                    'group_desc' => $data['WHGroupDesc'],
                    'municipality' => $data['Municipality'],
                    'status' => $data['Status'],
                    'subject_type' => 'App\\Models\\Warehouse\\WHTagging',
                    'subject_id' => $data['Warehouse'],
                    'event' => 'created',
                    'attributes' => $data
                ])
                ->log("Created new warehouse '{$data['Warehouse']}' - {$data['WHGroupDesc']}");
            
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

            // Store old data for logging
            $oldData = $found->toArray();

            $found->update($data);

            // Log the activity
            activity('warehouse')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'warehouse_code' => $Warehouse,
                    'warehouse_type' => $data['WHType'],
                    'group_desc' => $data['WHGroupDesc'],
                    'subject_type' => 'App\\Models\\Warehouse\\WHTagging',
                    'subject_id' => $Warehouse,
                    'event' => 'updated',
                    'attributes' => $data,
                    'old' => $oldData
                ])
                ->log("Updated warehouse '{$Warehouse}' - {$data['WHGroupDesc']}");

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
            // Get warehouse data before deletion for logging
            $warehouse = WHTagging::where('Warehouse', $id)->first();
            
            if (!$warehouse) {
                $response = [
                    'message' => 'Warehouse data not found',
                    'success' => false
                ];

                //break to reserve server resouces
                return response()->json($response);
            }

            // Store warehouse data for logging
            $warehouseData = $warehouse->toArray();
            
            $data = WHTagging::where('Warehouse', $id)->delete();

            // Log the activity
            activity('warehouse')
                ->withProperties([
                    'ip' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                    'url' => request()->fullUrl(),
                    'method' => request()->method(),
                    'warehouse_code' => $id,
                    'warehouse_type' => $warehouseData['WHType'],
                    'group_desc' => $warehouseData['WHGroupDesc'],
                    'subject_type' => 'App\\Models\\Warehouse\\WHTagging',
                    'subject_id' => $id,
                    'event' => 'deleted',
                    'old' => $warehouseData
                ])
                ->log("Deleted warehouse '{$id}' - {$warehouseData['WHGroupDesc']}");

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
