<?php

namespace App\Observers;

use App\Models\Orders\POItems;
use App\Services\ProductCalculator;

use App\Models\Supplier;
use App\Models\ProductPrices;

class POItemsObserver
{
    protected $productCalculator;

    public function __construct(ProductCalculator $productCalculator)
    {
        $this->productCalculator = $productCalculator;
    }

    public function creating(POItems $poItem)
    {
        $poItem = $this->validateAndCheckItemPrice($poItem);
    }

    public function updating(POItems $poItem)
    {
        $poItem = $this->validateAndCheckItemPrice($poItem);
    }

    public function updated(POItems $poItem)
    {
        $poItem->POHeader->updateTotalCost();

        try {
            $changes = $poItem->getChanges();
            $oldValues = [];
            $newValues = [];

            foreach ($changes as $field => $newValue) {
                if (!in_array($field, ['HeaderParentId'])) {
                    $oldValues[$field] = $poItem->getOriginal($field);
                    $newValues[$field] = $newValue;
                }
            }

            if (!empty($newValues)) {
                activity('purchase_order')
                    ->withProperties([
                        'po_number' => $poItem->PONumber,
                        'subject_type' => 'App\\Models\\Orders\\PO',
                        'subject_id' => $poItem->PONumber,
                        'event' => 'updated',
                        'stock_code' => $poItem->StockCode,
                        'old' => $oldValues,
                        'attributes' => $newValues
                    ])
                    ->event('updated')
                    ->log("Updated item {$poItem->StockCode} on Purchase Order #{$poItem->PONumber}");
            }
        } catch (\Exception $e) {
            // Silent fail; logging should not break business flow
        }
    }

    public function created(POItems $poItem)
    {
        $poItem->POHeader->updateTotalCost();

        try {
            if (request()->attributes->get('collect_po_item_logs')) {
                return;
            }
            $now = \Carbon\Carbon::now();
            $causerId = optional(auth()->user())->id;

            $payload = [
                'StockCode' => $poItem->StockCode,
                'Decription' => $poItem->Decription,
                'TotalPrice' => $poItem->TotalPrice,
            ];

            $existing = \Spatie\Activitylog\Models\Activity::query()
                ->where('log_name', 'purchase_order')
                ->whereIn('event', ['items_added', 'item_added'])
                ->where('properties->po_number', $poItem->PONumber)
                ->orderByDesc('id')
                ->first();

            $shouldAggregate = false;
            if ($existing) {
                $props = (array) $existing->properties;
                if (($props['po_number'] ?? null) === $poItem->PONumber) {
                    $ageSeconds = $now->diffInSeconds(\Carbon\Carbon::parse($existing->created_at));
                    $shouldAggregate = $ageSeconds <= 120;
                }
            }

            if ($existing && $shouldAggregate) {
                $props = (array) $existing->properties;
                $items = [];
                if ($existing->event === 'items_added') {
                    $items = isset($props['items']) && is_array($props['items']) ? $props['items'] : [];
                } elseif ($existing->event === 'item_added') {
                    $single = [];
                    if (isset($props['attributes']) && is_array($props['attributes'])) {
                        $single = $props['attributes'];
                    } else {
                        // fallback compose from known properties
                        $single = [
                            'StockCode' => $props['stock_code'] ?? null,
                            'Decription' => $props['Decription'] ?? null,
                            'TotalPrice' => $props['TotalPrice'] ?? null,
                        ];
                    }
                    if (!empty($single)) {
                        $items[] = $single;
                    }
                }
                $items[] = $payload;
                $itemsTotal = 0;
                foreach ($items as $it) {
                    $itemsTotal += floatval($it['TotalPrice'] ?? 0);
                }
                $props['items'] = $items;
                $props['items_total'] = $itemsTotal;
                unset($props['attributes']);
                $existing->properties = $props;
                $existing->event = 'items_added';
                $existing->description = "Added items to Purchase Order #{$poItem->PONumber}";
                $existing->save();
            } else {
                activity('purchase_order')
                    ->withProperties([
                        'po_number' => $poItem->PONumber,
                        'subject_type' => 'App\\Models\\Orders\\PO',
                        'subject_id' => $poItem->PONumber,
                        'event' => 'items_added',
                        'items' => [ $payload ],
                        'items_total' => floatval($poItem->TotalPrice),
                    ])
                    ->event('items_added')
                    ->log("Added items to Purchase Order #{$poItem->PONumber}");
            }
        } catch (\Exception $e) {
            // Silent fail
        }
    }

    public function deleted(POItems $poItem)
    {
        $poItem->POHeader->updateTotalCost();

        try {
            if (request()->attributes->get('collect_po_item_logs')) {
                return;
            }
            activity('purchase_order')
                ->withProperties([
                    'po_number' => $poItem->PONumber,
                    'subject_type' => 'App\\Models\\Orders\\PO',
                    'subject_id' => $poItem->PONumber,
                    'event' => 'deleted',
                    'stock_code' => $poItem->StockCode,
                    'deleted_data' => [
                        'PRD_INDEX' => $poItem->PRD_INDEX,
                        'StockCode' => $poItem->StockCode,
                        'Quantity' => $poItem->Quantity,
                        'UOM' => $poItem->UOM,
                        'PricePerUnit' => $poItem->PricePerUnit,
                        'TotalPrice' => $poItem->TotalPrice
                    ]
                ])
                ->event('deleted')
                ->log("Removed item {$poItem->StockCode} from Purchase Order #{$poItem->PONumber}");
        } catch (\Exception $e) {
            
        }
    }

    private function validateAndCheckItemPrice(POItems $poItem)
    {
        $convertionResult = $this->productCalculator->getTotalQtyInPCS($poItem->StockCode, $poItem->Quantity, $poItem->UOM);

        if ($convertionResult['success']) {
            $poItem->TotalQtyInPCS = $convertionResult['result'];
        }

        $supplier = Supplier::where('SupplierCode', trim($poItem->POHeader->SupplierCode))->firstOrFail();

        $getProductPrice = ProductPrices::where('STOCKCODE', $poItem->StockCode)->where('PRICECODE', $supplier->PriceCode)->firstOrFail();

        $poItem->PricePerUnit = $getProductPrice->UNITPRICE;
        $poItem->TotalPrice = $poItem->PricePerUnit * $poItem->TotalQtyInPCS;
        
        return $poItem;
    }
}
