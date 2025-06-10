<?php

namespace App\Http\Controllers\api\Inventory;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\InventoryManager;
use App\Services\ProductCalculator;
use App\Http\Controllers\Controller;
use App\Models\Inventory\InvAdjustmentLogs;
use App\Models\Inventory\InvMovements;
use App\Models\Inventory\InvWarehouse;
use App\Models\Orders\PO;

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
        try {
            $productCalculator = new ProductCalculator();
            $results = [];

            InvWarehouse::select('StockCode', 'Warehouse', 'QtyOnHand', 'DateLastStockMove')
                ->with('productdetails')
                ->orderBy('DateLastStockMove', 'desc')
                ->chunk(500, function ($products) use (&$results, $productCalculator) {
                    foreach ($products as $prod) {
                        $qtyInPcs = (float) $prod['QtyOnHand'];
                        $productDetails = $prod->productdetails;

                        $prod->conversion = $productCalculator->originalDynamicConvOptimized($productDetails, $qtyInPcs);

                        $results[] = $prod;
                    }
                });

            return response()->json([
                'success' => true,
                'message' => 'Latest inventory movement retrieved successfully',
                'data' => $results,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

public function getSKUMovement(string $StockCode, string $Warehouse)
{
    try {
        $productCalculator = new ProductCalculator();

        $runningBal = 0;
        $totalStockIn = 0;
        $totalStockOut = 0;
        $totalStockAdj = 0;
        $unitPrice = 0;
        $conversionCache = [];
        $results = [];

        InvMovements::select(
                'StockCode', 'Warehouse', 'TrnYear', 'TrnMonth', 'EntryDate',
                'MovementType', 'TrnType', 'Reference', 'SalesOrder', 'Customer',
                'Salesperson', 'CustomerPoNumber', 'TrnQty', 'UnitCost', 'NewWarehouse'
            )
            ->with(['productdetails', 'salesmandetails'])
            ->where('StockCode', $StockCode)
            ->where('Warehouse', $Warehouse)
            ->orderBy('EntryDate', 'ASC')
            ->chunk(50, function ($movements) use (&$results, &$runningBal, &$totalStockIn, &$totalStockOut, &$totalStockAdj, &$unitPrice, &$productCalculator, &$conversionCache) {
                foreach ($movements as $item) {
                    $qty = (float) $item->TrnQty;
                    $movementType = strtoupper($item->MovementType);
                    $trnType = strtoupper($item->TrnType);
                    $newWarehouse = trim($item->NewWarehouse);

                    if ($movementType === 'I') {
                        if ($trnType === 'A') {
                            $diff = $qty - $runningBal;
                            $runningBal += $diff;
                            $totalStockAdj++;
                        } elseif ($trnType === 'T') {
                            if ($newWarehouse !== '') {
                                $runningBal -= $qty;
                                $totalStockOut += $qty;
                            } else {
                                $runningBal += $qty;
                                $totalStockIn += $qty;
                            }
                        } else {
                            $runningBal += $qty;
                            $totalStockIn += $qty;
                        }
                    } elseif ($movementType === 'S') {
                        $runningBal -= $qty;
                        $totalStockOut += $qty;
                    }

                    if ($item->UnitCost > 0) {
                        $unitPrice = (float) $item->UnitCost;
                    }

                    $conversionKey = $item->StockCode . '_' . $runningBal;

                    if (!isset($conversionCache[$conversionKey])) {
                        $conversionCache[$conversionKey] = $productCalculator->originalDynamicConv($item->StockCode, $runningBal);
                    }

                    $results[] = [
                        'StockCode' => $item->StockCode,
                        'Warehouse' => $item->Warehouse,
                        'TrnYear' => $item->TrnYear,
                        'TrnMonth' => $item->TrnMonth,
                        'EntryDate' => $item->EntryDate,
                        'MovementType' => $item->MovementType,
                        'Reference' => $item->Reference,
                        'SalesOrder' => $item->SalesOrder,
                        'Customer' => $item->Customer,
                        'Salesperson' => $item->Salesperson,
                        'CustomerPoNumber' => $item->CustomerPoNumber,
                        'TrnQty' => $item->TrnQty,
                        'UnitCost' => $item->UnitCost,
                        'TrnType' => $item->TrnType,
                        'NewWarehouse' => $item->NewWarehouse,
                        'productdetails' => $item->productdetails,
                        'salesmandetails' => $item->salesmandetails,
                        'runningPcsBal' => $runningBal,
                        'runningBal' => $conversionCache[$conversionKey]['result']
                    ];
                }
            });

        $availableStock = $totalStockIn - $totalStockOut;
        $totalValue = $unitPrice * $totalStockOut;

        return response()->json([
            'success' => true,
            'message' => 'Product movement retrieved successfully',
            'data' => $results,
            'totalStockIn' => $totalStockIn,
            'totalStockOut' => $totalStockOut,
            'totalStockAdj' => $totalStockAdj,
            'totalStockAvail' => $runningBal,
            'stockCode' => $results[0]['StockCode'] ?? '',
            'description' => $results[0]['productdetails']['Description'] ?? '',
            'warehouse' => $results[0]['Warehouse'] ?? '',
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

    public function getAllWarehouse(){
        try {
            $data = InvWarehouse::select('Warehouse')
                    ->distinct()
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

    public function getAllSKUMovementPerWarehouse(string $Warehouse, string $StartDate, string $EndDate)
    {
        try {

            $StartDate .= ' 00:00:00';
            $EndDate .= ' 23:59:59';

            $productCalculator = new ProductCalculator();

            $runningBal = 0;
            $totalStockIn = 0;
            $totalStockOut = 0;
            $totalStockAdj = 0;
            $StockRecord = [];
            $results = [];
            $conversionCache = [];

            InvMovements::select(
                    'StockCode', 'Warehouse', 'TrnYear', 'TrnMonth', 'EntryDate',
                    'MovementType', 'Reference', 'SalesOrder', 'Customer',
                    'Salesperson', 'CustomerPoNumber', 'TrnQty', 'UnitCost',
                    'TrnType', 'NewWarehouse'
                )
                ->with([
                    'productdetails',
                    'salesmandetails'
                ])
                ->where('Warehouse', $Warehouse)
                ->whereBetween('EntryDate', [$StartDate, $EndDate])
                ->orderBy('EntryDate', 'ASC')
                ->chunk(50, function ($movements) use (&$StockRecord, &$totalStockIn, &$totalStockOut, &$totalStockAdj, &$results, &$productCalculator, &$conversionCache) {
                    foreach ($movements as $item) {
                        $stockCode = $item->StockCode;
                        $qty = (float) $item->TrnQty;
                        $movementType = $item->MovementType;
                        $trnType = $item->TrnType;
                        $newWarehouse = $item->NewWarehouse;

                        if (!isset($StockRecord[$stockCode])) {
                            $StockRecord[$stockCode] = [
                                'StockCode' => $stockCode,
                                'Qty' => 0
                            ];
                        }

                        // Adjust quantity
                        if ($movementType === 'I') {
                            if ($trnType === 'A') {
                                $diff = $qty - $StockRecord[$stockCode]['Qty'];
                                $StockRecord[$stockCode]['Qty'] += $diff;
                                $totalStockAdj++;
                            } elseif ($trnType === 'T') {
                                if (trim($newWarehouse) === '') {
                                    $StockRecord[$stockCode]['Qty'] += $qty;
                                    $totalStockIn++;
                                } else {
                                    $StockRecord[$stockCode]['Qty'] -= $qty;
                                    $totalStockOut++;
                                }
                            } else {
                                $StockRecord[$stockCode]['Qty'] += $qty;
                                $totalStockIn++;
                            }
                        } elseif ($movementType === 'S') {
                            $StockRecord[$stockCode]['Qty'] -= $qty;
                            $totalStockOut++;
                        }

                        // Store current quantity
                        $currentQty = $StockRecord[$stockCode]['Qty'];
                        $conversionKey = $stockCode . '_' . $currentQty;

                        // Cache conversion result
                        if (!isset($conversionCache[$conversionKey])) {
                            $conversionCache[$conversionKey] = $productCalculator->originalDynamicConv($stockCode, $currentQty);
                        }

                        $results[] = [
                            'StockCode' => $item->StockCode,
                            'Warehouse' => $item->Warehouse,
                            'TrnYear' => $item->TrnYear,
                            'TrnMonth' => $item->TrnMonth,
                            'EntryDate' => $item->EntryDate,
                            'MovementType' => $item->MovementType,
                            'Reference' => $item->Reference,
                            'SalesOrder' => $item->SalesOrder,
                            'Customer' => $item->Customer,
                            'Salesperson' => $item->Salesperson,
                            'CustomerPoNumber' => $item->CustomerPoNumber,
                            'TrnQty' => $item->TrnQty,
                            'UnitCost' => $item->UnitCost,
                            'TrnType' => $item->TrnType,
                            'NewWarehouse' => $item->NewWarehouse,
                            'productdetails' => $item->productdetails,
                            'salesmandetails' => $item->salesmandetails,
                            'CurrentQty' => $currentQty,
                            'conversion' => $conversionCache[$conversionKey]['result']
                        ];
                    }
                });

            return response()->json([
                'success' => true,
                'message' => 'Product movement retrieved successfully',
                'data' => $results,
                'totalStockIn' => $totalStockIn,
                'totalStockOut' => $totalStockOut,
                'totalStockAdj' => $totalStockAdj,
                'totalStockAvail' => count($StockRecord),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getWarehouseMovements(string $Warehouse){
        try {
            $data = InvMovements::select('StockCode', 'MovementType', InvMovements::raw('COUNT(*) as MovementCount'))
                        ->where('Warehouse', $Warehouse)
                        ->whereIn('MovementType', ['I', 'S'])
                        ->groupBy('StockCode', 'MovementType')
                        ->orderBy('StockCode')
                        ->orderBy('MovementType')
                        ->get();
            // dd($data);
            if (!$data) {
                return response()->json([
                    'response' => 'No Movement of Products In Warehouse',
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

    public function WarehouseStockInOut(string $Warehouse, string $Startdate, string $Enddate){
        try {
            $results = InvMovements::select(
                    'trnYear',
                    'trnMonth',
                    'MovementType',
                    InvMovements::raw('COUNT(*) as TotalTrn')
                )
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

    public function getTopMovingProducts(string $Warehouse, string $Startdate, string $Enddate){
        try {
            $topMovers = InvMovements::select('StockCode', InvMovements::raw('SUM(TrnQty) as TotalMoved'))
                            ->where('MovementType', 'S')
                            ->where('Warehouse', $Warehouse)
                            ->whereBetween('EntryDate', [$Startdate, $Enddate])
                            ->groupBy('StockCode')
                            ->orderBy('TotalMoved', 'desc')
                            ->limit(10)
                            ->get();

            // dd($topMovers);
            if (!$topMovers) {
                return response()->json([
                    'response' => 'No Movement of Products In Warehouse',
                    'status_response' => 0
                ]);
            } else {
                return response()->json([
                    'response' => $topMovers,
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

    public function getAllTransfer(){
        try {
            $productCalculator = new ProductCalculator();
            $data = InvMovements::select('StockCode','Warehouse','TrnQty','NewWarehouse','EntryDate','Reference')->with('productdetails')->where('MovementType', "I")->where('TrnType', "T")->orderBy('EntryDate','DESC')->orderBy('NewWarehouse', 'ASC')->get();

            $data->transform(function ($row) use ($productCalculator) {
                $qty = (int) floor($row->TrnQty);

                $conversion = $productCalculator->originalDynamicConv($row->StockCode, $qty);

                $row->runningBal = $conversion['result'];

                return $row;
            });

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

    public function getWarehouseInv(string $warehouse){
        try {
            $productCalculator = new ProductCalculator();
            $data = InvWarehouse::select('StockCode','Warehouse','QtyOnHand')
                        ->with('productdetails')->where('Warehouse', $warehouse)->get();

            $data->transform(function ($row) use ($productCalculator) {
                $qty = (int) floor($row->QtyOnHand);

                $conversion = $productCalculator->originalDynamicConv($row->StockCode, $qty);

                $row->runningBal = $conversion['result'];

                return $row;
            });

            return response()->json([
                'success' => true,
                'message' => 'Warehouse inventory retrieved successfully',
                'data' => $data,
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function InvWarehouseStockTransfer(Request $request){
        try {
            $InventoryManager = new InventoryManager();
            $items = $request->data['Items'];
            $mainDetails = $request->data;
            unset($mainDetails['Items']);

            foreach ($items as $item) {
                $sku = $item['StockCode'];
                $warehouse = $mainDetails['Warehouse'];
                $newWarehouse = $mainDetails['NewWarehouse'];
                $qty = $item['TrnQty'];
                $InventoryManager->InvWareHouseDirectionHandler($sku, $warehouse, $qty, "TRANSFER", $newWarehouse);
                $InventoryManager->InvMovement($mainDetails,  $item, 'I', 'T');
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock transfer successful',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function getAllAdjustments(){
        try {
            $data = InvAdjustmentLogs::select('REFERENCE','STOCKCODE','WAREHOUSE','ENTRY_DATE','PREV_QTY','NEW_QTY','ADJUSTED_QTY','ADJUSTMENT_TYPE','REASON','HANDLED_BY')->with('productdetails')->orderBy('ENTRY_DATE','DESC')->get();

            return response()->json([
                'success' => true,
                'message' => 'Latest inventory adjustment retrieved successfully',
                'data' => $data,
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function InvWarehouseStockAdjustment(Request $request){

        try {
            $InventoryManager = new InventoryManager();
            $items = $request->data['Items'];
            $mainDetails = $request->data;
            unset($mainDetails['Items']);

            $timestamp = now()->setTimezone('Asia/Manila')->format('Ymd_His');
            $ref = 'ADJ' . $mainDetails['Warehouse'] . $timestamp;
            $mainDetails["adjustmentRef"] = $ref;

            // dd($mainDetails);

            foreach ($items as $item) {
                $sku = $item['StockCode'];
                $warehouse = $mainDetails['Warehouse'];
                $qty = $item['TrnQty'];
                $InventoryManager->InvWareHouseDirectionHandler($sku, $warehouse, $qty, "ADJUST", null);
                $InventoryManager->InvMovement($mainDetails,  $item, 'I', 'A');
            }

            return response()->json([
                'success' => true,
                'message' => 'Stock transfer successful',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

}
