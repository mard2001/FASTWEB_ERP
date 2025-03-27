<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receiving Report TESTING</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<style>
    /* Header (Company Name & Logo) */
    .rr-header {
        font-size: 1.3rem;
        /* Adjust between 14–18pt */
        font-weight: bold;
        text-transform: uppercase;
        font-family: Arial, Helvetica, sans-serif;
    }

    .headerDetails{
        padding-bottom: 10px; 
        border-bottom: 2px solid #000 !important;
    }

    .tablefooterDiv{
        border-top: 2px solid #000 !important;
    }

    .commentDiv{
        border: 1px solid #2e2e2e !important;
        min-height: 50px; 
        max-width:500px;
        font-size: 10px;
    }
    .rr-info * {
        font-size: 10px;
        border: none;
        table-layout: fixed; /* Ensures the columns respect assigned widths */
        width: 100%; /* Makes table responsive */
        height: 30px !important; /* Ensure at least 2 lines */
        white-space: normal;
        word-wrap: break-word;
        overflow: hidden;
        line-height: 10px;
    }

    .rr-info tbody tr td{
        padding: .15rem .25rem;
    }

    .rr-info thead tr:first-child {
        text-align: center;
        text-decoration: underline;
        padding: .15rem .25rem;
        height: 20px !important;
    }

    .signatory{
        border-top: 2px solid #000 !important;
        padding-top: 10px;
    }

    .signatory div div:last-child{
        margin-top: 30px;
    }

    .footerText {
        width: 100%;
        text-align: center;
        background: white;
        padding: 10px 0;
        font-size: 12px;
        display: flex;
        flex-direction: column;
    }

    

    @media print {
        @page {
            margin: 0; /* Removes default browser print margins */
        }
        body {
            margin: 0;
            padding: 0 20px;
        }

        .header, .table-container {
            page-break-before: always;
        }
        hr {
            border: 10px solid black !important;
        }

        .footerTextLast {
            /* position: absolute; */
            bottom: 0;
            left: 0;
            width: 100%;
            text-align: center;
            background: white;
            padding: 10px 0;
            font-size: 12px;
        }
    }
</style>




