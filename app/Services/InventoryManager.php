<?php

namespace App\Services;

use App\Models\Inventory\InvMovements;
use App\Models\Inventory\InvWarehouse;

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

    public function AddProdToInvWarehouse($sku, $warehouse, $qty){
        InvWarehouse::create([
            'StockCode' => $sku,
            'Warehouse' => $warehouse,
            'QtyOnHand' => $qty,
            'DateLastStockMove' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
            'DateWhAdded' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
        ]);
    }

    public function InvWareHouseDirectionHandler($sku, $warehouse, $qty, $direction)
    {
        try {
            $existing = InvWarehouse::select('QtyOnHand')->where('StockCode', $sku)
                ->where('Warehouse', $warehouse)
                ->first();

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
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 0,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  
        }
    }

    public function InvMovement($headerDetails,  $detail, $movementType)
    {
        $productData = $detail;

        if($movementType == 'I'){
            $headerData =$headerDetails['rrData'];
            InvMovements::create([
                'StockCode' => $productData['SKU'],
                'Warehouse' => $productData['warehouse'],
                'TrnYear' => now()->setTimezone('Asia/Manila')->format('Y'),
                'TrnMonth' => now()->setTimezone('Asia/Manila')->format('n'),
                'EntryDate' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                'MovementType' => 'I',
                'TrnQty' => $productData['Quantity'],
                'Reference' => $headerData['Reference'],
                'UnitCost' => $productData['UnitPrice'],
            ]);
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
                'DetailLine' => $productData['SalesOrderLine']
            ]);
        }
    }
}
