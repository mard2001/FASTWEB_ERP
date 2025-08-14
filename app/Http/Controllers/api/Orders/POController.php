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
            $purchaseOrders = PO::orderBy('DateUploaded', 'desc')->select('id', 'OrderNumber', 'PONumber', 'SupplierName', 'PODate', 'orderPlacer', 'totalDiscount', 'totalCost', 'POStatus', 'ConfirmedBy', 'EditedBy', 'DateUpdated')->get();

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

            $query = PO::orderBy('DateUploaded', 'desc')->select('id', 'OrderNumber', 'PONumber', 'SupplierName', 'PODate', 'orderPlacer', 'totalDiscount', 'totalCost', 'POStatus', 'ConfirmedBy', 'EditedBy', 'DateUpdated')->whereIn('POStatus', $status);

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

            DB::transaction(function () use ($data, $request) {
                $items = Arr::pull($data, 'Items');
                $po = PO::create($data);
                $po->POItems()->createMany($items);
                
                // Log creation activity
                try {
                    activity('purchase_order')
                        ->performedOn($po)
                        ->withProperties([
                            'ip' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                            'url' => $request->fullUrl(),
                            'method' => $request->method(),
                            'po_number' => $po->PONumber,
                            'supplier_code' => $po->SupplierCode,
                            'supplier_name' => $po->SupplierName,
                            'order_placer' => $po->orderPlacer,
                            'action_type' => 'create',
                            'subject_type' => 'App\\Models\\Orders\\PO',
                            'subject_id' => $po->PONumber,
                            'event' => 'created',
                            'po_data' => $data
                        ])
                        ->event('created')
                        ->log("Created Purchase Order #{$po->PONumber}");
                } catch (\Exception $e) {
                    Log::warning('PO creation activity logging failed: ' . $e->getMessage());
                }
            });


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

            $data = PO::with('POItems')->findOrFail($id);
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
            $found = PO::findOrFail($id);

            if ($found->POStatus == null) {
                // Store original data for activity logging
                $originalData = $found->toArray();
                
                $found->fill($data);
                if ($found->isDirty()) {
                    $found->EditedBy = $request->user()->name ?? $request->user()->email ?? 'Admin';
                    $found->DateUpdated = now();
                    
                    // Get only the fields that were actually changed (dirty fields)
                    $changes = $found->getDirty();
                    $oldValues = [];
                    $newValues = [];
                    
                    // Only include fields that were actually changed
                    foreach ($changes as $field => $newValue) {
                        // Skip system fields that shouldn't appear in Changes Made
                        if (!in_array($field, ['EditedBy', 'DateUpdated'])) {
                            $oldValues[$field] = $originalData[$field] ?? null;
                            $newValues[$field] = $newValue;
                        }
                    }
                    
                    // Save the model (this will trigger automatic activity logging)
                    $found->save();
                    
                    // Log additional manual activity with old/new values ONLY for changed fields
                    if (!empty($oldValues) && !empty($newValues)) {
                        try {
                            activity('purchase_order')
                                ->performedOn($found)
                                ->withProperties([
                                    'ip' => $request->ip(),
                                    'user_agent' => $request->userAgent(),
                                    'url' => $request->fullUrl(),
                                    'method' => $request->method(),
                                    'po_number' => $found->PONumber,
                                    'supplier_code' => $found->SupplierCode,
                                    'supplier_name' => $found->SupplierName,
                                    'order_placer' => $found->orderPlacer,
                                    'action_type' => 'update',
                                    'subject_type' => 'App\\Models\\Orders\\PO',
                                    'subject_id' => $found->PONumber,
                                    'event' => 'updated',
                                    'old' => $oldValues,         // Only changed fields
                                    'attributes' => $newValues   // Only changed fields
                                ])
                                ->event('updated')
                                ->log("Updated Purchase Order #{$found->PONumber}");
                        } catch (\Exception $e) {
                            // Log activity failed, but don't block the update
                            Log::warning('PO activity logging failed: ' . $e->getMessage());
                        }
                    }
                }

                foreach ($data['Items'] as $item) {
                    $found->POItems()->updateOrCreate(
                        ['StockCode' => $item['StockCode']], // Search condition
                        $item // Data to update or create
                    );
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

            $data = PO::find($id);

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

            $data = PO::find($poid);

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
                    ->performedOn($data)
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
        dd("HELLOW");
        $data = PO::with('POItems')->where('PONumber', $poid)->firstOrFail();
        $data->SupplierCode = trim($data->SupplierCode);
        $data->posupplier = $data->posupplier->toArray();
        $data->POItems = $data->POItems->toArray();
        // dd($data->toArray());

        return view('Pages.PurchaseOrder-PDF', ['po' => $data]); // Pass the user to the view


        // $pdf = PDF::loadView('Pages.PurchaseOrder-PDF', ['po' => $data])
        //     ->setPaper('letter')
        //     ->setOptions(['margin-top' => 0, 'margin-bottom' => 0, 'margin-left' => 0, 'margin-right' => 0]);

        // return $pdf->download('purchase-order-' . $poid . '.pdf');

        // return response()->json($data);

    }
}
