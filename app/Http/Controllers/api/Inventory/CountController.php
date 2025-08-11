<?php

namespace App\Http\Controllers\api\Inventory;

use App\Models\Product;
use App\Models\Inventory\CSHeader;
use App\Models\Inventory\CSDetails;
use Illuminate\Http\Request;
use App\Services\ProductCalculator;
use App\Http\Controllers\Controller;
use App\Models\Inventory\CSLog;
use Illuminate\Support\Facades\Cache;
class CountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            
            $data = CSHeader::orderBy('DATECREATED','desc')->with('user')->whereIn('STATUS', [1, 2])->get();
            
            if (count($data) == 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'No Count Sheet Report Data found',
                ], 200);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Count Sheet Reports retrieved successfully',
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show2($id)
    {
        try {
            $productCalculator = new ProductCalculator();
            // $convertedUnits = $productCalculator->calculateDynamicCasePackUnit('12599975', 274);
            // return response()->json(['converted_units' => $convertedUnits]);

            $data = CSHeader::with(['details','user','details.proddetails' => function ($productQuery) {
                $productQuery->select('StockCode', 'Description', 'StockUom', 'AlternateUom', 'OtherUom', 'ConvFactAltUom')
                ->whereIn('StockCode', function ($subQuery) {
                    $subQuery->selectRaw("CAST(StockCode AS VARCHAR) FROM TBLINVCOUNT_DETAILS");
                });
            }])->where('CNTHEADER_ID',$id)->firstOrFail();

            // // Map over details and call service for each row
            $data->details = $data->details->map(function ($detail) use ($productCalculator) {
                $calculation = $productCalculator->originalDynamicConv((string)$detail->STOCKCODE, (int)$detail->MNLCOUNT);
                
                if($calculation['success']){
                    $detail->calculated_units = $calculation['result']; // Assuming service returns ['result' => value]
                }
                
                return $detail;
            });



            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inventory Count not found',
                ], 404);
            }else{
                return response()->json([
                    'message' => 'Inventory Count retrieved successfully',
                    'data' => $data,
                    'success' => true,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function show($id)
    {
        try {
            $productCalculator = new ProductCalculator();
            $data = CSHeader::with(['details', 'user'])->where('CNTHEADER_ID', $id)->firstOrFail();
            
            $productStockCodes = $data->details->pluck('STOCKCODE')->unique()->toArray();
            $products = Product::whereIn('StockCode', $productStockCodes)->get()->keyBy('StockCode'); 
            
            $data->details = $data->details->map(function ($detail) use ($productCalculator, $products) {
                $sku = (string)$detail->STOCKCODE;
                $quantity = (int)$detail->MNLCOUNT;
        
                if (isset($products[$sku])) {
                    $product = $products[$sku];
                    $calculation = $productCalculator->originalDynamicConvOptimized($product, $quantity);
                    
                    if ($calculation['success']) {
                        $detail->calculated_units = $calculation['result'];
                        $detail->uom = $calculation['uom'];
                        $detail->altUOM = $calculation['altUOM'];
                        $detail->othUOM = $calculation['othUOM'];
                    }
                    
                    // Add product details to the detail object
                    $detail->proddetails = $product;
                }
        
                return $detail;
            });
        
            return response()->json([
                'message' => 'Inventory Count retrieved successfully',
                'data' => $data,
                'success' => true,
            ]);
        
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
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
    public function update(Request $request, $cntHeaderId)
    {
        try {
            $updates = $request->input('data.SKUList'); // Assuming 'data' contains an array of objects
            if(count($updates) == 0){
                return response()->json([
                    'success' => true,
                    'message' => "No Items to be Updated!",
                    "data"=> $updates
                ], 200); 
            }
            foreach ($updates as $updateItem) {
                CSDetails::where('CNTHEADER_ID', $cntHeaderId)
                    ->where('STOCKCODE', $updateItem['STOCKCODE'])
                    ->update([
                        'MNLCOUNT' => $updateItem['convMNLCOUNT'],
                        'DATEUPDATED' => now()->setTimezone('Asia/Manila'),
                    ]);
            }

            CSLog::create([
                'PROCESSID' => $cntHeaderId,
                'PROCESSEDBY' => $request->input('data.userID'),
                'ACTION' => "Update",
                'DATECREATED' => now()->setTimezone('Asia/Manila'),
                'STATUS' => 1,
            ]);

            // Log the activity
            activity('stock_count')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'count_header_id' => $cntHeaderId,
                    'updated_by' => $request->input('data.userID'),
                    'total_items_updated' => count($updates),
                    'action_type' => 'update',
                    'updated_items' => array_map(function($item) {
                        return [
                            'stock_code' => $item['STOCKCODE'],
                            'manual_count' => $item['convMNLCOUNT']
                        ];
                    }, $updates),
                    'subject_type' => 'App\\Models\\Inventory\\CSHeader',
                    'subject_id' => $cntHeaderId,
                    'event' => 'updated'
                ])
                ->log("Stock count #{$cntHeaderId} has been updated by {$request->input('data.userID')} with " . count($updates) . " items modified");
    
            return response()->json([
                'success' => true,
                'message' => "Items in the Inventory Count Successfully Updated!",
                "data"=> $updates
            ], 200); 
        }  catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, string $headerID)
    {
        try {
            // Get count header info before deletion
            $countHeader = CSHeader::where('CNTHEADER_ID', $headerID)->first();
            
            // CSHeader::where('CNTHEADER_ID', $headerID)->update(['STATUS' => 0]);
            CSLog::create([
                'PROCESSID' => $headerID,
                'PROCESSEDBY' => $request->input('data.userID'),
                'ACTION' => "Delete",
                'DATECREATED' => now()->setTimezone('Asia/Manila'),
                'STATUS' => 1,
            ]);

            // Log the activity
            activity('stock_count')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'count_header_id' => $headerID,
                    'deleted_by' => $request->input('data.userID'),
                    'previous_status' => $countHeader ? $countHeader->STATUS : 'unknown',
                    'action_type' => 'delete',
                    'subject_type' => 'App\\Models\\Inventory\\CSHeader',
                    'subject_id' => $headerID,
                    'event' => 'deleted'
                ])
                ->log("Stock count #{$headerID} has been deleted by {$request->input('data.userID')}");

            return response()->json([
                'message' => 'Inventory Count deleted successfully',
                'headerID' => $headerID,
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    /**
     * Confirm the inventory count
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $headerID
     * @return \Illuminate\Http\Response
     */
    public function confirm(Request $request, string $headerID)
    {
        try {
            // Check if the inventory count exists
            $countHeader = CSHeader::where('CNTHEADER_ID', $headerID)->first();
            
            if (!$countHeader) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inventory Count not found',
                ], 404);
            }

            // Check if already confirmed
            if ($countHeader->STATUS == 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inventory Count is already confirmed',
                ], 400);
            }

            // Update status to confirmed (2)
            CSHeader::where('CNTHEADER_ID', $headerID)->update([
                'STATUS' => 2,
                'CONFIRMEDBY' => $request->input('data.userID'),
                'DATEUPDATED' => now()->setTimezone('Asia/Manila'),
            ]);

            // Log the confirmation action
            CSLog::create([
                'PROCESSID' => $headerID,
                'PROCESSEDBY' => $request->input('data.userID'),
                'ACTION' => "Confirm",
                'DATECREATED' => now()->setTimezone('Asia/Manila'),
                'STATUS' => 1,
            ]);

            // Log the activity
            activity('stock_count')
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'count_header_id' => $headerID,
                    'confirmed_by' => $request->input('data.userID'),
                    'previous_status' => $countHeader->STATUS,
                    'new_status' => 2,
                    'action_type' => 'confirm',
                    'subject_type' => 'App\\Models\\Inventory\\CSHeader',
                    'subject_id' => $headerID,
                    'event' => 'confirmed'
                ])
                ->log("Stock count #{$headerID} has been confirmed by {$request->input('data.userID')}");

            return response()->json([
                'message' => 'Inventory Count confirmed successfully',
                'headerID' => $headerID,
                'success' => true,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function setCNTHeader(Request $request) {
        // Session::put('RRNum', $request->RRNum);
        Cache::put('CNTHeader', $request->CNTHeader, now()->addMinutes(1)); 

        $CNTHeaderID = Cache::get('CNTHeader');

        // Get count header info for logging
        $countHeader = CSHeader::where('CNTHEADER_ID', $request->CNTHeader)->first();
        
        // Log the print activity
        activity('stock_count')
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'count_header_id' => $request->CNTHeader,
                'action_type' => 'print',
                'print_type' => 'count_sheet',
                'count_status' => $countHeader ? $countHeader->STATUS : 'unknown',
                'subject_type' => 'App\\Models\\Inventory\\CSHeader',
                'subject_id' => $request->CNTHeader,
                'event' => 'printed'
            ])
            ->log("Stock count #{$request->CNTHeader} sheet has been printed");
        
        return response()->json([
            'success' => true,
            'originalData' => $request->CNTHeader,
            'sessionData' => $CNTHeaderID
        ]);
    }

    public function remCNTHeader() {
        // Session::put('RRNum', $request->RRNum);
        Cache::forget('CNTHeader');
        Cache::flush();

        return response()->json([
            'success' => true,
        ]);
    }

    public function printManualPage(){
        try {
            $cntHeaderId = Cache::get('CNTHeader') ?? "";
            $data = $this->getProductsWithMNLCount($cntHeaderId);

            // return $data;
            return view('Pages.Printing.CountSheet_printing', ['report' => $data]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function getProductsWithMNLCount($cntHeaderId)
    {
        
        $products = Product::select('StockCode', 'Description')
            ->orderBy('StockCode', 'asc')
            ->get();  

        if($cntHeaderId != '' || $cntHeaderId != null) {
            $countDetails = CSDetails::where('CNTHEADER_ID', $cntHeaderId)
                ->pluck('MNLCOUNT', 'STOCKCODE');

            $mergedData = $products->map(function ($product) use ($countDetails) {
                if ($countDetails->has($product->StockCode)) {
                    $productCalculator = new ProductCalculator();
                    $ConvResult = $productCalculator->originalDynamicConv($product->StockCode, $countDetails->get($product->StockCode));
                    return [
                        'StockCode'   => $product->StockCode,
                        'Description' => $product->Description,
                        'ConvResult'    => $ConvResult['result'],
                    ];
                } else {
                    return [
                        'StockCode'   => $product->StockCode,
                        'Description' => $product->Description,
                    ];
                }
                
                
            });

            // Return or use $mergedData
            return $mergedData;
        }
        return $products;
    }
}
