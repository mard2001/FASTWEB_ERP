<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Count Sheet</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<style>
    .countSht-info * {
        font-size: 10px;
        border: none;
        table-layout: fixed; /* Ensures the columns respect assigned widths */
        width: 100%; /* Makes table responsive */
        height: 33px !important; /* Ensure at least 2 lines */
        white-space: normal;
        word-wrap: break-word;
        overflow: hidden;
        line-height: 10px;
    }
    .countSht-info tbody tr td{
        border: 2px solid #000 !important;

    }

    @media print {
        @page {
            margin: 0; /* Removes default browser print margins */
        }
        body {
            margin: 10px 0;
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
        $maxRowsPerPage = 27;  
        $rowCount = 0; 
        $totalPages = ceil(count($report)/$maxRowsPerPage);
        $pageNumber = 1;
    @endphp
    @foreach ($report as $item)
        @if ($rowCount % $maxRowsPerPage == 0)
            @if ($rowCount > 0)
                <div style="page-break-before: always;"></div>
                @php
                    $pageNumber++;
                @endphp
            @endif
            <div class="container-fluid">
                <h5>COUNT SHEET</h5>
                <div class="row">
                    <div class="col d-flex flex-column mb-3">
                        <span>Fast Distribution Corporation</span>
                        {{-- <span>{{ $report->branch }}</span> --}}
                    </div>
                    <div class="col d-flex flex-column mb-3">
                        {{-- <span>{{ $report->warehouse }}</span> --}}
                        {{-- <span>{{ $report->date }}</span> --}}
                    </div>
                </div>
            </div>
            <table class="table countSht-info">
                <thead>
                    <tr>
                        <th scope="col" style="width: 5%;"> </th>
                        <th scope="col" style="width: 15%;">Stock Code</th>
                        <th scope="col" style="width: 50%;">Description</th>
                        {{-- <th scope="col" style="width: 10%;">MNLCNT</th> --}}
                        <th scope="col" style="width: 10%;">Cases</th>
                        <th scope="col" style="width: 10%;">IB</th>
                        <th scope="col" style="width: 10%;">Pcs</th>
                    </tr>
                </thead>
                <tbody>
        @endif                   
                    <tr>
                        <td style="width: 5%;" class="text-center">{{ $rowCount+1 }}</td>
                        <td style="width: 15%;">{{ $item['StockCode'] ?? '' }}</td>
                        <td style="width: 50%;">{{ $item['Description']?? '' }}</td>
                        {{-- <td style="width: 10%;">{{ $item['MNLCOUNT']?? '' }}</td> --}}
                        <td style="width: 10%;" class="text-center">
                            {{ isset($item['ConvResult']['inCS']) && $item['ConvResult']['inCS'] > 0 ? $item['ConvResult']['inCS'] : '' }}
                        </td>
                        <td style="width: 10%;" class="text-center">
                            {{ isset($item['ConvResult']['inIB']) && $item['ConvResult']['inIB'] > 0 ? $item['ConvResult']['inIB'] : '' }}
                        </td>
                        <td style="width: 10%;" class="text-center">
                            {{ isset($item['ConvResult']['inPC']) && $item['ConvResult']['inPC'] > 0 ? $item['ConvResult']['inPC'] : '' }}
                        </td>
                    </tr>
                    @php
                        $rowCount++; // Increment row count
                    @endphp

        @if ($rowCount % $maxRowsPerPage == 0)
                </tbody>
            </table>
            @if ($pageNumber != $totalPages )
                <span style="font-size: 9px;">*Report continues on the next page...</span>
                {{-- <div class="footerText d-flex justify-content-between mt-4">
                    <div class="countedDiv">
                        COUNTED BY:
                        <span class="counter">{{ $report->counted }}</span>
                    </div>
            
                    <div class="Page">
                        @php
                            echo "<span style='text-align: center; font-size: 9px'>Page {$pageNumber} of {$totalPages}</span>";
                        @endphp
                    </div>
            
                    <div class="confirmedDiv">
                        CONFIRMED BY:
                        <span class="confirmer">{{ $report->confirmed }}</span>
                    </div>
                </div> --}}
            @endif
        @endif   
    @endforeach
    
    @if ($rowCount % $maxRowsPerPage != 0)
                @php
                    $remainingRows = ($maxRowsPerPage*$pageNumber)-$rowCount; 
                @endphp
                @for ($i = 0; $i < $remainingRows; $i++)
                    <tr>
                        <td style="width: 5%;"> </td>
                        <td style="width: 15%;"> </td>
                        <td style="width: 50%;"> </td>
                        <td style="width: 10%;"> </td>
                        <td style="width: 10%;"> </td>
                        <td style="width: 10%;"> </td>
                    </tr>
                @endfor               
            </tbody>
        </table>
    @endif

    @if ($pageNumber == $totalPages)
        <div class="footerText d-flex justify-content-between mt-4">
            <div class="countedDiv">
                COUNTED BY:
                {{-- <span class="counter">{{ $report->counted }}</span> --}}
            </div>

            <div class="Page">
                @php
                    echo "<span style='text-align: center; font-size: 9px'>Page {$pageNumber} of {$totalPages}</span>";
                @endphp
            </div>

            <div class="confirmedDiv" style="margin-right: 100px">
                CONFIRMED BY:
                {{-- <span class="confirmer">{{ $report->confirmed }}</span> --}}
            </div>
        </div>
    @endif  
    
</body>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
{{-- <script src="{{ asset('assets/js/printing/rrjs.js') }}"></script> --}}


<script>
    window.onload = function() {
        window.print();

        // Close the tab after printing or if the user cancels
        window.onafterprint = function() {
            window.close();
        };
    };
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