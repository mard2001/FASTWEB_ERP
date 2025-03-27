<?php

namespace App\Http\Controllers\Helpers;

use Illuminate\Http\Request;

class ShellRRPdfParser
{

    protected static  $jobPositions = [
        // Managerial Positions
        "Manager",
        "Assistant Manager",
        "General Manager",
        "Senior Manager",
        "Project Manager",
        "Team Manager",
        "Operations Manager",
        "Product Manager",
        "Sales Manager",
        "Marketing Manager",
        "Finance Manager",
        "HR Manager",
        "IT Manager",
        "Customer Service Manager",
        "poNumber Manager",
        "Branch Manager",
        "Regional Manager",
        "Area Manager",
        "Office Manager",
        "Facilities Manager",

        // Supervisory Positions
        "Supervisor",
        "Lead Supervisor",
        "Senior Supervisor",
        "Team Leader",
        "Shift Supervisor",
        "Floor Supervisor",
        "Operations Supervisor",
        "Sales Supervisor",
        "Customer Service Supervisor",
        "Manufacturing Supervisor",
        "Warehouse Supervisor",
        "Production Supervisor",
        "Quality Control Supervisor",

        // Executive Positions
        "Executive Director",
        "Chief Executive Officer (CEO)",
        "Chief Operating Officer (COO)",
        "Chief Financial Officer (CFO)",
        "Chief Marketing Officer (CMO)",
        "Chief Technology Officer (CTO)",
        "Chief Human Resources Officer (CHRO)",

        // Other Common Positions
        "Coordinator",
        "Administrator",
        "Consultant",
        "Specialist",
        "Director",
        "Vice President (VP)",
        "Head of Department",
        "Associate",
        "Team Lead",
        "Executive Assistant"
    ];

    public static function FUIRRHeader(Request $request)
    {
        $jobPositions = array_map(fn($item) => strtolower($item), self::$jobPositions);
        $pdfString =  str_replace("\r", '', $request->extractedString);

        // Extract Supplier details
        preg_match('/Supplier Name:([\s\S]*?)Status:/', $pdfString, $supplierMatch);
        $supplierDetails = explode("\n", trim($supplierMatch[1] ?? ''));

        $RRSupplierCode = $supplierDetails[0] ?? '';
        $RRSupplierName = $supplierDetails[1] ?? '';
        $RRAddress = $supplierDetails[2] ?? '';
        $RRSupplierTIN = str_replace(' ', '', $supplierDetails[4] ?? '');

        // Extract RR details
        preg_match('/Reference:([\s\S]*?)Item/', $pdfString, $RRMatch);
        $RRDetails = explode("\n", trim($RRMatch[1] ?? ''));

        $RRDate = $RRDetails[0] ?? '';
        $RRReference = $RRDetails[1] ?? '';
        $RRStatus = ($RRDetails[2] ?? '') . ' ' . ($RRDetails[3] ?? '');
        $RRNum = $RRDetails[10] ?? '';

        // Extract RR total
        preg_match('/(\d[\d,]*\.\d+)\s+\w+\s*Comments:/', $pdfString, $totalMatch);
        $RRTotal = $totalMatch[1] ?? '';

        // Extract footer details
        preg_match('/Comments:([\s\S]*?)Page/', $pdfString, $footerMatch);
        $footerDetails = explode("\n", trim($footerMatch[1] ?? ''));

        $RRPrintedBy = $footerDetails[count($footerDetails) - 2] ?? '';

        $preparedByString = $footerDetails[4] ?? '';
        $splitResult = preg_split('/(?=[A-Z])/', $preparedByString, -1, PREG_SPLIT_NO_EMPTY);

        $preparedBy = [];

        foreach ($splitResult as $data) {
            if (in_array(strtolower($data), $jobPositions)) {
                $preparedBy[] = $data;
            }
        }

        foreach ($preparedBy as $data) {
            $preparedByString = str_replace($data, '', $preparedByString);
        }

        $preparedBy[] = trim($preparedByString);

        //return  self::itemsFromFUIRR($pdfString, $RRNum, $RRTotal);

        return [
            'SupplierCode' => $RRSupplierCode,
            'SupplierName' => $RRSupplierName,
            'SupplierTIN' => $RRSupplierTIN,
            'Address' => $RRAddress,
            'RRNo' => $RRNum,
            'Date' => $RRDate,
            'Reference' => $RRReference,
            'Status' => $RRStatus,
            'Total' => str_replace(',', '', $RRTotal),
            'ApprovedBy' => $preparedBy[0] ?? '',
            'CheckedBy' => $preparedBy[1] ?? '',
            'PreparedBy' => $preparedBy[2] ?? '',
            'PrintedBy' => $RRPrintedBy,
            'Items' => self::itemsFromFUIRR($pdfString, $RRNum, $RRTotal),
        ];
    }

