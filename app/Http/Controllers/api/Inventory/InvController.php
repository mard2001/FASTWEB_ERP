<?php

namespace App\Http\Controllers\api\Inventory;

use Illuminate\Http\Request;
use App\Services\ProductCalculator;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InvMovements;
use App\Models\Inventory\InvWarehouse;
use App\Models\Product;

class InvController extends Controller
{
    public function latestInvMovement(){
        try{
            $data = InvMovements::select('StockCode','Warehouse','EntryDate','MovementType','TrnQty','TrnValue')->where('TrnMonth', date('m'))->orderBy('EntryDate','desc')->limit(1000)->get();

            return response()->json([
                'success' => true,
                'message' => 'Latest inventory movement retrieved successfully',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function getStocks(){
        try{
            $data = InvMovements::select('StockCode','Warehouse','QtyOnHand','DateLastPurchase')
                    ->with('productdetails')->get();

            return response()->json([
                'success' => true,
                'message' => 'Latest inventory movement retrieved successfully',
                'data' => $data
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function getInventory(){
        try{
            $productCalculator = new ProductCalculator();
            $data = InvWarehouse::select('StockCode','Warehouse','QtyOnHand','DateLastStockMove')
                ->with('productdetails')->orderBy('DateLastStockMove', 'desc')->get();

            foreach($data as $prod){
                $qtyInPcs = (float)$prod['QtyOnHand'];
                $productDetails = Product::where('StockCode', $prod['StockCode'])->first();

                $prod->conversion = $productCalculator->originalDynamicConvOptimized($productDetails, $qtyInPcs);
            }

            // foreach($data as $prod){
            //     $ProductUoms = [$prod['productdetails']['StockUom'],$prod['productdetails']['AlternateUom'],$prod['productdetails']['OtherUom']];
            //     $qtyInLargestUnit = (float)$prod['QtyOnHand'];
            //     $ConvFactAltUom = (float)$prod['productdetails']['ConvFactAltUom'];
            //     $ConvFactOthUom = (float)$prod['productdetails']['ConvFactOthUom'];

            //     $prod->conversion = $productCalculator->reverseConvertFromLargestUnit($ProductUoms, $qtyInLargestUnit, $ConvFactAltUom, $ConvFactOthUom);
            // }
            
            return response()->json([
                'success' => true,
                'message' => 'Latest inventory movement retrieved successfully',
                'data' => $data,
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function getSKUMovement2(string $StockCode){
        try {
            $productCalculator = new ProductCalculator();
            $runningBal = 0;
            $total_stockin = 0;
            $total_stockout = 0;
            $availstock = 0;
            $unitPrice = '';

            $data = InvMovements::select('StockCode', 'Warehouse', 'TrnYear', 'TrnMonth', 'EntryDate','MovementType','Reference', 'SalesOrder', 'Customer', 'SalesPerson','CustomerPoNumber', 'TrnQty', 'UnitCost')
                                ->with(['productdetails', 'salesmanetails'])->where('StockCode', $StockCode)
                                ->orderBy('EntryDate', 'ASC')->get();


            foreach($data as $movementRow){
                if($movementRow->MovementType == "I"){
                    $runningBal += floor($movementRow->TrnQty);
                    $movementRow->runningPcsBal = $runningBal;
                    $total_stockin += floor($movementRow->TrnQty);
                    
                } else if($movementRow->MovementType == "S"){
                    $runningBal -= floor($movementRow->TrnQty);
                    $movementRow->runningPcsBal = $runningBal;
                    $total_stockout += floor($movementRow->TrnQty);
                }
                if((float)$movementRow->UnitCost != 0){
                    $unitPrice = (float)$movementRow->UnitCost;
                }
                $conv = $productCalculator->originalDynamicConv($movementRow->StockCode, $runningBal);
                $movementRow->runningBal = $conv["result"];
            }
            $availstock = $total_stockin - $total_stockout;
            $totalPrice = $unitPrice*$total_stockout;

            return response()->json([
                'success' => true,
                'message' => 'Product movement retrieved successfully',
                'data' => $data,
                'totalStockIn' => $total_stockin,
                'totalStockOut' => $total_stockout,
                'totalStockAvail' => $availstock,
                'stockCode' => $data[0]['StockCode'],
                'description' => $data[0]['productdetails']['Description'],
                'warehouse' => $data[0]['Warehouse'],
                'unitPrice' => $unitPrice,
                'ttlPrice' => $totalPrice
            ], 200);  // HTTP 200 OK
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function getSKUMovement(string $StockCode, string $Warehouse)
    {   
        try {
            $productCalculator = new ProductCalculator();

            $runningBal = 0;
            $totalStockIn = 0;
            $totalStockOut = 0;
            $unitPrice = 0;

            // Fetch only necessary data with eager loading
            $movements = InvMovements::select( 'StockCode', 'Warehouse', 'TrnYear', 'TrnMonth', 'EntryDate', 'MovementType', 'Reference', 'SalesOrder', 'Customer', 'SalesPerson', 'CustomerPoNumber', 'TrnQty', 'UnitCost')
                ->with(['productdetails', 'salesmanetails'])
                ->where('StockCode', $StockCode)
                ->where('Warehouse', $Warehouse)
                ->orderBy('EntryDate', 'ASC')
                // ->limit(10)
                ->get();
            // Transform the data
            $movements->transform(function ($row) use (&$runningBal, &$totalStockIn, &$totalStockOut, &$unitPrice, $productCalculator) {
                $qty = (int) floor($row->TrnQty);

                if (strtoupper($row->MovementType) == 'I') {
                    $runningBal += $qty;
                    $totalStockIn += $qty;
                } elseif (strtoupper($row->MovementType) == 'S') {
                    $runningBal -= $qty;
                    $totalStockOut += $qty;
                }

                // Save the latest non-zero unit cost
                if ($row->UnitCost > 0) {
                    $unitPrice = (float) $row->UnitCost;
                }

                $conversion = $productCalculator->originalDynamicConv($row->StockCode, $runningBal);
                // dd($conversion);

                $row->runningPcsBal = $runningBal;
                $row->runningBal = $conversion['result'];

                return $row;
            });

            $availableStock = $totalStockIn - $totalStockOut;
            $totalValue = $unitPrice * $totalStockOut;

            return response()->json([
                'success' => true,
                'message' => 'Product movement retrieved successfully',
                'data' => $movements,
                'totalStockIn' => $totalStockIn,
                'totalStockOut' => $totalStockOut,
                'totalStockAvail' => $availableStock,
                'stockCode' => $movements[0]->StockCode ?? '',
                'description' => $movements[0]->productdetails->Description ?? '',
                'warehouse' => $movements[0]->Warehouse ?? '',
                'unitPrice' => $unitPrice,
                'ttlPrice' => $totalValue
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }   

    public function StockInOut(string $StockCode, string $Warehouse){
        try {
            $results = InvMovements::select(
                    'trnYear',
                    'trnMonth',
                    'MovementType',
                    InvMovements::raw('COUNT(*) as TotalTrn')
                )
                ->where('StockCode', $StockCode)
                ->where('Warehouse', $Warehouse)
                ->whereIn('MovementType', ['I', 'S'])
                ->whereRaw("
                    DATEFROMPARTS(trnYear, trnMonth, 1) >= 
                    DATEADD(MONTH, -11, DATEFROMPARTS(YEAR(GETDATE()), MONTH(GETDATE()), 1))
                ")
                ->groupBy('trnYear', 'trnMonth', 'MovementType')
                ->orderBy('trnYear')
                ->orderBy('trnMonth')
                ->orderBy('MovementType')
                ->get();

            return $results;
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function getProducts(){
        try {
            $data = InvWarehouse::select('StockCode')
                    ->distinct()
                    ->with('prodname')
                    ->orderBy('StockCode')
                    ->get();
                    
            if (!$data) {
                return response()->json([
                    'response' => 'products not found',
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
    
    public function getProdWarehouse(string $StockCode){
        try {
            $data = InvWarehouse::select('Warehouse')
                    ->distinct()
                    ->where('StockCode', $StockCode)
                    ->get();
            // dd($data);
            if (!$data) {
                return response()->json([
                    'response' => 'Product warehouse not found',
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
}
