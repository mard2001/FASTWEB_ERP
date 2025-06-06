<?php

namespace App\Services;

use App\Models\Inventory\InvMovements;
use App\Models\Inventory\InvWarehouse;
use App\Models\Inventory\InvAdjustmentLogs;

class InventoryManager
{
    public function isInvEnough($productsQty)
    {
        try {
            foreach ($productsQty as $prod) {
                $res = InvWarehouse::where('StockCode', $prod['stockCode'])
                    ->where('Warehouse', $prod['warehouse'])
                    ->where('QtyOnHand', '>', (float)$prod['qty'])
                    ->first();
                if (!$res) {
                    return false; 
                }
            }

            return true; 

        } catch (\Exception $e) {
            return false;  
        }
    }

    public function isInvFound($sku, $warehouse)
    {
        try {
            $res = InvWarehouse::select('QtyOnHand')->where('StockCode', $sku)
                ->where('Warehouse', $warehouse)
                ->first();
;
            if (!$res) {
                return false; 
            }

            return $res; 

        } catch (\Exception $e) {
            return false;  
        }
    }

    public function AddProdToInvWarehouse($sku, $warehouse, $qty){
        InvWarehouse::create([
            'StockCode' => $sku,
            'Warehouse' => $warehouse,
            'QtyOnHand' => $qty,
            'DateLastStockMove' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
            'DateWhAdded' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
        ]);
    }