    public static function itemsFromFUIRR($pdfString, $RRNum, $RRTotal)
    {
        // Regular expression pattern to extract the portion between 'UnitPrice' and 'Total'
        $pattern = '/UnitPrice([\s\S]*?)' . $RRTotal . '/';

        // Execute the regular expression to get all item details
        preg_match($pattern, $pdfString, $result);
        $extractedValue = $result ? trim($result[1]) : "";

        // Use preg_match_all to extract the blocks
        preg_match_all('/(\d{7,}.*?)(?=\d{7,}|\Z)/s', $extractedValue, $matches);


        $itemsDetails = [];

        // Output the results
        foreach ($matches[1] as $entries) {
            $itemIndex = $itemSku = $itemDecription = $itemQuantity = $itemUOM = $itemWhsCode = $itemUnitPrice = $itemNetVat = $itemVat = $itemGross = null;

            $line = explode("\n", trim($entries));

            $itemIndex = trim($line[1]);
            $itemSku = trim($line[0]);

            $unitPriceAndGross = explode("  ", $line[count($line) - 1]);

            // echo $line[count($line) - 1];

            $itemGross = trim(reset($unitPriceAndGross));
            $itemUnitPrice = trim(end($unitPriceAndGross));
            $itemVat = trim($line[(count($line) - 1) - 1]);
            $itemNetVat = trim($line[(count($line) - 1) - 2]);
            $itemWhsCode = trim($line[(count($line) - 1) - 3]);
            $itemUOM = trim($line[(count($line) - 1) - 4]);
            $itemQuantity = trim($line[(count($line) - 1) - 5]);



            // Escape special characters in $itemQuantity
            $itemQuantityEscaped = preg_quote($itemQuantity, '/');
            // Define the regex pattern
            $pattern = "/\n+\s+\d+\s+\n([\s\S]*?)$itemQuantityEscaped/";

            preg_match($pattern, $entries, $itemDecriptionMatch);

            $itemDecription = $itemDecriptionMatch ? $itemDecriptionMatch[1] : "";

            $itemDecription = preg_replace(['/\n/', '/\s{2,}/'], [" ", ""], $itemDecription);

            // Execute the regular expression to get all item details
            preg_match($pattern, $pdfString, $result);
            $extractedValue = $result ? trim($result[1]) : "";


            // Push the extracted details into the itemsDetails array
            $itemsDetails[] = [
                'PRD_INDEX' => $itemIndex,
                'SKU' => $itemSku,
                'Decription' => $itemDecription,
                'Quantity' => $itemQuantity,
                'UOM' => $itemUOM,
                'WhsCode' => $itemWhsCode,
                'UnitPrice' => str_replace(',', '', $itemUnitPrice),
                'NetVat' => str_replace(',', '', $itemNetVat),
                'Vat' => str_replace(',', '', $itemVat),
                'Gross' => str_replace(',', '', $itemGross),
                'RRNo' => $RRNum
            ];

            //echo preg_replace('/^\s+|\s+$/m', "", $entries) . "\n\n";
        }
        return  $itemsDetails;









        // // Loop through each line to extract item details
        // for ($i = 0; $i < count($lines); $i++) {
        //     $lines[$i] = trim($lines[$i]);

        //     if (empty($lines[$i])) {
        //         continue; // Skip the iteration if the line is empty
        //     }

        //     // Initialize item variables

        //     // if(preg_match('/^\d+$/', $lines[$i])){
        //     //     return $lines[$i] . ' - ' . $lines[$i + 1];
        //     // }

        //     // Check if lines[i] and lines[i+1] are numeric (item index and SKU)
        //     if (preg_match('/^\d+$/', $lines[$i]) && preg_match('/^\d+$/', $lines[$i + 1])) {
        //         $itemIndex = $lines[$i + 1];
        //         $itemSku = $lines[$i];
        //         $itemDecription = $lines[$i + 2] . ' ' . $lines[$i + 3];
        //         $itemQuantity = $lines[$i + 4];
        //         $itemUOM = $lines[$i + 5];
        //         $itemWhsCode = $lines[$i + 6];
        //         $itemNetVat = $lines[$i + 7];
        //         $itemVat = $lines[$i + 8];
        //         // Split itemVat and itemGross
        //         $vatAndGross = array_filter(explode(' ', $lines[$i + 9]), function ($item) {
        //             return trim($item) !== '';
        //         });

        //         $itemUnitPrice = $vatAndGross[1] ?? '';
        //         $itemGross = $vatAndGross[0] ?? '';
        //     }
        // }

        // Return the itemsDetails array
        // return $itemsDetails;
    }
}
