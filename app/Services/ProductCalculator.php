<?php

namespace App\Services;

use App\Models\Product;
// use App\Models\ProductPrices;
use App\Models\Orders\POItems;

class ProductCalculator
{

    public function transformQuantitiesAndUnits($sku, $uoms)
    {

        try {

            $product = Product::where('StockCode', $sku)->firstOrFail();

            $ConvFactAltUom = $product->ConvFactAltUom;
            $ConvFactOthUom = $product->ConvFactOthUom;
            $totalInPieces = 0;

            $itemUoms = [$product->StockUom, $product->AlternateUom, $product->OtherUom];
            $keepAvailableUomOnly = array_intersect_key($uoms, array_flip($itemUoms));

            foreach ($keepAvailableUomOnly as $key => $uom) {

                if (strcasecmp($key, "PC") === 0) {
                    $totalInPieces = $totalInPieces + $uom;
                } else if (strcasecmp($key, "IB") === 0) {
                    $totalInPieces = $totalInPieces + ($ConvFactAltUom / $ConvFactOthUom) * $uom;
                } else if (strcasecmp($key, "CS") === 0) {
                    $totalInPieces = $totalInPieces + $uom * $ConvFactAltUom;
                }
            }


            $convertIntoLargestUom = $this->convertProductToLargesttUnit($itemUoms, $totalInPieces, $ConvFactAltUom, $ConvFactOthUom);
            $convertIntoLargestUom['QuantityInPieces'] = floor($totalInPieces);
            // dd($convertIntoLargestUom);

            return [
                'success' => true,
                'result' =>   $convertIntoLargestUom,
            ];
        } catch (\Exception $e) {

            return [
                'success' => false,
                'result' => $e->getMessage(),
            ];
        }
    }

    public function convertProductToLargesttUnit($ProductUoms, $totalQuantity, $ConvFactAltUom, $ConvFactOthUom)
    {

        if (in_array('CS', $ProductUoms)) {

            return [
                'uom' => 'CS',
                'convertedToLargestUnit' => $totalQuantity / $ConvFactAltUom
            ];
        } else if (in_array('IB', $ProductUoms)) {

            return [
                'uom' => 'IB',
                'convertedToLargestUnit' => $totalQuantity / ($ConvFactAltUom / $ConvFactOthUom)
            ];
        } else  if (in_array('PC', $ProductUoms)) {
            return [
                'uom' => 'PC',
                'convertedToLargestUnit' => $totalQuantity
            ];
        }
    }

    public function getTotalQtyInPCS($sku, $quantity, $uom)
    {

        try {
            $product = Product::where('StockCode', $sku)->first();

            $ConvFactAltUom = $product->ConvFactAltUom;
            $ConvFactOthUom = $product->ConvFactOthUom;

            $StockUom = $product->StockUom;
            $AlternateUom = $product->AlternateUom;
            $otherUOM = $product->OtherUom;

            $totalInPieces = 0;            


            if (strcasecmp($uom, "PC") === 0) {
                $totalInPieces = $quantity;
            } else if (strcasecmp($uom, "IB") === 0) {
                $totalInPieces = ($ConvFactAltUom / $ConvFactOthUom) * $quantity;
            } else if (strcasecmp($uom, "CS") === 0) {
                $totalInPieces = $quantity * $ConvFactAltUom;
            }

            return [
                'success' => true,
                'result' =>  floor($totalInPieces),
            ];
        } catch (\Exception $e) {

            return [
                'success' => false,
                'result' => $e->getMessage(),
            ];
        }
    }

