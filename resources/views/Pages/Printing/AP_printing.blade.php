<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accounts Payable Report</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>

    <style>
        /* Header (Company Name & Logo) */
        .ap-header {
            font-size: 1.3rem;
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

        .ap-table * {
            font-size: 10px;
            border: none;
        }

        .ap-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            padding: .15rem .25rem;
            white-space: nowrap;
        }

        .ap-table td {
            padding: .15rem .25rem;
            text-align: center;
        }

        .ap-table .text-end {
            text-align: right !important;
        }

        .ap-table .text-start {
            text-align: left !important;
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
                margin: 0.5in;
                size: A4;
            }
            body {
                margin: 0;
                padding: 0;
            }

            .page-break {
                page-break-before: always;
            }

            .ap-table {
                page-break-inside: auto;
            }

            .ap-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .ap-table thead {
                display: table-header-group;
                text-decoration: underline;
            }

            .page-number {
                position: fixed;
                bottom: 20px;
                right: 20px;
                font-size: 10px;
            }

            .footerTextLast {
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
            $totalRecords = $reports->count();
            $regularPageSize = 30;
            $lastPageMaxSize = 20;
            
            // Calculate custom pagination
            $chunks = collect();
            $currentIndex = 0;
            
            while ($currentIndex < $totalRecords) {
                $remainingRecords = $totalRecords - $currentIndex;
                
                // If this would be the last chunk and it has more than 20 records,
                // take only enough to leave 20 or fewer for the last page
                if ($remainingRecords > $regularPageSize && $remainingRecords <= $regularPageSize + $lastPageMaxSize) {
                    $chunkSize = $remainingRecords - $lastPageMaxSize;
                } else {
                    $chunkSize = min($regularPageSize, $remainingRecords);
                }
                
                $chunks->push($reports->slice($currentIndex, $chunkSize));
                $currentIndex += $chunkSize;
            }
            
            $totalPages = $chunks->count();
        @endphp

        @foreach($chunks as $pageIndex => $pageData)
            @if($pageIndex > 0)
                <div class="page-break"></div>
            @endif
            
            <!-- Header for each page -->
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
                    <p class="text-nowrap text-center ap-header my-0" style="font-size: 16px;">Accounts Payable Report</p>
                </div>
            
                <div class="d-flex justify-content-between headerDetails">
                    <div>
                        <p class="mb-0" style="font-size: 12px;">Report Type: <strong>All Accounts Payable Data</strong></p>
                        <p class="mb-0" style="font-size: 12px;">Total Records: <strong>{{ $totalRecords }}</strong></p>
                    </div>
                    <div>
                        <p class="mb-0" style="font-size: 12px;">Generated: <span>{{ now()->format('Y-m-d H:i:s') }}</span></p>
                        <p class="mb-0" style="font-size: 12px;">Generated By: <strong>{{ $user->name ?? 'System User' }}</strong></p>
                        <p class="mb-0" style="font-size: 12px;">Page: <strong>{{ $pageIndex + 1 }} of {{ $totalPages }}</strong></p>
                    </div>
                </div>
            </header>

            <!-- Data table for current page -->
            <table class="table ap-table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 15%;">Branch</th>
                        <th style="width: 10%;">Opening Balance</th>
                        <th style="width: 10%;">Invoices</th>
                        <th style="width: 10%;">Debit Notes</th>
                        <th style="width: 10%;">Credit Notes</th>
                        <th style="width: 10%;">Adjustments</th>
                        <th style="width: 10%;">Disbursements</th>
                        <th style="width: 10%;">Closing Balance</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 10%;">Report Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pageData as $report)
                    <tr>
                        <td class="text-center">{{ $report->id }}</td>
                        <td class="text-start">{{ $report->branch ?? 'N/A' }}</td>
                        <td class="text-end">{{ number_format($report->opening_balance ?? 0, 2) }}</td>
                        <td class="text-end">{{ number_format($report->invoices ?? 0, 2) }}</td>
                        <td class="text-end">{{ number_format($report->debit_notes ?? 0, 2) }}</td>
                        <td class="text-end">{{ number_format($report->credit_notes ?? 0, 2) }}</td>
                        <td class="text-end">{{ number_format($report->adjustments ?? 0, 2) }}</td>
                        <td class="text-end">{{ number_format($report->disbursements ?? 0, 2) }}</td>
                        <td class="text-end">{{ number_format($report->closing_balance ?? 0, 2) }}</td>
                        <td class="text-center">{{ $report->status ?? 'N/A' }}</td>
                        <td class="text-center">{{ $report->report_date ? \Carbon\Carbon::parse($report->report_date)->format('Y-m-d') : 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center">No accounts payable data found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Show continuation message on all pages except the last -->
            @if($pageIndex < $totalPages - 1)
                <span style="font-size: 9px;">*Report continues on the next page...</span>
            @endif

            <!-- Total section and footer only on last page -->
            @if($pageIndex == $totalPages - 1 && $reports->count() > 0)
                <!-- Total section immediately after last table -->
                <div class="d-flex justify-content-end tablefooterDiv mt-3">
                    <div class="totalDiv">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th style="font-size: 12px;">Total Records:</th>
                                    <td style="padding-left:20px; font-size: 12px; font-weight: bold;">{{ $totalRecords }}</td>
                                </tr>
                                <tr>
                                    <th style="font-size: 12px;">Total Closing Balance:</th>
                                    <td style="padding-left:20px; border-bottom: 3px double #000; font-size: 12px; font-weight: bold;">{{ number_format($reports->sum('closing_balance'), 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                @php
                     $lastPageRecords = $pageData->count();
                     $remainingRows = $lastPageMaxSize - $lastPageRecords;
                     $spacerHeight = max(100, $remainingRows * 30); // Reduced spacer calculation
                 @endphp
                 
                 <div style="height: {{ $spacerHeight }}px;"></div>

                <!-- Footer at bottom of last page -->
                <div class="d-flex justify-content-between signatory text-center mb-3" style="font-size: 12px; margin-top: 50px;">
                    <div class="preparedDiv">
                        <div>Prepared by:</div>
                        <div class="preparer">{{ auth()->user()->name ?? 'System User' }}</div>
                    </div>
                    <div class="checkedDiv">
                        <div>Checked by:</div>
                        <div class="checker">Supervisor</div>
                    </div>
                    <div class="approvedDiv">
                        <div>Approved by:</div>
                        <div class="approver">Manager</div>
                    </div>
                </div>
            @endif
        @endforeach
    </body>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <script>
        window.onload = function() {
            window.print();

            // Close the tab after printing or if the user cancels
            window.onafterprint = function() {
                window.close();
            };
        };
    </script>
</html>