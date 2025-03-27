<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\helpers\PDFParserController;
use Illuminate\Queue\Console\RetryBatchCommand;
use PDO;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ShellPOPdfParser
{
    protected static $packageUnits = [
        "Box",
        "Carton",
        "Crate",
        "Pallet",
        "Skid",
        "Drum",
        "Barrel",
        "Bag",
        "Roll",
        "Parcel",
        "Bundle",
        "Tote",
        "Crate box",
        "Shrink wrap",
        "Fibreboard box",
        "Plastic container",
        "Wooden crate",
        "Canister",
        "Vial",
        "Cask",
        "Boxed set",
        "Bale",
        "Slip sheet",
        "Trunk",
        "Unit",
        "Piece",
        "Satchel",
        "Container",
        "Pallet box",
        "Roll cage",
        "Bag-in-box",
        "Tub",
        "Sack",
        "Carton box",
        "Wooden box",
        "Bin",
        "Tubular container",
        "IBC (Intermediate Bulk Container)",
        "Tanker",
        "Keg",
        "Cylinder",
        "Jerrican",
        "Cage",
        "Crate pack",
        "Spool",
        "Blister pack",
        "Clip",
        "Plank",
        "Flexible container",
        "Loose box",
        "Envelope",
        "Pack",
        "Strip",
        "Roll-off container",
        "Conveyor pack",
        "Mailing bag",
        "Gantry",
        "Multipack",
        "Pouch",
        "Bale wrap",
        "Roll film",
        "Box shrink wrap"
    ];

    public static function POHeader(Request $request)
    {
        $parseResult = PDFParserController::parsePDF($request);

        if ($parseResult['status']) {
            $parsedText = str_replace("\u{00A0}", " ", $parseResult['extracted_text']);

            preg_match('/Order #(.*?)1\. Includes/s', $parsedText, $headerMatch);

            $orderNumber = $PONumber = $PODate = $POAccount = $productType = $orderPlacer = $orderPlacerEmail = $deliveryAddress = $deliveryMethod = $totalNetVol = $totalNetWeight = $totalGrossWeight = $subTotal = $totalDiscount = $totalTax = $totalCost = null;

            if ($headerMatch[0]) {
                $splitByLine = explode("\n", $headerMatch[0] ?? '');

                $orderNumber = $splitByLine[0] ? str_replace('Order #', '', $splitByLine[0]) : '';
                $PONumber = $splitByLine[3] ?? '';
                $PODate = $splitByLine[5] ?? '';

                $PODateFormatted = Carbon::createFromFormat('d.m.Y', $PODate)->format('Y-m-d');

                $POAccount = $splitByLine[7] ?? '';
                $productType = $splitByLine[9] ?? '';
                $orderPlacer = $splitByLine[11] ?? '';
                $orderPlacerEmail = $splitByLine[12] ?? '';

                preg_match('/Delivery address([\s\S]*?)Delivery method/', $headerMatch[0], $deliveryAddressMatch);

                $deliveryAddress = $deliveryAddressMatch[1] ?? '';

                // Transform deliveryAddress
                $deliveryAddress = $deliveryAddress ?? preg_replace([
                    "/\n/",                  // Replace newlines with spaces (even if no space around it)
                    "/\s+,/",                // Replace spaces before a comma with just a comma
                    "/\s+(?=\.)/",           // Remove spaces before a period
                    "/\s*-\s*/"              // Remove spaces around a hyphen
                ], [
                    " ",                     // Replacement for newlines
                    ",",                     // Replacement for spaces before a comma
                    "",                      // Replacement for spaces before a period
                    "-"                      // Replacement for spaces around a hyphen
                ], $deliveryAddress);

                preg_match('/Delivery method([\s\S]*?)Delivery instructions/', $headerMatch[0], $deliveryAddressMatch);

                // Extract and clean the delivery method if it exists
                $deliveryMethod = isset($deliveryAddressMatch[1])
                    ? preg_replace("/\n/", '', $deliveryAddressMatch[1])
                    : '';

                preg_match('/You’ll receive items on multiple delivery dates([\s\S]*?)Products/', $headerMatch[0], $headerDeliveryDatesMatch);

                $headerDeliveryDates = explode("\n", $headerDeliveryDatesMatch[1] ?? '');
                $headerDeliveryDates = array_values(array_filter($headerDeliveryDates));

                preg_match_all('/^\d+\s+products$/um', $parsedText, $headerDeliveryProductsMatch);

                // Replace \u00a0 with a normal space
                // $headerDeliveryProducts = array_map(function ($item) {
                //     return str_replace("\u{00A0}", " ", $item);
                // }, $headerDeliveryProductsMatch[0]);

                $headerDeliveryProducts = $headerDeliveryProductsMatch ? $headerDeliveryProductsMatch[0] : "";

                $headerDeliverySchedule = [];

                if (count($headerDeliveryDates) == count($headerDeliveryProducts)) {

                    foreach ($headerDeliveryDates as $index => $DeliveryDate) {
                        $headerDeliverySchedule[] = [
                            'DeliveryDates' => $headerDeliveryDates[$index],
                            'DeliveryProducts' => $headerDeliveryProducts[$index],
                        ];
                    }
                }

                preg_match('/Products([\s\S]*?)1. Includes/', $headerMatch[0], $orderSummaryMatch);

                $orderSummary = explode("\n", $orderSummaryMatch[0] ?? '');

                //return $orderSummary;

                if ($orderSummary) {
                    $parenthesesPattern = '/\((.*?)\)/';

                    $totalNetVol = explode("\t", $orderSummary[1]);
                    preg_match($parenthesesPattern, $totalNetVol[0], $volMeasurementMatches);
                    $volMeasurement = $volMeasurementMatches[1] ?? '';
                    $totalNetVol = end($totalNetVol);
                    $totalNetVol = str_replace(",", "", $totalNetVol);

                    $volumeUOM = $volMeasurement;

                    $totalNetWeight = explode("\t", $orderSummary[2]);
                    preg_match($parenthesesPattern, $totalNetWeight[0], $weightMeasurementMatches);
                    // $weightMeasurement = $weightMeasurementMatches[1] ?? '';
                    $totalNetWeight = end($totalNetWeight);
                    $totalNetWeight = str_replace(",", "", $totalNetWeight);

                    preg_match($parenthesesPattern, $orderSummary[4], $grossWeightMeasurementMatches);
                    $grossWeightMeasurement = $grossWeightMeasurementMatches[1] ?? '';
                    $totalGrossWeight = $orderSummary[5];
                    $totalGrossWeight = str_replace(",", "", $totalGrossWeight);

                    $weightUOM = $grossWeightMeasurement;

                    //--------------------------------------------------------------------------------------//
                    // $itemVolume = trim(preg_replace('/[a-zA-Z,]+/', '', $itemQuantityDecription[0]));
                    // $itemVolumeUOM = trim(preg_replace('/\d+(\.\d+)?/', '', $itemQuantityDecription[0]));

                    // $itemWeight = trim(preg_replace('/[a-zA-Z,]+/', '', $itemQuantityDecription[1]));
                    // $itemWeightUOM = trim(preg_replace('/\d+(\.\d+)?/', '', $itemQuantityDecription[1]));

                    // $itemDeliveredQuantity = $explodeByLines[$packageUnitIndex + 2];
                    // $itemTotalPrice = $explodeByLines[$packageUnitIndex + 3];
                    // $usedCurrency = trim(preg_replace('/\d+(\.\d+)?/', '', $itemTotalPrice));

                    // $pricePerUnit = str_replace('\/', '/', $explodeByLines[$packageUnitIndex + 5]);
                    //--------------------------------------------------------------------------------------//

                    $subTotal = explode("\t", $orderSummary[6]);
                    $subTotal = trim(preg_replace('/[a-zA-Z,]+/', '', end($subTotal)));

                    $totalDiscount = explode("\t", $orderSummary[7]);
                    $totalDiscount = trim(preg_replace('/[a-zA-Z,]+/', '', end($totalDiscount)));

                    $totalTax = explode("\t", $orderSummary[8]);
                    $totalTax = trim(preg_replace('/[a-zA-Z,]+/', '', end($totalTax)));

                    $totalCost = explode("\t", $orderSummary[9]);
                    $usedCurrency = trim(preg_replace('/\d{1,3}(,\d3})*(\.\d+)?|,/', '', end($totalCost)));
                    $totalCost = trim(preg_replace('/[a-zA-Z,]+/', '', end($totalCost)));
                }
            }

            // return self::POItemsDeliveries($parsedText, $PONumber);

            return [
                // 'pdfType'=> 'po',
                'OrderNumber' => $orderNumber,
                'PONumber' => $PONumber,
                'PODate' => $PODateFormatted,
                'POAccount' =>  $POAccount,
                'productType' => $productType,
                'orderPlacer' => $orderPlacer,
                'orderPlacerEmail' => $orderPlacerEmail,
                'deliveryAddress' => preg_replace(["/\n/", "/\s+,/"], ['', ','], trim($deliveryAddress)),
                'deliveryMethod' => $deliveryMethod,
                'headerDeliverySchedule' => $headerDeliverySchedule,
                'totalNetVol' => $totalNetVol,
                'volumeUOM' => $volumeUOM,
                'totalNetWeight' => $totalNetWeight,
                'totalGrossWeight' => $totalGrossWeight,
                'weightUOM' => $weightUOM,
                'subTotal' => $subTotal,
                'totalDiscount' => $totalDiscount,
                'totalTax' => $totalTax,
                'totalCost' => $totalCost,
                'usedCurrency' => $usedCurrency,
                'Items' => self::POItems($parsedText, $PONumber),
                'Deliveries' => self::POItemsDeliveries($parsedText, $PONumber)
            ];
        }
    }

    public static function POItems($pdfString, $PONo)
    {

        // convert all packageunit entries into lowercase
        $packageUnits = array_map(fn($item) => strtolower($item), self::$packageUnits);
        $itemsDetails = [];

        // Extract the items string from the pdf string using the status pattern
        preg_match('/Status(.*?)Your Deliveries/s', $pdfString, $itemsString);

        //echo  $itemsString[1];

        // $matches = [];

        // Extract sections using the product number pattern and date pattern
        preg_match_all('/^\d{1,2}+\s+\d+\s[\s\S]*?(?:\d{1,2}\.\d{1,2}\.\d{4})$/m', $itemsString[1], $matches);

        $sections = $matches[0]; // Array of matched sections

        // Output or process each section
        foreach ($sections as $section) {

            $explodeByLines = explode("\n", $section);
            $packageUnitIndex = null;

            foreach ($explodeByLines as $index => $line) {

                if (preg_match('/^\d/', $line)) {
                    $getPackageUnit = preg_replace('/\d+(\.\d+)?/', '', $line);

                    //remove the plural from like example "catoons" into "cartton"
                    $getPackageUnit = trim(Str::endsWith(strtolower($getPackageUnit), 's') ? substr($getPackageUnit, 0, -1) : $getPackageUnit);

                    if (in_array(strtolower($getPackageUnit), $packageUnits)) {
                        $packageUnitIndex = $index;
                        // echo $index . "\n";
                    }
                }
            }

            $indexAndMatCode = preg_split('/\s+/', $explodeByLines[0]);

            $itemIndex = $indexAndMatCode[0];
            $itemMatCode =  $indexAndMatCode[1];
            $itemDecription = '';

            for ($i = 1; $i < $packageUnitIndex; $i++) {
                $itemDecription .= ' ' . str_replace("\u{00a0}", "", $explodeByLines[$i]);
            }

            $itemQuantity = $explodeByLines[$packageUnitIndex];
            //get the uom or package unit from items
            $itemUOM = trim(preg_replace('/\d+(\.\d+)?/', '', $itemQuantity));

            //remove the uom or package unit from items
            $itemQuantity = trim(preg_replace('/[a-zA-Z,]+/', '', $itemQuantity));

            $itemQuantityDecription = null;

            if (preg_match('/^\d{1,3}(?:,\d{3})*(\.\d+)?$/', $explodeByLines[$packageUnitIndex + 2])) {
                $itemQuantityDecription = $explodeByLines[$packageUnitIndex + 1];
            } else {
                $itemQuantityDecription = $explodeByLines[$packageUnitIndex + 1] . $explodeByLines[$packageUnitIndex + 2];
                $packageUnitIndex++;
            }

            //--------------------------------------------------------------------------------//
            //clean and extract currency and amount

            $itemQuantityDecription = explode(";", $itemQuantityDecription);

            $itemVolume = trim(preg_replace('/[a-zA-Z,]+/', '', $itemQuantityDecription[0]));
            $itemVolumeUOM = trim(preg_replace('/\d+(\.\d+)?/', '', $itemQuantityDecription[0]));

            $itemWeight = trim(preg_replace('/[a-zA-Z,]+/', '', $itemQuantityDecription[1]));
            $itemWeightUOM = trim(preg_replace('/\d+(\.\d+)?/', '', $itemQuantityDecription[1]));

            $itemDeliveredQuantity = $explodeByLines[$packageUnitIndex + 2];
            $itemTotalPrice = $explodeByLines[$packageUnitIndex + 3];
            $usedCurrency = trim(preg_replace('/\d{1,3}(,\d{3})*(\.\d+)?/', '', $itemTotalPrice));

            $pricePerUnit = str_replace('\/', '/', $explodeByLines[$packageUnitIndex + 5]);
            //--------------------------------------------------------------------------------//

            // Push the extracted details into the itemsDetails array
            $itemsDetails[] = [
                'PRD_INDEX' => $itemIndex,
                'PONumber' => $PONo,
                'MaterialCode' => $itemMatCode,
                'Decription' =>  trim($itemDecription),
                'Quantity' => $itemQuantity,
                'UOM' => $itemUOM,
                'DeliveredQuantity' => $itemDeliveredQuantity,
                'ItemVolume' => $itemVolume,
                'ItemVolumeUOM' => trim(preg_replace('/,/', '', $itemVolumeUOM)),
                'ItemWeight' => $itemWeight,
                'ItemWeightUOM' => trim(preg_replace('/,/', '', $itemWeightUOM)),
                'TotalPrice' => trim(preg_replace('/[a-zA-Z,]+/', '', $itemTotalPrice)),
                'UsedCurrency' => trim(preg_replace('/,/', '', $usedCurrency)),
                'PricePerUnit' =>   $pricePerUnit,
            ];

            //echo $section . "\n\n";
        }

        return $itemsDetails;

        // Return the extracted items string
        //return $itemsString[1];  // Return the matched items string

    }

    public static function POItemsDeliveries($pdfString, $PONo)
    {
        // Extract the items string from the pdf string using the status pattern
        preg_match('/Your Deliveries(.*?)Delivery By/s', $pdfString, $itemsString);

        //return $pdfString;

        // preg_match_all('/^\s*\d{1,2}[\s\S]*?\d{1,2}\s*$/m', $itemsString[0], $matches);

        preg_match_all('/^\d{1,2}[\s\S]*?(?:\d{1,2}\.\d{1,2}\.\d{4})$/m', $itemsString[0], $matches);


        //-----------------------------------------------------------------------------------------------------//
        //re-group by delivery date pattern is if (prd_index == 1) = new group

        $groupByDeliveryGroup = [];
        $groupIndexByDelivery = 0;

        foreach ($matches[0] as $match) {
            $splitByLine = explode("\n", $match);
            //return $splitByLine;

            $groupIndexByDelivery = $splitByLine[0] == "1" ? $groupIndexByDelivery + 1 :  $groupIndexByDelivery;
            $groupByDeliveryGroup[$groupIndexByDelivery][] = $match;
        }

        //-----------------------------------------------------------------------------------------------------//


        //-----------------------------------------------------------------------------------------------------//
        //extract datas from regrouped delliveries

        $itemsDeliveryDetails = [];
        $prd_index = $itemMatCode = $itemDescription  = $itemQuantity = $quantityUOM = $itemVolume = $itemVolumeUOM = $itemWeight = $itemWeightUOM = $shippingDate = null;
        //return $groupByDeliveryGroup;

        foreach ($groupByDeliveryGroup as $index => $deliveries) {
            $deliveryDetails = [];

            foreach ($deliveries as $delivery) {
                // echo $delivery . "\n\n";
                $splitDeliveryDetails = explode("\n", $delivery);

                $prd_index = $splitDeliveryDetails[0];
                $itemDescription = $splitDeliveryDetails[1];
                $itemMatCode = trim(preg_replace('/[a-zA-Z,]+/', '', $splitDeliveryDetails[2]));

                $itemQuantityAndUOM = explode(" ", $splitDeliveryDetails[3]);
                $itemQuantity = trim($itemQuantityAndUOM[0]);
                $quantityUOM = trim($itemQuantityAndUOM[1]);

                $itemVolumeAndWeight = explode(";", $splitDeliveryDetails[4]);

                $itemVolumeQuantityAndUOM = explode(" ", $itemVolumeAndWeight[0]);
                $itemVolume = trim($itemVolumeQuantityAndUOM[0]);
                $itemVolumeUOM = trim(preg_replace('/[\(\)]/', '', $itemVolumeQuantityAndUOM[1]));

                $itemWeightQuantityAndUOM = explode(" ", $itemVolumeAndWeight[1]);
                $itemWeight = trim($itemWeightQuantityAndUOM[0]);
                $itemWeightUOM = trim(preg_replace('/[\(\)]/', '', $itemWeightQuantityAndUOM[1]));

                $shippingDate = $splitDeliveryDetails[5];
                $shippingDateFormatted = Carbon::createFromFormat('d.m.Y', $shippingDate)->format('Y-m-d');

                $deliveryDetails[] = [
                    'PRD_INDEX' => $prd_index,
                    'PONumber' => $PONo,
                    'MaterialCode' => str_replace(":", "", $itemMatCode),
                    'Decription' =>  trim($itemDescription),
                    'Quantity' => $itemQuantity,
                    'UOM' => $quantityUOM,
                    'ItemVolume' => str_replace(",", "", $itemVolume),
                    'ItemVolumeUOM' => trim($itemVolumeUOM),
                    'ItemWeight' => str_replace(",", "", $itemWeight),
                    'ItemWeightUOM' => trim($itemWeightUOM),
                    'ShippingDate' => $shippingDateFormatted,
                ];
            }

            $itemsDeliveryDetails[] = $deliveryDetails;
        }

        //return  $itemsDeliveryDetails;

        preg_match_all('/^Delivery By:.*$/m', $pdfString, $deliveryDateAndNumber);

        //return $deliveryDateAndNumber;

        // echo count($deliveryDateAndNumber[0]) . ' - ' . count($itemsDeliveryDetails);

        $combineDeliveryData = [];

        if (count($deliveryDateAndNumber[0]) == count($itemsDeliveryDetails)) {

            //return $deliveryDateAndNumber;

            foreach ($deliveryDateAndNumber[0] as $index => $itemsDelivery) {

                //echo $itemsDeliver;

                $splitDeliveryDateAndNumber = explode("\t", $itemsDelivery);

                $itemDeliveryDateLine = explode(":", $splitDeliveryDateAndNumber[0]);
                $itemDeliveryNumberLine = explode(":", $splitDeliveryDateAndNumber[1]);

                $itemDeliveryDate = trim(end($itemDeliveryDateLine));
                $itemDeliveryNumber = trim(end($itemDeliveryNumberLine));

                // $combineDeliveryData[] = [
                //     'DeliveryDate' => $itemDeliveryDate,
                //     'DeliveryNumber' => $itemDeliveryNumber,
                //     'Items' => $itemsDeliveryDetails[$index]
                // ];

                foreach ($itemsDeliveryDetails[$index] as $delivery) {
                    $delivery['DeliveryDate'] = Carbon::createFromFormat('d.m.Y', $itemDeliveryDate)->format('Y-m-d');;
                    $delivery['DeliveryNumber'] = $itemDeliveryNumber;

                    // $shippingDateFormatted = Carbon::createFromFormat('d.m.Y', $shippingDate)->format('Y-m-d');

                    $combineDeliveryData[] = $delivery;
                }
            }
        }

        return $combineDeliveryData;





        //-----------------------------------------------------------------------------------------------------//



        // $test = explode("\n", $groupByDeliveryGroup[0]);


        // return $test;

        //return  ;
    }
}
