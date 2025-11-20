<?php

namespace App\Http\Controllers\api\Orders;

use Illuminate\Http\Request;
use App\Models\Orders\PO;
use App\Models\Supplier;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\http\Requests\Orders\StorePOHeaderRequest;
use Illuminate\Support\Arr;
use Barryvdh\DomPDF\Facade\Pdf;

class POController
{


    public function index()
    {
        try {
            $purchaseOrders = PO::orderBy('PODate', 'desc')->select('id', 'OrderNumber', 'PONumber', 'SupplierName', 'PODate', 'orderPlacer', 'totalDiscount', 'totalCost', 'POStatus', 'ConfirmedBy', 'EditedBy', 'DateUpdated')->get();

            if ($purchaseOrders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No purchase orders found',
                ], 200);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase orders retrieved successfully',
                'data' => $purchaseOrders
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    public function filterPOByStatus(Request $status)
    {
        try {
            // $status = $status->json()->all();
            // return response()->json(gettype($status));
            // $status =  $request->input('status');

            $query = PO::orderBy('PODate', 'desc')->select('id', 'OrderNumber', 'PONumber', 'SupplierName', 'PODate', 'orderPlacer', 'totalDiscount', 'totalCost', 'POStatus', 'ConfirmedBy', 'EditedBy', 'DateUpdated')->whereIn('POStatus', $status);

            if (in_array(null, $status->json()->all(), true)) {
                $query->orWhereNull('POStatus');
            }

            $purchaseOrders = $query->get();


            if ($purchaseOrders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No purchase orders found',
                ], 200);  // HTTP 404 Not Found
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase orders retrieved successfully',
                'data' => $purchaseOrders
            ], 200);  // HTTP 200 OK

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }




    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $data = $request->data;

            // Suppress per-item logging during creation; we'll aggregate into a single record
            request()->attributes->set('collect_po_item_logs', true);

            $po = DB::transaction(function () use ($data) {
                $items = Arr::pull($data, 'Items');
                $po = PO::create($data);
                $po->POItems()->createMany($items);
                $po->load('POItems');
                return $po;
            });

            // Build aggregated items payload
            $itemsPayload = $po->POItems->map(function($it){
                return [
                    'StockCode' => $it->StockCode,
                    'Decription' => $it->Decription,
                    'TotalPrice' => $it->TotalPrice,
                ];
            })->values()->toArray();
            $itemsTotal = array_sum(array_map(function($it){
                return (float)($it['TotalPrice'] ?? 0);
            }, $itemsPayload));

            // Log a single aggregated 'items_added' record for creation
            activity('purchase_order')
                ->withProperties([
                    'po_number' => $po->PONumber,
                    'subject_type' => 'App\\Models\\Orders\\PO',
                    'subject_id' => $po->PONumber,
                    'event' => 'items_added',
                    'items' => $itemsPayload,
                    'items_total' => $itemsTotal,
                ])
                ->event('items_added')
                ->log("Added items to Purchase Order #{$po->PONumber}");

            return response()->json([
                'success' => true,
                'message' => 'New Purchase Order created successfully',
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
     */
    public function show(string $id)
    {
        $response = array();
        try {
            $query = PO::with('POItems');
            $data = is_numeric($id)
                ? $query->where('id', (int)$id)->firstOrFail()
                : $query->where('PONumber', $id)->firstOrFail();
            $response = [
                'message' => 'Purchase orders retrieved successfully',
                'data' => $data,
                'success' => true,
            ];
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 200);  // HTTP 200 OK
        }

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePOHeaderRequest $request, string $id)
    {
        try {
            $data = $request['data'];
            unset($data['subTotal'], $data['totalDiscount'], $data['totalTax'], $data['totalCost']);
            $found = is_numeric($id)
                ? PO::where('id', (int)$id)->firstOrFail()
                : PO::where('PONumber', $id)->firstOrFail();

            if ($found->POStatus == null) {
                // Store original data for activity logging
                $originalData = $found->toArray();
                
                $found->fill($data);
                if ($found->isDirty()) {
                    $found->EditedBy = $request->user()->name ?? $request->user()->email ?? 'Admin';
                    $found->DateUpdated = now();
                    $found->save();
                }

                // Get current item stock codes from the request
                $newStockCodes = collect($data['Items'])->pluck('StockCode')->toArray();
                
                // Delete items that are no longer in the updated list and collect them for logging
                $removedItemsModels = $found->POItems()->whereNotIn('StockCode', $newStockCodes)->get();
                $removedItems = $removedItemsModels->map(function($it){
                    return [
                        'StockCode' => $it->StockCode,
                        'Decription' => $it->Decription,
                        'TotalPrice' => $it->TotalPrice,
                    ];
                })->values()->toArray();
                $found->POItems()->whereNotIn('StockCode', $newStockCodes)->delete();
                
                // Update or create items from the request
                // Collect newly added items for a single aggregated activity log
                request()->attributes->set('collect_po_item_logs', true);
                $addedItems = [];
                foreach ($data['Items'] as $item) {
                    $existingItem = $found->POItems()->where('StockCode', $item['StockCode'])->first();
                    $saved = $found->POItems()->updateOrCreate(
                        ['StockCode' => $item['StockCode']],
                        $item
                    );
                    if (!$existingItem) {
                        $addedItems[] = [
                            'StockCode' => $saved->StockCode,
                            'Decription' => $saved->Decription,
                            'TotalPrice' => $saved->TotalPrice,
                        ];
                    }
                }

                // Aggregated items_added and items_removed logs
                if (!empty($addedItems)) {
                    $itemsTotal = array_sum(array_map(function($it){
                        return (float)($it['TotalPrice'] ?? 0);
                    }, $addedItems));
                    activity('purchase_order')
                        ->withProperties([
                            'po_number' => $found->PONumber,
                            'subject_type' => 'App\\Models\\Orders\\PO',
                            'subject_id' => $found->PONumber,
                            'event' => 'items_added',
                            'items' => $addedItems,
                            'items_total' => $itemsTotal,
                        ])
                        ->event('items_added')
                        ->log("Added items to Purchase Order #{$found->PONumber}");
                }

                if (!empty($removedItems)) {
                    $removedTotal = array_sum(array_map(function($it){
                        return (float)($it['TotalPrice'] ?? 0);
                    }, $removedItems));
                    activity('purchase_order')
                        ->withProperties([
                            'po_number' => $found->PONumber,
                            'subject_type' => 'App\\Models\\Orders\\PO',
                            'subject_id' => $found->PONumber,
                            'event' => 'items_removed',
                            'items' => $removedItems,
                            'items_total' => $removedTotal,
                        ])
                        ->event('items_removed')
                        ->log("Removed items from Purchase Order #{$found->PONumber}");
                }

                return response()->json([
                    'success' => true,
                    'message' =>  "PO updated succesfully!",
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot edit PO is already processed.',
                ], 400);
            }
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' =>  $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $data = is_numeric($id)
                ? PO::where('id', (int)$id)->first()
                : PO::where('PONumber', $id)->first();

            if (!$data) {

                return response()->json([
                    'success' => false,
                    'message' => 'No purchase order found',
                ], 400);  // HTTP 400 BAD REQ.
            }


            if ($data->POStatus == null) {
                // Store PO data before deletion for logging
                $poData = $data->toArray();
                
                $data->delete();

                // Log deletion activity
                try {
                    if (false) {
                    activity('purchase_order')
                        ->withProperties([
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'url' => $request->fullUrl(),
                            'method' => $request->method(),
                            'po_number' => $poData['PONumber'],
                            'supplier_code' => $poData['SupplierCode'],
                            'supplier_name' => $poData['SupplierName'],
                            'order_placer' => $poData['orderPlacer'],
                            'action_type' => 'delete',
                            'subject_type' => 'App\\Models\\Orders\\PO',
                            'subject_id' => $poData['PONumber'],
                            'event' => 'deleted',
                            'deleted_data' => $poData
                        ])
                        ->event('deleted')
                        ->log("Deleted Purchase Order #{$poData['PONumber']}");
                    }
                } catch (\Exception $e) {
                    Log::warning('PO deletion activity logging failed: ' . $e->getMessage());
                }

                return response()->json([
                    'success' => true,
                    'message' => 'PO deleted succesfully!',
                ], 200);
            } else {

                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete PO is already processed.',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);  // HTTP 400 BAD REQ.

        }
    }

    public function POConfirm(Request $request, string $poid)
    {
        try {
            $data = is_numeric($poid)
                ? PO::where('id', (int)$poid)->first()
                : PO::where('PONumber', $poid)->first();

            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'PONumber invalid',
                ], 400);  // HTTP 400 BAD REQ.
            }

            if ($data->POStatus != null) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase order is already processed!',
                ], 400);
            }

            $data->POStatus = 1;
            $data->ConfirmedBy = $request->user()->name ?? $request->user()->email ?? 'Admin';
            $data->DateUpdated = now();
            // Save without triggering automatic 'updated' activity logging
            $data->saveQuietly();

            // Log activity as Confirmed (event: confirmed)
            try {
                    activity('purchase_order')
                        ->withProperties([
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'url' => $request->fullUrl(),
                            'method' => $request->method(),
                            'po_number' => $data->PONumber,
                            'supplier_code' => $data->SupplierCode,
                            'supplier_name' => $data->SupplierName,
                            'order_placer' => $data->orderPlacer,
                            'confirmed_by' => $request->user()->name ?? $request->user()->email ?? 'Admin',
                            'action_type' => 'confirm',
                            'subject_type' => 'App\\Models\\Orders\\PO',
                            'subject_id' => $data->PONumber,
                            'event' => 'confirmed'
                        ])
                        ->event('confirmed')
                        ->log("Confirmed Purchase Order #{$data->PONumber}");
            } catch (\Exception $e) {
                Log::warning('PO confirmation activity logging failed: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Purchase Order confirmed succesfully!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);  // HTTP 400 BAD REQ.

        }
    }

    public function generatePDF(string $poid)
    {
        try {
            $data = PO::with('POItems')->where('PONumber', $poid)->firstOrFail();
            $data->SupplierCode = trim($data->SupplierCode);
            $data->posupplier = $data->posupplier->toArray();
            $data->POItems = $data->POItems->toArray();

            try {
                activity('purchase_order')
                    ->withProperties([
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'url' => request()->fullUrl(),
                        'method' => request()->method(),
                        'po_number' => $data->PONumber,
                        'supplier_code' => $data->SupplierCode,
                        'supplier_name' => $data->SupplierName,
                        'order_placer' => $data->orderPlacer,
                        'action_type' => 'print',
                        'subject_type' => 'App\\Models\\Orders\\PO',
                        'subject_id' => $data->PONumber,
                        'event' => 'printed'
                    ])
                    ->event('printed')
                    ->log("Printed Purchase Order #{$data->PONumber}");
            } catch (\Exception $e) {
                // Silent fail on logging; should not block PDF rendering
            }

            return view('Pages.PurchaseOrder-PDF', ['po' => $data]); // Pass the user to the view
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Purchase order not found or error generating PDF: ' . $e->getMessage(),
            ], 404);
        }


        // $pdf = PDF::loadView('Pages.PurchaseOrder-PDF', ['po' => $data])
        //     ->setPaper('letter')
        //     ->setOptions(['margin-top' => 0, 'margin-bottom' => 0, 'margin-left' => 0, 'margin-right' => 0]);

        // return $pdf->download('purchase-order-' . $poid . '.pdf');

        // return response()->json($data);

    }
}
