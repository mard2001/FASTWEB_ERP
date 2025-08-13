<?php

namespace App\Http\Controllers\api\Salesman;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Salesman\Salesperson;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class SalespersonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = [];
            $data = Salesperson::get();

            if (count($data) == 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'No Salesman Data found',
                    'data' => $data
                ], 200);  // HTTP 404 Not Found
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Salesman Data retrieved successfully',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function create()
    // {
    //     //
    // }

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
            $salesperson = Salesperson::create($data);
            
            // Create activity log entry directly
            ActivityLog::create([
                'log_name' => 'salesman',
                'description' => "created new salesman: {$salesperson->Name} (Code: {$salesperson->Salesperson})",
                'subject_type' => 'App\\Models\\Salesman\\Salesperson',
                'subject_id' => null, // Keep as null to avoid bigint conversion error
                'event' => 'created',
                'causer_type' => 'App\\Models\\User',
                'causer_id' => Auth::id(),
                'properties' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->url(),
                    'method' => $request->method(),
                    'module' => 'Salesman Management',
                    'action_type' => 'create',
                    'salesman_data' => $data,
                    'employee_id' => $salesperson->EmployeeID,
                    'salesperson_code' => $salesperson->Salesperson
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'New Salesman created successfully',
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function storeBulk(Request $request)
    {   
        $allSalesperson = $request->json()->all();
        $inserted = 0;
        $notInserted = 0;
        $duplicatesInRequest = 0;
        $batchSize = 100; 

        try {
            // Remove duplicates within the request payload itself
            $uniqueSalespersons = collect($allSalesperson)
                ->unique('Salesperson')
                ->values()
                ->toArray();

            $duplicatesInRequest = count($allSalesperson) - count($uniqueSalespersons);

            foreach (array_chunk($uniqueSalespersons, $batchSize) as $batch) {
                $batch = array_map(function ($item) {
                    return array_map('trim', $item);
                }, $batch);

                $salespersonKeys = collect($batch)->pluck('Salesperson')->toArray();

                $existingSalespersons = Salesperson::whereIn('Salesperson', $salespersonKeys)
                    ->pluck('Salesperson')
                    ->toArray();

                $newSalespersons = collect($batch)->reject(function ($item) use ($existingSalespersons) {
                    return in_array($item['Salesperson'], $existingSalespersons);
                })->map(function ($item) {
                    return collect($item)->except(['EmployeeID'])->toArray();
                })->values()->toArray();

                if (!empty($newSalespersons)) {
                    Salesperson::insert($newSalespersons);
                    $inserted += count($newSalespersons);
                }

                $notInserted += count($batch) - count($newSalespersons);
            }

            $retval = ($notInserted > 0) ? 2 : 1;

            return response()->json([
                'success' => true,
                'status_response' => $retval,
                'message' => 'Salespersons processed successfully',
                'successful' => $inserted,
                'unsuccessful' => $notInserted,
                'duplicates_in_request' => $duplicatesInRequest,
                'totalFileLength' => count($allSalesperson)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status_response' => 0,
                'message' => $e->getMessage(),
            ], 500);
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
            //code...
            $data = Salesperson::where('EmployeeID',$id)->first();

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Salesman not found',
                ], 404);
            }
            return response()->json([
                'message' => 'Salesman Details retrieved successfully',
                'data' => $data,
                'success' => true,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function edit($id)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request['data'];
            $found = Salesperson::where('EmployeeID', $id)->first();

            if (!$found) {
                $response = [
                    'response' => 'data not found',
                    'success' => false
                ];

                //break to reserve server resouces
                return response()->json($response);
            }
            
            // Store original data for activity log
            $originalData = $found->toArray();
            
            // Update the salesperson
            $found->update($data);
            
            // Create activity log entry directly
            ActivityLog::create([
                'log_name' => 'salesman',
                'description' => "updated salesman: {$found->Name} (Code: {$found->Salesperson})",
                'subject_type' => 'App\\Models\\Salesman\\Salesperson',
                'subject_id' => null, // Keep as null to avoid bigint conversion error
                'event' => 'updated',
                'causer_type' => 'App\\Models\\User',
                'causer_id' => Auth::id(),
                'properties' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->url(),
                    'method' => $request->method(),
                    'module' => 'Salesman Management',
                    'action_type' => 'update',
                    'attributes' => $data,
                    'old' => $originalData,
                    'employee_id' => $found->EmployeeID,
                    'salesperson_code' => $found->Salesperson
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' =>  "Salesman updated successfully!",
                "data"=> $found
            ]);

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
    public function destroy(Request $request, $id)
    {
        try {
            
            $data = Salesperson::where('EmployeeID', $id)->first();

            if (!$data) {
                $response = [
                    'message' => 'Salesman not found',
                    'success' => false
                ];

                return response()->json($response);
            }

            // Store salesperson data before deletion for activity log
            $salesmanName = $data->Name;
            $salesmanCode = $data->Salesperson;
            $salesmanData = $data->toArray();
            $employeeID = $data->EmployeeID;
            
            // Create activity log entry directly
            ActivityLog::create([
                'log_name' => 'salesman',
                'description' => "deleted salesman: {$salesmanName} (Code: {$salesmanCode})",
                'subject_type' => 'App\\Models\\Salesman\\Salesperson',
                'subject_id' => null, // Keep as null to avoid bigint conversion error
                'event' => 'deleted',
                'causer_type' => 'App\\Models\\User',
                'causer_id' => Auth::id(),
                'properties' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url' => $request->url(),
                    'method' => $request->method(),
                    'module' => 'Salesman Management',
                    'action_type' => 'delete',
                    'deleted_data' => $salesmanData,
                    'employee_id' => $employeeID,
                    'salesperson_code' => $salesmanCode
                ],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $data->delete();

            $response = [
                'message' => 'Salesman deleted successfully!',
                'success' => true,
            ];
        } catch (\Exception $e) {
            $response = [
                'message' => $e->getMessage(),
                'success' => 0
            ];
        }

        return response()->json($response);
    }

    public function getActiveSalesperson()
    {
        try {
            $data = [];
            $data = Salesperson::select('EmployeeID', 'Salesperson', 'Name', 'mdCode')->get();

            if (count($data) == 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'No Salesman Data found',
                    'data' => $data
                ], 200);  // HTTP 404 Not Found
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Salesman Data retrieved successfully',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
