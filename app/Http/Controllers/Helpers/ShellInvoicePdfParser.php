<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;
use App\Http\Controllers\helpers\PDFParserController;
use Carbon\Carbon;

class ShellInvoicePdfParser
{
    //

    public static function invoiceHeader(Request $request)
    {

        //return self::itemDetails($request);

        $parsedText = $request->extractedString;

        //return $parsedText;
        preg_match('/Sold([\s\S]*?)Page 1/', $parsedText, $headerMatch);

        $headerString = $headerMatch ? $headerMatch[1] : '';

        // echo  $headerString;

        preg_match('/Party([\s\S]*?)Shell/', $headerString, $soldToMatch);
        $soldTo = strtoupper(trim(str_replace(["\n", "\r"], ['', " "], $soldToMatch[1])));

        preg_match('/Total Amount\s+([\d,]+\.\d{2})/', $headerString, $totalAmountMatch);
        $totalAmount = $totalAmountMatch ? $totalAmountMatch[1] : '';

        preg_match('/Total VAT Amount\s+([\d,]+\.\d{2})/', $headerString, $totalVatMatch);
        $totalVat = $totalVatMatch ? $totalVatMatch[1] : '';

        preg_match('/Amount Due\s+([\d,]+\.\d{2})/', $headerString, $amountDueMatch);
        $amountDue = $amountDueMatch ? $amountDueMatch[1] : '';

        preg_match('/Invoice Number\s+(\d+)/', $headerString, $invoiceNumberMatch);
        $invoiceNumber = $invoiceNumberMatch ? $invoiceNumberMatch[1] : '';

        preg_match('/Account Number\s+(\d+)/', $headerString, $accountNumberMatch);
        $accountNumber = $accountNumberMatch ? $accountNumberMatch[1] : '';

        preg_match('/Due Date\s+(\b\d{2}\.\d{2}\.\d{4}\b)/', $headerString, $dueDateMatch);
        $dueDate = $dueDateMatch ? $dueDateMatch[1] : '';

        preg_match('/Invoice Date\s+(\b\d{2}\.\d{2}\.\d{4}\b)/', $headerString, $invoiceDateMatch);
        $invoiceDate = $invoiceDateMatch ? $invoiceDateMatch[1] : '';

        preg_match('/Invoice Date\s+(\b\d{2}\.\d{2}\.\d{4}\b)/', $headerString, $invoiceDateMatch);
        $invoiceDate = $invoiceDateMatch ? $invoiceDateMatch[1] : '';

        preg_match('/Payment Terms([\s\S]*?)Payment Method/', $headerString, $paymentTermsMatch);
        $paymentTerms = $paymentTermsMatch ? $paymentTermsMatch[1] : '';

        preg_match('/Payment Method\s+(.*)/', $headerString, $paymentMethodMatch);
        $paymentMethod = $paymentMethodMatch ? $paymentMethodMatch[1] : '';

        preg_match('/Delivery Method\s+(.*)/', $headerString, $deliveryMethodMatch);
        $deliveryMethod = $deliveryMethodMatch ? $deliveryMethodMatch[1] : '';

        preg_match('/Your TIN:\s+(\d+)/', $headerString, $yourTINMatch);
        $yourTIN = $yourTINMatch ? $yourTINMatch[1] : '';

        preg_match('/collected by\s+(.*)/', $headerString, $paymentCollectedByMatch);
        $paymentCollectedBy = $paymentCollectedByMatch ? $paymentCollectedByMatch[1] : '';

        preg_match('/(?<=Details)(.*)/s', $headerString, $bankDetailsMatch);
        $bankDetails = $bankDetailsMatch ? $bankDetailsMatch[1] : '';

        preg_match('/Ship to:([\s\S]*?)Plant/', $parsedText, $shippedToMatch);
        $shippedTo = $shippedToMatch ? $shippedToMatch[1] : '';

        preg_match('/Plant:\s+(.*)/', $parsedText, $plantMatch);
        $plant = $plantMatch ? $plantMatch[1] : '';

        preg_match('/PO Number:\s+(.*)/', $parsedText, $PONumberMatch);
        $PONumber = $PONumberMatch ? $PONumberMatch[1] : '';



        // preg_match('/+(\b\d{2}\.\d{2}\.\d{4}\b)/', $parsedText, $shippedToMatch);
        preg_match('/Ship to:\s([\s\S]*?)Plant/', $parsedText, $shippedToMatch);
        $shippedTo = $shippedToMatch ? $shippedToMatch[1] : '';

        //return $headerMatch[1];


        $dueDateFormatted = Carbon::createFromFormat('d.m.Y', $dueDate)->format('Y-m-d');
        $invoiceDateFormatted = Carbon::createFromFormat('d.m.Y', $invoiceDate)->format('Y-m-d');


        $return = [
            // 'pdfType' => 'invoice',
            'SoldTo' =>  $soldTo,
            'totalAmount' => str_replace(",", "", $totalAmount),
            'totalVat' => str_replace(",", "", $totalVat),
            'amountDue' => str_replace(",", "", $amountDue),
            'invoiceNumber' => $invoiceNumber,
            "accountNumber" => $accountNumber,
            'dueDate' => $dueDateFormatted,
            'invoiceDate' => $invoiceDateFormatted,
            'paymentTerms' => trim(str_replace(["\n", "\r"], "", $paymentTerms)),
            'paymentMethod' => str_replace(["\n", "\r"], "", $paymentMethod),
            'deliveryMethod' => str_replace(["\n", "\r"], "", $deliveryMethod),
            'yourTIN' => $yourTIN,
            'paymentCollectedBy' => str_replace(["\n", "\r"], "", $paymentCollectedBy),
            'bankDetails' => str_replace(["\r\n", " - "], "", $bankDetails),
            'shippedTo' => trim(str_replace("\r\n", "", $shippedTo)),
            'plant' => str_replace(["\n", "\r"], "", $plant),
            'PONumber' => str_replace(["\n", "\r"], "", $PONumber),
            'Items' => self::itemDetails($request, $invoiceNumber)

        ];


        return $return;
    }

