<?php

namespace App\Http\Controllers\api\ReceivingReports;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Orders\PO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ProductCalculator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Models\Inventory\InvMovements;
use App\Models\Inventory\InvWarehouse;
use Illuminate\Support\Facades\Session;
use App\Events\Inventory\InventoryMovement;
use App\Events\Inventory\InventoryWarehouse;
use App\Models\ReceivingReports\ReceivingRHeader;
use App\Models\ReceivingReports\ReceivingRDetails;

class RRController extends Controller
{

    protected $productController;

    public function __construct(ProductCalculator $productController)
    {
        $this->productController = $productController;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            
            $dataset = [];
            $dataset = ReceivingRHeader::select('RRNo', 'Reference', 'RRDATE', 'Status', 'RECEIVEDBY', 'PO_NUMBER', 'Total', 'PreparedBy')
                ->with([
                    'poincluded' => function ($query) {
                        $query->selectRaw('PONumber, TRIM(SupplierCode) as SupplierCode')
                            ->with(['posupplier' => function ($supplierQuery) {
                                $supplierQuery->select('SupplierCode', 'SupplierName', 'CompleteAddress');
                            }]);
                    },
                    'preparedby' => function ($query) {
                        $query->selectRaw("CAST(USERID AS VARCHAR) as USERID, FULLNAME");
                    }
                ])
                ->get();


            if (count($dataset) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No Receiving Report Data found',
                ], 200);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Receiving Reports retrieved successfully',
                'data' => $dataset
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
    public function show($RRNum)
    {
        try {

            $data = ReceivingRHeader::select('RRNo', 'Reference', 'RRDATE', 'Status', 'RECEIVEDBY', 'PO_NUMBER', 'Total')
                ->with([
                    'rrdetails' => function ($query) {
                        $query->with(['product' => function ($productQuery) {
                            $productQuery->whereIn('StockCode', function ($subQuery) {
                                $subQuery->selectRaw("CAST(SKU AS VARCHAR)") // Ensure SKU is treated as VARCHAR
                                    ->from('tblInvRRDetails');
                            });
                        }]);
                    },
                    'poincluded' => function ($query) {
                        $query->selectRaw('PONumber, TRIM(SupplierCode) as SupplierCode') // Fetch only required columns from PO
                            ->with(['posupplier' => function ($supplierQuery) {
                                $supplierQuery->select('SupplierCode', 'SupplierName', 'CompleteAddress'); // Fetch required supplier details
                            }]);
                    }
                ])
                ->where('RRno', $RRNum)
                ->get()
                ->map(function ($header) {
                    foreach ($header->rrdetails as $detail) {
                        if ($detail->product) {
                            // Call the convertProductToLargesttUnit method
                            $uoms = array_map('strval', [
                                $detail->product->StockUom, $detail->product->AlternateUom, $detail->product->OtherUom
                            ]);
                            
                            $detail->convertedQuantity = app(ProductCalculator::class)->convertProductToLargesttUnit(
                                $uoms, 
                                $detail->Quantity, 
                                $detail->product->ConvFactAltUom, 
                                $detail->product->ConvFactOthUom
                            );
                        }
                    }
                    return $header;
                });


            if (count($data) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receiving Report not found',
                ], 404);
            }
            return response()->json([
                'message' => 'Receiving Report retrieved successfully',
                'data' => $data,
                'success' => true,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function showv2($RRNum)
    {
        try {
            $perPOData = ReceivingRDetails::select('PO_NUMBER', 'RRNo')
            ->with('rrheader') 
            ->where('RRNo', $RRNum)
            ->distinct()
            ->get();

            foreach ($perPOData as $PO) {
                $supCode = PO::where('PONumber', $PO->PO_NUMBER)->value('SupplierCode');
                $PO->POSupplierCode = $supCode; 

                $supplierDetails = Supplier::select('SupplierName','SupplierType','CompleteAddress')
                    ->where('SupplierCode', $supCode)
                    ->first(); 

                if ($supplierDetails) {
                    $PO->SupplierName = $supplierDetails->SupplierName;
                    $PO->SupplierType = $supplierDetails->SupplierType;
                    $PO->CompleteAddress = $supplierDetails->CompleteAddress;
                    
                }

                $RRItems = ReceivingRDetails::select('SKU','Quantity','UOM','WhsCode','UnitPrice','NetVat','Vat','Gross')
                    ->where('RRNo', $RRNum)
                    ->where('PO_Number',$PO->PO_NUMBER)
                    ->get();

                $PO->RRItems = $RRItems;
                
                foreach ($PO->RRItems as $productItem) {
                    $ProdDetails = Product::select('Description','StockUom','AlternateUom','OtherUom','ConvFactAltUom','ConvMulDiv','ConvFactOthUom')
                        ->where('StockCode', $productItem->SKU)
                        ->first();
                    if ($ProdDetails) {
                        // Add product details as key-value pairs to the current productItem
                        $productItem->Description = $ProdDetails->Description;
                        $productItem->StockUom = $ProdDetails->StockUom;
                        $productItem->AlternateUom = $ProdDetails->AlternateUom;
                        $productItem->OtherUom = $ProdDetails->OtherUom;
                        $productItem->ConvFactAltUom = $ProdDetails->ConvFactAltUom;
                        $productItem->ConvMulDiv = $ProdDetails->ConvMulDiv;
                        $productItem->ConvFactOthUom = $ProdDetails->ConvFactOthUom;
                    }
                }
            }

            if (count($perPOData) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receiving Report not found',
                ], 404);
            }

            return response()->json([
                'message' => 'Receiving Report retrieved successfully',
                'data' => $perPOData,
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function setRRNum(Request $request) {
        // Session::put('RRNum', $request->RRNum);
        Cache::put('RRNum', $request->RRNum, now()->addMinutes(1)); 

        $RRNum = Cache::get('RRNum');
        return response()->json([
            'success' => true,
            'originalData' => $request->RRNum,
            'sessionData' => $RRNum
        ]);
    }

    public function printPage()
    {
        try{
            $RRNum = Cache::get('RRNum');
            if($RRNum != null){
                $data = ReceivingRHeader::select('RRNo', 'Reference', 'RRDATE', 'Status', 'RECEIVEDBY', 'PO_NUMBER', 'Total')
                    ->with([
                        'rrdetails' => function ($query) {
                            $query->with(['product' => function ($productQuery) {
                                $productQuery->whereIn('StockCode', function ($subQuery) {
                                    $subQuery->selectRaw("CAST(SKU AS VARCHAR)") // Ensure SKU is treated as VARCHAR
                                        ->from('tblInvRRDetails');
                                });
                            }]);
                        },
                        'poincluded' => function ($query) {
                            $query->selectRaw('PONumber, TRIM(SupplierCode) as SupplierCode') // Fetch only required columns from PO
                                ->with(['posupplier' => function ($supplierQuery) {
                                    $supplierQuery->select('SupplierCode', 'SupplierName', 'CompleteAddress'); // Fetch required supplier details
                                }]);
                        }
                    ])
                    ->where('RRno', $RRNum)
                    ->first();
                    // Check if data is found before modifying it
                    if ($data) {
                        tap($data, function ($header) {
                            foreach ($header->rrdetails as $detail) {
                                if ($detail->product) {
                                    $uoms = array_map('strval', [
                                        $detail->product->StockUom, $detail->product->AlternateUom, $detail->product->OtherUom
                                    ]);

                                    $detail->convertedQuantity = app(ProductCalculator::class)->convertProductToLargesttUnit(
                                        $uoms,
                                        $detail->Quantity,
                                        $detail->product->ConvFactAltUom,
                                        $detail->product->ConvFactOthUom
                                    );
                                }
                            }
                        });
                    }

                return view('Pages.Printing.RR_printing', ['report' => $data]);
            };

            return view('Pages.receiving_report_page');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }

        
    }

    public function approveRR(Request $request){
        try {
            $rrNo = $request->data['rrNum'];
            $user = $request->data['user'];
            $details = $request->data['rrData']['rrdetails'];
            $rrHeaderDetails = $request->data;
            unset($rrHeaderDetails['rrData']['rrdetails'], $rrHeaderDetails['rrData']['poincluded']);

            $isPresent = ReceivingRHeader::where('RRNo', $rrNo)
                ->update([
                    'status' => 2,  // Confirmed
                    'ApprovedBy' => $user,
                    'CheckedBy' => $user,
                    'DATEUPDATED' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                ]);

            if(!$isPresent){
                return response()->json([
                    'success' => false,
                    'message' => 'Receiving Report not found',
                ], 404);
            }

            
            foreach ($details as $detail) {
                $sku = $detail['SKU'];
                $warehouse = $detail['warehouse'] = 'M1';
                $qty = $detail['convertedQuantity']['convertedToLargestUnit'];
                

                event(new InventoryWarehouse($sku, $warehouse, $qty));
                event(new InventoryMovement($rrHeaderDetails,  $detail, 'I'));
            }

            return response()->json([
                'message' => 'Receiving Report confirmed successfully',
                'success' => true,
                // 'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }



}