    private function ItemPackingQuantity($sku, $quantity)
    {
        try {
            $product = Product::findOrFail($sku);

            $ConvFactAltUom = $product->ConvFactAltUom;
            $ConvFactOthUom = $product->ConvFactOthUom;
            $otherUOM = $product->OtherUom;
            $result = 0;

            if (strcasecmp($otherUOM, "IB") === 0) {

                $mod1 = $quantity % $ConvFactAltUom;
                $mod2 = $mod1 % ($ConvFactAltUom / $ConvFactOthUom);

                $result = floor($mod2);
            } else {

                $result = $quantity % $ConvFactOthUom;
            }

            return response()->json([
                'success' => true,
                'result' =>  $result,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function calculateDynamicCasePackUnit(string $sku, int $quantity)
    {
        try {
            if ($quantity > 0) {
                $product = Product::where('StockCode', $sku)->firstOrFail();

                $ConvFactAltUom = $product->ConvFactAltUom;
                $ConvFactOthUom = $product->ConvFactOthUom;
                $itemUoms = [$product->StockUom, $product->AlternateUom, $product->OtherUom];

                $result = [
                    "inCS" => 0,
                    "inIB" => 0,
                    "inPC" => 0,
                ];

                // Convert to largest possible UOM first
                if (in_array("CS", $itemUoms) && $quantity >= $ConvFactAltUom) {
                    $result["inCS"] = intdiv($quantity, $ConvFactAltUom);
                    $quantity %= $ConvFactAltUom;
                }

                if (in_array("IB", $itemUoms) && $quantity >= $ConvFactOthUom) {
                    $result["inIB"] = intdiv($quantity, $ConvFactOthUom);
                    $quantity %= $ConvFactOthUom;
                }

                // Remaining are just pieces
                $result["inPC"] = $quantity;

                return [
                    "success" => true,
                    "result" => $result,
                ];
            } else {
            }
        } catch (\Exception $e) {

            return [
                'success' => false,
                'result' => $e->getMessage(),
            ];
        }
    }

    public function originalDynamicConv(string $sku, int $quantity)
    {
        try {
            if ($quantity > 0) {
                $product = Product::where('StockCode', $sku)->firstOrFail();
                $originalQty = $quantity;
                $ConvFactAltUom = $product->ConvFactAltUom;
                $ConvFactOthUom = $product->ConvFactOthUom;
                $itemUoms = [$product->StockUom, $product->AlternateUom, $product->OtherUom];

                $result = [
                    "inCS" => 0,
                    "inIB" => 0,
                    "inPC" => 0,
                ];

                if (in_array("CS", $itemUoms)) {
                    $result["inCS"] = intdiv($originalQty, $ConvFactAltUom);
                    $quantity = $originalQty % $ConvFactAltUom;
                }

                if (in_array("IB", $itemUoms) && $product->OtherUom == 'IB') {
                    $remaining = $originalQty % $ConvFactAltUom;
                    $IBVal = $ConvFactAltUom / $ConvFactOthUom;
                    $result["inIB"] = intdiv($remaining, $IBVal);
                    $quantity = $originalQty % $ConvFactAltUom;
                }

                if (in_array("PC", $itemUoms)) {
                    if ($product->OtherUom == 'IB') {
                        $remaining1 = $originalQty % $ConvFactAltUom;
                        $IBVal = $ConvFactAltUom / $ConvFactOthUom;
                        $result["inPC"] = $remaining1 % $IBVal;
                    } else {
                        $result["inPC"] = $originalQty % $ConvFactOthUom;
                    }
                }

                return [
                    "success" => true,
                    "result" => $result,
                ];
            } else {
            }
        } catch (\Exception $e) {

            return [
                'success' => false,
                'result' => $e->getMessage(),
            ];
        }
    }

    public function calculateCasePackUnits(string $sku, int $quantity)
    {
        try {
            if ($quantity > 0) {
                $product = Product::where('StockCode', $sku)->firstOrFail();
                $totalInPieces = $quantity; //341
                $currPieces =  $totalInPieces;

                //EXPECTED OUTPUT: CS = 1  IB = 2  PC = 5
                $itemUoms = array_filter([$product->StockUom, $product->AlternateUom, $product->OtherUom]); // Remove empty values
                $uniqueUoms = array_unique($itemUoms);
                $units = [];
                $retVal = [
                    "inCS" => 0,
                    "inIB" => 0,
                    "inPC" => 0
                ];

                // Assigning Conversion Factors
                foreach ($uniqueUoms as $uom) {
                    if ($uom === $product->AlternateUom) {
                        $units[$uom] = $product->ConvFactAltUom; //PCS = 288
                    } elseif ($uom === $product->OtherUom) {
                        $units[$uom] = $product->ConvFactOthUom; //IB = 24
                    } elseif ($uom === $product->StockUom) {
                        $units[$product->StockUom] = 0;
                    }
                }

                // Sort the array by conversion factor in ascending order
                asort($units);


                foreach ($units as $key => $value) {
                    if ($key == "CS") {
                        if ($currPieces >= $units["PC"]) {
                            $CSValue = floor($currPieces / $units["PC"]);
                            $currPieces %= $units["PC"];
                            $retVal["inCS"] = $CSValue;
                        }
                    } elseif ($key == "IB") {
                        if ($currPieces >= $units["IB"]) {
                            $IBValue = floor($currPieces / $units["IB"]);
                            $currPieces %= $units["IB"];
                            $retVal["inIB"] = $IBValue;
                        }
                    }
                }

                // Remaining pieces are in PC
                $retVal["inPC"] = $currPieces;

                return [
                    'success' => true,
                    'uom' => $uniqueUoms,
                    'result' =>   $retVal,
                ];
            }
        } catch (\Exception $e) {

            return [
                'success' => false,
                'result' => $e->getMessage(),
            ];
        }
    }

    public function originalDynamicConvOptimized(Product $product, int $quantity)
    {
        try {
            if ($quantity > 0) {
                $originalQty = $quantity;
                $ConvFactAltUom = $product->ConvFactAltUom;
                $ConvFactOthUom = $product->ConvFactOthUom;
                $itemUoms = [$product->StockUom, $product->AlternateUom, $product->OtherUom];

                $result = [
                    "inCS" => 0,
                    "inIB" => 0,
                    "inPC" => 0,
                ];

                if (in_array("CS", $itemUoms)) {
                    $result["inCS"] = intdiv($originalQty, $ConvFactAltUom);
                    $quantity %= $ConvFactAltUom;
                }

                if (in_array("IB", $itemUoms) && $product->OtherUom == 'IB') {
                    $IBVal = intdiv($ConvFactAltUom, $ConvFactOthUom);
                    $result["inIB"] = intdiv($quantity, $IBVal);
                    $quantity %= $IBVal;
                }

                if (in_array("PC", $itemUoms)) {
                    $result["inPC"] = $quantity;
                }

                return [
                    "success" => true,
                    "result" => $result,
                    "uom" => array_unique($itemUoms),
                    "altUOM" => $ConvFactAltUom,
                    "othUOM" => $ConvFactOthUom,
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'result' => $e->getMessage(),
            ];
        }
    }
}
