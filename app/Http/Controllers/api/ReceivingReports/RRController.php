<?php

namespace App\Http\Controllers\api\ReceivingReports;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Orders\PO;
use App\Models\AccountsPayable;
use App\Models\SupplierCredit;
use Illuminate\Http\Request;
use App\Services\InventoryManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                        // Simply load the product relationship without the complex whereIn subquery
                        $query->with('product');
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
                        // If product relationship fails due to data type mismatch, try to find manually
                        if (!$detail->product) {
                            try {
                                $product = Product::where('StockCode', $detail->SKU)
                                    ->orWhereRaw('CAST(StockCode AS VARCHAR) = ?', [$detail->SKU])
                                    ->select('StockCode', 'Description', 'StockUom', 'AlternateUom','OtherUom','ConvFactAltUom', 'ConvFactOthUom')
                                    ->first();
                                
                                if ($product) {
                                    $detail->setRelation('product', $product);
                                }
                            } catch (\Exception $e) {
                                // Log the issue but don't break the flow
                                Log::warning("Failed to load product for SKU: {$detail->SKU}", ['error' => $e->getMessage()]);
                            }
                        }
                        
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
                            // Simply load the product relationship without the complex whereIn subquery
                            $query->with('product');
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
                                // If product relationship fails due to data type mismatch, try to find manually
                                if (!$detail->product) {
                                    try {
                                        $product = Product::where('StockCode', $detail->SKU)
                                            ->orWhereRaw('CAST(StockCode AS VARCHAR) = ?', [$detail->SKU])
                                            ->select('StockCode', 'Description', 'StockUom', 'AlternateUom','OtherUom','ConvFactAltUom', 'ConvFactOthUom')
                                            ->first();
                                        
                                        if ($product) {
                                            $detail->setRelation('product', $product);
                                        }
                                    } catch (\Exception $e) {
                                        // Log the issue but don't break the flow
                                        Log::warning("Failed to load product for SKU: {$detail->SKU}", ['error' => $e->getMessage()]);
                                    }
                                }
                                
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

                // Log printing activity when data is available
                try {
                    if ($data) {
                        activity('receiving_report')
                            ->performedOn($data)
                            ->causedBy(auth()->user())
                            ->withProperties([
                                'ip' => request()->ip(),
                                'user_agent' => request()->userAgent(),
                                'url' => request()->fullUrl(),
                                'method' => request()->method(),
                                'rr_no' => $RRNum,
                                'status' => $data->Status ?? null,
                                'total' => $data->Total ?? null,
                            ])
                            ->event('printed')
                            ->log("Printed Receiving Report #{$RRNum}");
                    }
                } catch (\Throwable $e) {
                    // Non-blocking: ignore logging failures
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
            $InventoryManager = new InventoryManager();

            $rrNo = $request->data['rrNum'];
            $user = $request->data['user'];
            $details = $request->data['rrData']['rrdetails'];
            $rrHeaderDetails = $request->data;
            unset($rrHeaderDetails['rrData']['rrdetails'], $rrHeaderDetails['rrData']['poincluded']);

            // Fetch header first for logging/subject reference
            $header = ReceivingRHeader::where('RRNo', $rrNo)->with('poincluded')->first();

            $isPresent = false;
            if ($header) {
                $isPresent = ReceivingRHeader::where('RRNo', $rrNo)
                    ->update([
                        'status' => 2,  // Confirmed
                        'ApprovedBy' => $user,
                        'CheckedBy' => $user,
                        'DATEUPDATED' => now()->setTimezone('Asia/Manila')->format('Y-m-d H:i:s'),
                    ]);
            }

            if(!$isPresent || !$header){
                return response()->json([
                    'success' => false,
                    'message' => 'Receiving Report not found',
                ], 404);
            }

            // Get warehouse from related PO, fallback to 'M1' if not found
            $warehouse = 'M1'; // Default fallback
            if ($header->poincluded && $header->poincluded->warehouseCode) {
                $warehouse = $header->poincluded->warehouseCode;
            }

            foreach ($details as $detail) {
                $sku = $detail['SKU'];
                $detail['warehouse'] = $warehouse;
                $qty = $detail['Quantity'];
                $InventoryManager->InvWareHouseDirectionHandler($sku, $warehouse, $qty, "IN", null);
                $InventoryManager->InvMovement($rrHeaderDetails,  $detail, 'I', 'R');
            }

            // Log confirmation activity FIRST (to appear later in chronological order)
            try {
                activity('receiving_report')
                    ->performedOn($header)
                    ->causedBy($request->user())
                    ->withProperties([
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'rr_no' => $rrNo,
                        'items' => is_array($details) ? count($details) : null,
                    ])
                    ->event('confirmed')
                    ->log("Confirmed Receiving Report #{$rrNo}");
            } catch (\Throwable $e) {
                // Non-blocking: ignore logging failures
            }

            // Create Accounts Payable record after confirming RR (this will log AFTER RR confirmation)
            $apCreationResult = null;
            try {
                $apCreationResult = $this->createAccountsPayableFromRR($header, $details, $user);
                Log::info("Successfully created Accounts Payable for RR {$rrNo} by user {$user}", [
                    'ap_id' => $apCreationResult ? $apCreationResult->id : null
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to create Accounts Payable for RR {$rrNo}: " . $e->getMessage(), [
                    'exception' => $e,
                    'rr_no' => $rrNo,
                    'user' => $user,
                    'stack_trace' => $e->getTraceAsString()
                ]);
                // Don't fail the RR confirmation if AP creation fails, but include in response
                return response()->json([
                    'message' => 'Receiving Report confirmed successfully but failed to create Accounts Payable',
                    'success' => true,
                    'warning' => 'Accounts Payable creation failed: ' . $e->getMessage(),
                    'ap_created' => false
                ]);
            }

            return response()->json([
                'message' => 'Receiving Report confirmed successfully and Accounts Payable created',
                'success' => true,
                'ap_created' => $apCreationResult !== null,
                'ap_id' => $apCreationResult ? $apCreationResult->id : null
            ]);
        } catch (\Exception $e) {
            Log::error("Error confirming RR: " . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);  // HTTP 500 Internal Server Error
        }
    }

    /**
     * Create Accounts Payable record from confirmed RR
     */
    private function createAccountsPayableFromRR($rrHeader, $rrDetails, $user)
    {
        // Check if AP record already exists for this RR
        $existingAP = AccountsPayable::where('rr_number', $rrHeader->RRNo)->first();
        if ($existingAP) {
            Log::info("Accounts Payable record already exists for RR {$rrHeader->RRNo}");
            return $existingAP;
        }

        // Calculate total amount from RR details
        $totalAmount = 0;
        foreach ($rrDetails as $detail) {
            $totalAmount += floatval($detail['Gross'] ?? 0);
        }

        // Get supplier information
        $supplierCode = $rrHeader->poincluded->SupplierCode ?? null;
        $supplier = null;
        
        if ($supplierCode) {
            $supplier = Supplier::where('SupplierCode', trim($supplierCode))->first();
        }

        // Get terms from supplier or default to "30 Days"
        $terms = "30 Days";
        if ($supplier && !empty($supplier->TermsCode)) {
            $terms = $supplier->TermsCode;
        }

        // Check for available credit memos for this supplier
        $availableCreditMemo = 0;
        if ($supplierCode) {
            // Get total original credit memos
            $totalCreditMemos = AccountsPayable::where('supplier_code', trim($supplierCode))
                ->sum('CreditMemo') ?? 0;
            
            // Get total applied credit memos (from AUTO-CM- payment records)
            $appliedCreditMemos = \App\Models\Payment::whereHas('accountsPayable', function($query) use ($supplierCode) {
                $query->where('supplier_code', trim($supplierCode));
            })
            ->where('reference_number', 'LIKE', 'AUTO-CM-%')
            ->sum('payment_amount') ?? 0;
            
            // Available credit memo = Original credit memos - Applied credit memos
            $availableCreditMemo = $totalCreditMemos - $appliedCreditMemos;
            $availableCreditMemo = max(0, $availableCreditMemo); // Ensure non-negative
        }

        // Calculate automatic credit memo application
        $creditMemoApplied = 0;
        $finalAmount = $totalAmount;
        $initialStatus = 'Pending';
        $remarks = null;

        if ($availableCreditMemo > 0) {
            if ($availableCreditMemo >= $totalAmount) {
                // Credit memo covers the entire amount
                $creditMemoApplied = $totalAmount;
                $finalAmount = 0;
                $initialStatus = 'Paid';
                $remarks = "Automatically paid using credit memo (₱" . number_format($creditMemoApplied, 2) . ")";
            } else {
                // Credit memo covers partial amount
                $creditMemoApplied = $availableCreditMemo;
                $finalAmount = $totalAmount - $creditMemoApplied;
                $remarks = "Partial payment applied from credit memo (₱" . number_format($creditMemoApplied, 2) . ")";
            }
        }

        // Create the Accounts Payable record
        $accountsPayable = AccountsPayable::create([
            'date' => $rrHeader->RRDATE,
            'supplier_code' => trim($supplierCode),
            'supplier_name' => $supplier ? $supplier->SupplierName : 'Unknown Supplier',
            'rr_number' => $rrHeader->RRNo,
            'reference_number' => $rrHeader->Reference ?? $rrHeader->RRNo,
            'total_amount' => $totalAmount,
            'terms' => $terms,
            'status' => $initialStatus,
            'remarks' => $remarks,
            'process_by' => $user,
        ]);

        // Apply credit memo if available
        if ($creditMemoApplied > 0) {
            // Create automatic payment record for credit memo application
            $autoCreditPayment = \App\Models\Payment::create([
                'accounts_payable_id' => $accountsPayable->id,
                'payment_amount' => $creditMemoApplied,
                'payment_type' => 'cash',
                'payment_status' => $finalAmount <= 0 ? 'full' : 'partial',
                'payment_date' => now(),
                'reference_number' => 'AUTO-CM-' . $rrHeader->RRNo,
                'remarks' => 'Automatic credit memo application',
                'process_by' => $user
            ]);

            Log::info("Created automatic credit memo payment", [
                'payment_id' => $autoCreditPayment->id,
                'ap_id' => $accountsPayable->id,
                'amount' => $creditMemoApplied
            ]);

            // NOTE: We no longer deduct from original credit memo records to preserve them.
            // Credit memo applications are now tracked through Payment records with AUTO-CM- reference.
            // The available credit memo calculation will be updated to consider these payment applications.

            // Find source AP records that have available credit memos to track the application
            $availableCreditSources = AccountsPayable::where('supplier_code', trim($supplierCode))
                ->where('CreditMemo', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            $remainingCreditToApply = $creditMemoApplied;

            foreach ($availableCreditSources as $sourceAP) {
                if ($remainingCreditToApply <= 0) break;

                // Calculate how much credit has already been used from this source
                $usedCredit = \App\Models\CreditMemoApplication::where('source_ap_id', $sourceAP->id)->sum('credit_amount');
                $availableFromSource = $sourceAP->CreditMemo - $usedCredit;

                if ($availableFromSource > 0) {
                    $creditFromThisSource = min($availableFromSource, $remainingCreditToApply);

                    // Create CreditMemoApplication entry
                    try {
                        \App\Models\CreditMemoApplication::create([
                            'source_ap_id' => $sourceAP->id,
                            'target_ap_id' => $accountsPayable->id,
                            'credit_amount' => $creditFromThisSource,
                            'application_date' => now(),
                            'created_by' => $user,
                            'notes' => 'Automatic credit memo application from ' . $sourceAP->reference_number . ' to new invoice ' . $rrHeader->RRNo
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error creating CreditMemoApplication entry: ' . $e->getMessage());
                    }

                    $remainingCreditToApply -= $creditFromThisSource;
                }
            }

            // Create SupplierRunningBalance entry for auto credit memo application
            try {
                \App\Models\SupplierRunningBalance::addEntry(
                    trim($supplierCode),
                    now(),
                    $accountsPayable->id,
                    'credit_memo_applied',
                    -$creditMemoApplied, // Negative because it reduces the debt
                    'Automatic credit memo application of ₱' . number_format($creditMemoApplied, 2)
                );
            } catch (\Exception $e) {
                Log::error('Error creating SupplierRunningBalance entry for auto CM: ' . $e->getMessage());
            }

            Log::info("Applied credit memo to new AP record", [
                'ap_id' => $accountsPayable->id,
                'rr_no' => $rrHeader->RRNo,
                'original_amount' => $totalAmount,
                'credit_memo_applied' => $creditMemoApplied,
                'final_amount' => $finalAmount,
                'status' => $initialStatus
            ]);
        }

        // Create initial SupplierRunningBalance entry for the invoice
        try {
            \App\Models\SupplierRunningBalance::addEntry(
                trim($supplierCode),
                $rrHeader->RRDATE,
                $accountsPayable->id,
                'invoice',
                $totalAmount, // Positive because it increases the debt
                'Invoice created from RR#' . $rrHeader->RRNo . ' - ' . ($supplier ? $supplier->SupplierName : 'Unknown')
            );
        } catch (\Exception $e) {
            Log::error('Error creating SupplierRunningBalance entry for invoice: ' . $e->getMessage());
        }

        // Update supplier credit data after creating AP record
        try {
            if ($supplierCode) {
                SupplierCredit::updateSupplierCredit(trim($supplierCode));
                Log::info('Supplier credit updated after AP creation for supplier: ' . trim($supplierCode));
            }
        } catch (\Exception $e) {
            Log::error('Error updating supplier credit after AP creation: ' . $e->getMessage(), [
                'supplier_code' => $supplierCode,
                'ap_id' => $accountsPayable->id
            ]);
        }

        Log::info("Successfully created Accounts Payable record", [
            'ap_id' => $accountsPayable->id,
            'rr_no' => $rrHeader->RRNo,
            'total_amount' => $totalAmount,
            'supplier_code' => $supplierCode,
            'supplier_name' => $supplier ? $supplier->SupplierName : 'Unknown Supplier'
        ]);

        return $accountsPayable;
    }

    /**
     * Manually create Accounts Payable records for confirmed RRs that don't have them yet
     * This is useful for backfilling existing data
     */
    public function createMissingAccountsPayable(Request $request)
    {
        try {
            // Get all confirmed RRs that don't have corresponding Accounts Payable records
            $confirmedRRs = ReceivingRHeader::where('Status', 2)
                ->with(['poincluded', 'rrdetails'])
                ->whereNotIn('RRNo', function($query) {
                    $query->select('rr_number')
                          ->from('tblAccountsPayable')
                          ->whereNotNull('rr_number');
                })
                ->get();

            $created = 0;
            $errors = [];

            foreach ($confirmedRRs as $rr) {
                try {
                    $this->createAccountsPayableFromRR($rr, $rr->rrdetails->toArray(), 'System');
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "RR {$rr->RRNo}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Created {$created} Accounts Payable records",
                'created_count' => $created,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating missing Accounts Payable records: ' . $e->getMessage(),
            ], 500);
        }
    }



}