    public static function itemDetails(Request $request, $invoiceNumber)
    {

        // $parseResult = PDFParserController::parsePDF($request);
        // // return $parseResult['extracted_text'];


        preg_match('/Ship to:([\s\S]*?)GENERAL TERMS AND CONDITIONS/', $request->extractedString, $itemStringMatch);
        $itemString = $itemStringMatch ? $itemStringMatch[1] : '';
        // return $itemString;
        //clean dashes and \r
        $itemString = preg_replace('/\r|-{3,}/', '', $itemString);
        //return $itemString;

        $patternDetails = '/PRICE\s+PHP[\s\S]*?\n{3,}[\s\S]*?\n{3,}/';
        preg_match_all($patternDetails, $itemString, $itemDetails);


        $items = [];

        foreach ($itemDetails[0] as $prd_index => $item) {
            $discountPerUnit = $totalDiscountPerUnit = $itemDescription = $netPricePerUnit = $totalNetPrice = $subUOM = null;

            $item = trim(preg_replace('/(?<=\d{2}\.\d{2}\.\d{4})\s+/', "\n", $item));

            //echo trim($item) . "\n\n";
            $splitItemDetails = explode("\n", $item);
            $amount = $splitItemDetails[2];
            $unitPrice = $splitItemDetails[3];
            $UOM = $splitItemDetails[4];
            $deliveryAndOrderDate = [];
            $poductDeliveryAndOrderNumber = [];
            $quantityAndTotalUOM = [];

            foreach ($splitItemDetails as $splitItem) {
                if (preg_match('/\d{2}\.\d{2}\.\d{4}/', $splitItem)) {
                    $deliveryAndOrderDate[] = $splitItem;
                } else if (preg_match('/\d{6,}/', $splitItem)) {
                    $poductDeliveryAndOrderNumber[] = $splitItem;
                } else if (preg_match('/\.\d{2}+-$/', $splitItem)) {
                    $totalDiscountPerUnit = $splitItem;
                } else if (preg_match('/\.\d{2}+-/', $splitItem)) {
                    $discountPerUnit = $splitItem;
                } else if (preg_match('/\.\d{3}$/', $splitItem)) {
                    $quantityAndTotalUOM[] = $splitItem;
                }
            }

            $orderDate = Carbon::createFromFormat('d.m.Y', $deliveryAndOrderDate[0])->format('Y-m-d');
            $deliveryDate = Carbon::createFromFormat('d.m.Y', $deliveryAndOrderDate[1])->format('Y-m-d');


            if (preg_match('/' . $poductDeliveryAndOrderNumber[0] . '(.*?)' . $poductDeliveryAndOrderNumber[1] . '/s', $item, $itemDescriptionMatch)) {
                // If a match is found, process the captured string
                $itemDescription = trim(str_replace("\n", "", $itemDescriptionMatch[1])); // Captured string is in index 1

                if (empty($itemDescription)) {
                    if (preg_match('/' . $poductDeliveryAndOrderNumber[1] . '(.*?)' . $poductDeliveryAndOrderNumber[2] . '/s', $item, $itemDescriptionMath2)) {
                        $itemDescription = trim(str_replace("\n", "", $itemDescriptionMath2[1]));
                    }
                }
            }

            $itemDescription = preg_replace("/\d{4}$/", "", $itemDescription);


            //loop last 4 lines to get the net price per unit and total net price
            $startIndex = max(count($splitItemDetails) - 4, 0);
            for ($i = $startIndex; $i < count($splitItemDetails); $i++) {
                if (preg_match('/\.\d{2}+\s+PHP/', $splitItemDetails[$i])) {
                    $netPricePerUnit = $splitItemDetails[$i];
                } else if (preg_match('/\.\d{2}$/', $splitItemDetails[$i])) {
                    $totalNetPrice = $splitItemDetails[$i];
                }
            }


            //remove all extracted string to get the remaining details 
            $removeExtractedDataFromString = [$amount, $unitPrice, $UOM, $discountPerUnit, $totalDiscountPerUnit, $netPricePerUnit, $totalNetPrice, "PHP", "PRICE", "Cust Prod Disc -Unit", "Net Price (excl. VAT)"];

            $itemDescKeywords = explode(" ", $itemDescription);
            $removeExtractedDataFromString = array_merge($removeExtractedDataFromString,  $itemDescKeywords, $quantityAndTotalUOM, $deliveryAndOrderDate, $poductDeliveryAndOrderNumber);

            $item = str_replace($removeExtractedDataFromString, "", $item);


            //because it can't be predicted or cant have pattern because it will be placed randomly
            //the only option to extract subUOM is to delete all extracted data from string            
            $item = preg_replace('/\d{4}|\s{2,}/', "", $item);
            $subUOM =  $item;

            //clean detials that have per unit indicator or (--> 100.00 PHP / 1L <-- ) sample
            $removeAfterPHP = '/PHP.*|-.*|,/';

            $unitPrice = preg_replace($removeAfterPHP, '', $unitPrice);
            $discountPerUnit = preg_replace($removeAfterPHP, '', $discountPerUnit);
            $totalDiscountPerUnit = preg_replace($removeAfterPHP, '', $totalDiscountPerUnit);
            $netPricePerUnit = trim(preg_replace($removeAfterPHP, '', $netPricePerUnit));

            $amount = trim(preg_replace($removeAfterPHP, '', $amount));
            $quantityAndTotalUOM[0] = trim(preg_replace($removeAfterPHP, '', $quantityAndTotalUOM[0]));
            $quantityAndTotalUOM[1] = trim(preg_replace($removeAfterPHP, '', $quantityAndTotalUOM[1]));
            $totalNetPrice = trim(preg_replace($removeAfterPHP, '', $totalNetPrice));


            //Note all prices in this array is Excl. VAT
            $items[] = [
                'PRD_INDEX' => $prd_index + 1,
                'totalPrice' => $amount,
                'pricePerUnit' => trim($unitPrice),
                'UOM' => $UOM,
                'subUOM' => $subUOM,
                'quantity' => $quantityAndTotalUOM[0],
                'totalQuantityInUOM' => $quantityAndTotalUOM[1],
                'orderDate' => trim($orderDate),
                'deliveryDate' => trim($deliveryDate),
                'productCode' => $poductDeliveryAndOrderNumber[0],
                'deliveryNumber' => $poductDeliveryAndOrderNumber[1],
                'orderNumber' => $poductDeliveryAndOrderNumber[2],
                'discountPerUnit' =>  $discountPerUnit,
                'totalDiscountPerUnit' => $totalDiscountPerUnit,
                'netPricePerUnit' => $netPricePerUnit,
                'itemDescription' =>  $itemDescription,
                'totalNetPrice' =>  $totalNetPrice,
                'invoiceNumber' => $invoiceNumber

            ];
        }


        //reconstruct data base on extracted text it interchange the position
        //if first or last of delivery number and productCode

        if (count($items) > 0) {
            $temp = $items[0]['productCode'];
            $items[0]['productCode'] = $items[0]['deliveryNumber'];
            $items[0]['deliveryNumber'] = $temp;

            $temp2 = $items[count($items) - 1]['productCode'];
            $items[count($items) - 1]['productCode'] = $items[count($items) - 1]['deliveryNumber'];
            $items[count($items) - 1]['deliveryNumber'] = $temp2;
        }

        return $items;
    }
}