<body>
    @php
        $maxRowsPerPage = 15;  
        $rowCount = 0; 
        $totalPages = ceil(count($report->items)/$maxRowsPerPage);
        $pageNumber = 1;
        $totalAmount = 0;
    @endphp

    @foreach ($report->items as $index => $item)
        @if ($rowCount % $maxRowsPerPage == 0)
            @if ($rowCount > 0)
                <div style="page-break-before: always;"></div>
                @php
                    $pageNumber++;
                @endphp
            @endif

            <header class="px-2 py-1">
                <div class="d-flex flex-row mb-3">
                    <div class="p-2 pt-3">
                        <img src="https://jobslin.com/storage/logow/ph/FAST/fast-unimerchants-inc-1722319497.webp" alt="Description" width="250" height="80">
                    </div>
                    <div class="p-2">
                        <p class="m-0" style="font-size: 16px; font-weight:700">FAST DISTRIBUTION CORPORATION</p>
                        <p class="m-0" style="font-size: 10px;">H Abellana Street, Canduman, Mandaue City, Cebu, 6014</p>
                        <p class="m-0" style="font-size: 10px;">Tel. No. (032) 343-7888</p>
                        <p class="m-0" style="font-size: 10px;">Business Style: Wholesale and Retail Distribution Services</p>
                        <p class="m-0" style="font-size: 10px;"> VAT REG. TIN 485-010-749-00006</p>
                        
                    </div>
                </div>
                <div class="d-flex justify-content-between">
                    <p class="text-nowrap text-center rr-header my-0" style="font-size: 16px;">Receiving Report</p>
                </div>
            
                <div class="d-flex justify-content-between headerDetails">
                    <div>
                        <p class="distName fw-bold mb-4" style="font-size: 14px;">{{ $report->distName }}</p>
                        <div class="d-flex justify-content-between">
                            <p class="mb-0" style="font-size: 12px;">Supplier Code:<strong class="supplierCode text-uppercase" style="margin-left: 19px">{{ $report->supCode }}</strong></p>
                            <p class="mb-0" style="margin-right: 20px; font-size: 12px;">Supplier's TIN:<strong class="supplierCode text-uppercase" style="margin-left: 15px">{{ $report->supTIN }}</strong></p>
                        </div>
                        <p class="mb-0" style="font-size: 12px;">Supplier Name:<strong class="supplierName text-uppercase" style="margin-left: 15px">{{ $report->supName }}</strong></p>
                        <p class="mb-0" style="font-size: 12px;">Address:<span class="supplierAdd text-uppercase" style="margin-left: 50px">{{ $report->supAdd }}</span></p>
                    </div>
                    <div>
                        <p class="mb-0" style="font-size: 12px;">RR No.:<strong class="rrNum text-uppercase" style="margin-left: 31px">{{ $report->rrNo }}</strong></p>
                        <p class="mb-0" style="font-size: 12px;">Date:<span class="rrDate" style="margin-left: 44px">{{ $report->rrDate }}</span></p>
                        <p class="" style="font-size: 12px;">Reference:<strong class="rrRefNum" style="margin-left: 16px">{{ $report->rrRef }}</strong></p>
                        <p class="mb-0" style="font-size: 12px;">Status:<span class="rrNum text-uppercase" style="margin-left: 37px">{{ $report->rrStat1 }}</span></p>
                        <p class="mb-0" style="font-size: 12px;"><strong class="rrNum text-uppercase" style="margin-left: 72px">{{ $report->rrStat2 }}</strong></p>
                    </div>
                </div>
            </header>

            <table class="table rr-info">
                <thead>
                    <tr>
                        <th style="width: 5%;">No.</th>
                        <th style="width: 10%;">Item</th>
                        <th class="text-start" style="width: 25%;">Description</th>
                        <th style="width: 5%;">Quantity</th>
                        <th style="width: 5%;">OuM</th>
                        <th style="width: 10%;">WhsCode</th>
                        <th class="text-end" style="width: 10%;">UnitPrice</th>
                        <th class="text-end" style="width: 10%;">Net of Vat</th>
                        <th class="text-end" style="width: 10%;">Vat</th>
                        <th class="text-end" style="width: 10%;">Gross</th>
                    </tr>
                </thead>
                <tbody>
        @endif

        {{-- Table rows --}}
        <tr>
            <td class="text-center" style="width: 5%;">{{ $index + 1 }}</td>  
            <td class="text-center" style="width: 10%;">{{ $item->itemCode }}</td>  
            <td class="text-start" style="width: 25%;">{{ $item->itemDesc }}</td>  
            <td class="text-center" style="width: 5%;">{{ $item->itemQty }}</td>  
            <td class="text-center" style="width: 5%;">CS</td>  
            <td class="text-center" style="width: 10%;">{{ $item->itemWhsCode }}</td>  
            <td class="text-end" style="width: 10%;">{{ number_format($item->itemUnitPrice,2) }}</td>  
            <td class="text-end" style="width: 10%;">{{ number_format($item->netVat,2) }}</td>  
            <td class="text-end" style="width: 10%;">{{ number_format($item->vat,2) }}</td>  
            <td class="text-end" style="width: 10%;">{{ number_format($item->gross,2) }}</td>  
        </tr>
        @php
            $totalAmount += $item->gross;
            $rowCount++; // Increment row count
        @endphp

        @if ($rowCount % $maxRowsPerPage == 0)
                </tbody>
            </table>
            <span style="font-size: 9px;">*Report continues on the next page...</span>
            @if ($pageNumber != $totalPages )
                <div class="footerText" style="margin-top: 180px">
                    <strong style="font-size: 9px">"THIS DOCUMENT IS NOT VALID FOR CLAIM OF INPUT TAX."</strong>
                    <span style="font-size: 8px">'THIS IS A SYSTEM-GENERATED DOCUMENT.'</span>
                    @php
                        echo "<div style='text-align: center; font-size: 7px'>Page {$pageNumber} of {$totalPages}</div>";
                    @endphp
                </div>
            @endif
        @endif
    @endforeach

    @if ($rowCount % $maxRowsPerPage != 0)
            </tbody>
        </table>
    @endif

    <div class="d-flex justify-content-end tablefooterDiv">
        <div class="totalDiv">
            <table>
                <tbody>
                    <tr>
                        <th>Total</th>
                        <td style="padding-left:20px; border-bottom: 5px solid; border-style: double;">{{ number_format($totalAmount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if ($pageNumber == $totalPages)
        <div class="d-flex justify-content-start">
            <div>
                <span style="font-size: 12px">Comments:</span>
                <div class="commentDiv px-3 mx-1 mb-4" >
                    THIS IS A TESTING COMMENT. Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry
                </div>
            </div>
        </div>
        @php
            $remainingRows = ($maxRowsPerPage*$pageNumber)-$rowCount; 
            $maxMargin = $remainingRows*28;
            echo '<div style="height: '.$maxMargin.'px !important"></div>';
        @endphp
        <div class="d-flex justify-content-between signatory text-center mb-3" style="font-size: 12px;">
            <div class="preparedDiv">
                <div>Prepared by:</div>
                <div class="preparer ">{{ $report->prepared }}</div>
            </div>
            <div class="checkedDiv">
                <div>Checked by:</div>
                <div class="checker ">{{ $report->checked }}</div>
            </div>
            <div class="approvedDiv">
                <div>Approved by:</div>
                <div class="approver ">{{ $report->approved }}</div>
            </div>
        </div>

        <div class="footerText" style="margin-top: 20px">
            <strong style="font-size: 9px">"THIS DOCUMENT IS NOT VALID FOR CLAIM OF INPUT TAX."</strong>
            <span style="font-size: 8px">'THIS IS A SYSTEM-GENERATED DOCUMENT.'</span>
            @php
                echo "<div style='text-align: center; font-size: 7px'>Page {$pageNumber} of {$totalPages}</div>";
            @endphp
        </div>
    @endif   
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="{{ asset('assets/js/printing/rrPrintJS.js') }}"></script>


<script>
    // window.onload = function() {
    //     window.print();

    //     // Close the tab after printing or if the user cancels
    //     window.onafterprint = function() {
    //         window.close();
    //     };
    // };
</script>
<script>
    async function savePageAsPDF() {
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4'); // Portrait, millimeters, A4 size

        // Capture the entire body as an image
        const canvas = await html2canvas(document.body, {
            scale: 2
        });
        const imgData = canvas.toDataURL("image/png");

        // Calculate dimensions to fit the A4 page
        const imgWidth = 210; // A4 width in mm
        const imgHeight = (canvas.height * imgWidth) / canvas.width; // Maintain aspect ratio

        doc.addImage(imgData, "PNG", 0, 0, imgWidth, imgHeight);
        doc.save("full-page.pdf");
    }

    // Call the function when Ctrl + P is pressed
    document.addEventListener("keydown", function(event) {
        if (event.ctrlKey && event.key === "p") {
            event.preventDefault(); // Prevent default print dialog
            savePageAsPDF();
        }
    });
</script>

</html>