    public function InvWareHouseDirectionHandler($sku, $warehouse, $qty, $direction, $newWarehouse)
    {
        try {
            $existing = $this->isInvFound($sku, $warehouse);

            if($direction == "IN"){
                if($existing){
                    InvWarehouse::where('StockCode', $sku)
                    ->where('Warehouse', $warehouse)
                    ->update([
                        'QtyOnHand' => (float)$existing->QtyOnHand + (float)$qty,
                        'DateLastStockMove' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                    ]);
                } else{
                    $this->AddProdToInvWarehouse($sku, $warehouse, $qty);
                }

                return response()->json([
                    'message' => 'Inventory Inbound successfully',
                    'success_status' => 1,
                ]);
            } else if($direction == "OUT"){
                InvWarehouse::where('StockCode', $sku)
                ->where('Warehouse', $warehouse)
                ->update([
                    'QtyOnHand' => (float)$existing->QtyOnHand - (float)$qty,
                    'DateLastStockMove' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                ]);

                return response()->json([
                    'message' => 'Inventory Outbound successfully',
                    'success_status' => 1,
                ]);
            } else if($direction == "TRANSFER"){
                if($existing){
                    InvWarehouse::where('StockCode', $sku)
                    ->where('Warehouse', $warehouse)
                    ->update([
                        'QtyOnHand' => (float)$existing->QtyOnHand - (float)$qty,
                        'DateLastStockMove' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                    ]);

                    $existingDestWH = $this->isInvFound($sku, $newWarehouse);
                    // dd($existingDestWH);
                    if($existingDestWH){
                        InvWarehouse::where('StockCode', $sku)
                        ->where('Warehouse', $newWarehouse)
                        ->update([
                            'QtyOnHand' => (float)$existing->QtyOnHand + (float)$qty,
                            'DateLastStockMove' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                        ]);
                    } else{
                        $this->AddProdToInvWarehouse($sku, $newWarehouse, $qty);
                    }
    
                    return response()->json([
                        'message' => 'Inventory Transfer successfully',
                        'success_status' => 1,
                    ]);
                }
            } else if($direction == "ADJUST"){
                if($existing){
                    InvWarehouse::where('StockCode', $sku)
                    ->where('Warehouse', $warehouse)
                    ->update([
                        'QtyOnHand' => (float)$qty,
                        'DateLastStockMove' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                    ]);
                    return response()->json([
                        'message' => 'Inventory Adjustment successfully',
                        'success_status' => 1,
                    ]);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  
        }
    }

    public function InvMovement($headerDetails,  $detail, $movementType, $trnType)
    {
        $productData = $detail;

        if($movementType == 'I'){
            if($trnType == 'R'){
                $headerData = $headerDetails['rrData'];
                InvMovements::create([
                    'StockCode' => $productData['SKU'],
                    'Warehouse' => $productData['warehouse'],
                    'TrnYear' => now()->setTimezone('Asia/Manila')->format('Y'),
                    'TrnMonth' => now()->setTimezone('Asia/Manila')->format('n'),
                    'EntryDate' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                    'MovementType' => 'I',
                    'TrnType' => 'R',
                    'TrnQty' => $productData['Quantity'],
                    'Reference' => $headerData['RRNo'],
                    'UnitCost' => $productData['UnitPrice'],
                    'CustomerPoNumber' => $headerData['PO_NUMBER'],
                ]);
            } else if($trnType == 'T'){
                $timestamp = now()->setTimezone('Asia/Manila')->format('Ymd_His');
                $ref = 'T' . $headerDetails['Warehouse'] . $headerDetails['NewWarehouse'] . $timestamp;

                InvMovements::create([
                    'StockCode' => $productData['StockCode'],
                    'Warehouse' => $headerDetails['Warehouse'],
                    'TrnYear' => now()->setTimezone('Asia/Manila')->format('Y'),
                    'TrnMonth' => now()->setTimezone('Asia/Manila')->format('n'),
                    'EntryDate' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                    'MovementType' => 'I',
                    'TrnType' => 'T',
                    'TrnQty' => $productData['TrnQty'],
                    'NewWarehouse' => $headerDetails['NewWarehouse'],
                    'Reference' => $ref,
                ]);

                sleep(2);

                InvMovements::create([
                    'StockCode' => $productData['StockCode'],
                    'Warehouse' => $headerDetails['NewWarehouse'],
                    'TrnYear' => now()->setTimezone('Asia/Manila')->format('Y'),
                    'TrnMonth' => now()->setTimezone('Asia/Manila')->format('n'),
                    'EntryDate' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                    'MovementType' => 'I',
                    'TrnType' => 'T',
                    'TrnQty' => $productData['TrnQty'],
                    'Reference' => $ref,
                ]);
            } else if($trnType == 'A'){
                InvMovements::create([
                    'StockCode' => $productData['StockCode'],
                    'Warehouse' => $headerDetails['Warehouse'],
                    'TrnYear' => now()->setTimezone('Asia/Manila')->format('Y'),
                    'TrnMonth' => now()->setTimezone('Asia/Manila')->format('n'),
                    'EntryDate' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                    'MovementType' => 'I',
                    'TrnType' => 'A',
                    'TrnQty' => $productData['TrnQty'],
                    'Reference' => $headerDetails['adjustmentRef'],
                ]);

                InvAdjustmentLogs::create([
                    'REFERENCE' => $headerDetails['adjustmentRef'],
                    'STOCKCODE' => $productData['StockCode'],
                    'WAREHOUSE' => $headerDetails['Warehouse'],
                    'ENTRY_DATE' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                    'PREV_QTY' => $productData['ActualQty'],
                    'NEW_QTY' => $productData['TrnQty'],
                    'ADJUSTED_QTY' => (float)$productData['ActualQty'] - (float)$productData['TrnQty'],
                    'ADJUSTMENT_TYPE' => $headerDetails['Type'],
                    'REASON' => '',
                    'HANDLED_BY' => $headerDetails['LastOperator']
                ]);
            }
        } else if($movementType == 'S'){
            $headerData = $headerDetails;

            InvMovements::create([
                'StockCode' => $productData['MStockCode'],
                'Warehouse' => $productData['MWarehouse'],
                'TrnYear' => now()->setTimezone('Asia/Manila')->format('Y'),
                'TrnMonth' => now()->setTimezone('Asia/Manila')->format('n'),
                'EntryDate' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                'MovementType' => 'S',
                'TrnQty' => $productData['QTYinPCS'],
                'SalesOrder' => $productData['SalesOrder'],
                'Customer' => $headerData['Customer'],
                'Branch' => $headerData['Branch'],
                'Salesperson' => $headerData['Salesperson'],
                'CustomerPoNumber' => $headerData['CustomerPoNumber'],
                'OrderType' => $headerData['DocumentType'],
                'ProductClass' => $productData['MProductClass'],
                'DetailLine' => $productData['SalesOrderLine'],
            ]);
        }
    }

    public function InvWarehouseTransfer(){

    }
}
