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
            
            $data = CSHeader::orderBy('DATECREATED','desc')->with('user')->where('STATUS', '1')->get();
            
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
            $data = CSHeader::with(['details', 'user', 'details.proddetails' => function ($productQuery) {
                $productQuery->select('StockCode', 'Description', 'StockUom', 'AlternateUom', 'OtherUom', 'ConvFactAltUom')
                    ->whereIn('StockCode', function ($subQuery) {
                        $subQuery->selectRaw("CAST(StockCode AS VARCHAR) FROM TBLINVCOUNT_DETAILS");
                    });
            }])->where('CNTHEADER_ID', $id)->firstOrFail();
            
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
            // CSHeader::where('CNTHEADER_ID', $headerID)->update(['STATUS' => 0]);
            CSLog::create([
                'PROCESSID' => $headerID,
                'PROCESSEDBY' => $request->input('data.userID'),
                'ACTION' => "Delete",
                'DATECREATED' => now()->setTimezone('Asia/Manila'),
                'STATUS' => 1,
            ]);

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

    public function setCNTHeader(Request $request) {
        // Session::put('RRNum', $request->RRNum);
        Cache::put('CNTHeader', $request->CNTHeader, now()->addMinutes(1)); 

        $CNTHeaderID = Cache::get('CNTHeader');
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
