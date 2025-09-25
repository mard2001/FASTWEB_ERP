<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Counter Receipt - {{ $supplier->SupplierName ?? 'Supplier' }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>

    <style>
        /* Header (Company Name & Logo) */
        .receipt-header {
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

        .receipt-table * {
            font-size: 10px;
            border: none;
        }

        .receipt-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            padding: .15rem .25rem;
            white-space: nowrap;
        }

        .receipt-table td {
            padding: .15rem .25rem;
            text-align: center;
        }

        .receipt-table .text-end {
            text-align: right !important;
        }

        .receipt-table .text-start {
            text-align: left !important;
        }

        .signatory{
            border-top: 2px solid #000 !important;
            padding-top: 10px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .signatory div div:last-child{
            margin-top: 30px;
        }

        .footer-section {
            page-break-inside: avoid;
            break-inside: avoid;
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

        .supplier-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }

        .pending-notice {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: bold;
            color: #333;
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

            .receipt-table {
                page-break-inside: auto;
            }

            .receipt-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .receipt-table thead {
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
            $totalRecords = $pendingTransactions->count();
            $regularPageSize = 25;
            $lastPageMaxSize = 15;
            
            // Calculate custom pagination
            $chunks = collect();
            $currentIndex = 0;
            
            // If there are no records, create one empty chunk to ensure the layout is still shown
            if ($totalRecords == 0) {
                $chunks->push(collect()); // Empty collection for the single page
            } else {
                while ($currentIndex < $totalRecords) {
                    $remainingRecords = $totalRecords - $currentIndex;
                    
                    if ($remainingRecords > $regularPageSize && $remainingRecords <= $regularPageSize + $lastPageMaxSize) {
                        $chunkSize = $remainingRecords - $lastPageMaxSize;
                    } else {
                        $chunkSize = min($regularPageSize, $remainingRecords);
                    }
                    
                    $chunks->push($pendingTransactions->slice($currentIndex, $chunkSize));
                    $currentIndex += $chunkSize;
                }
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
                    <p class="text-nowrap text-center receipt-header my-0" style="font-size: 16px;">Counter Receipt</p>
                </div>

                <!-- Pending Notice -->
                <div class="pending-notice">
                    @if($totalRecords > 0)
                        OUTSTANDING PAYABLES - PAYMENT REQUIRED
                    @else
                        NO OUTSTANDING PAYABLES - ALL TRANSACTIONS PAID
                    @endif
                </div>

                <!-- Supplier Information -->
                <div class="supplier-info">
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-1" style="font-size: 12px;"><strong>Supplier Code:</strong> {{ $supplier->SupplierCode ?? 'N/A' }}</p>
                            <p class="mb-1" style="font-size: 12px;"><strong>Supplier Name:</strong> {{ $supplier->SupplierName ?? 'N/A' }}</p>
                        </div>
                        <div class="col-6">
                            <p class="mb-1" style="font-size: 12px;"><strong>Contact Person:</strong> {{ $supplier->ContactPerson ?? 'N/A' }}</p>
                            <p class="mb-1" style="font-size: 12px;"><strong>Contact Number:</strong> {{ $supplier->ContactNo ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            
                <div class="d-flex justify-content-between headerDetails">
                    <div>
                        <p class="mb-0" style="font-size: 12px;">Report Type: <strong>Counter Receipt @if($totalRecords > 0)(Pending Payments Only)@else(No Pending Payments)@endif</strong></p>
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
            <table class="table receipt-table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 15%;">Reference #</th>
                        <th style="width: 15%;">RR Number</th>
                        <th style="width: 20%;">Description</th>
                        <th style="width: 10%;">Amount</th>
                        <th style="width: 10%;">Paid</th>
                        <th style="width: 10%;">Balance</th>
                        <th style="width: 8%;">Status</th>
                        <th style="width: 10%;">Terms</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pageData as $row)
                    <tr @if($row['type']==='payment') style="background:#eaf7ff;" @elseif($row['type']==='credit_memo') style="background:#fff3cd; font-style: italic;" @endif>
                        <td class="text-center">{{ $row['date'] ? \Carbon\Carbon::parse($row['date'])->format('M d, Y') : 'N/A' }}</td>
                        <td class="text-center">{{ $row['reference_number'] ?? 'N/A' }}</td>
                        <td class="text-center">{{ $row['rr_number'] ?? 'N/A' }}</td>
                        <td class="text-start">{{ $row['description'] ?? '' }}</td>
                        <td class="text-end">@if($row['amount']>0) ₱{{ number_format($row['amount'],2) }} @endif</td>
                        <td class="text-end">
                            @if($row['paid']>0)
                                ₱{{ number_format($row['paid'],2) }}
                            @endif
                        </td>
                        <td class="text-end">₱{{ number_format($row['balance'] ?? 0,2) }}</td>
                        <td class="text-center">
                            @if(($row['status'] ?? '') === 'Credit Available')
                                <span style="background-color: #17a2b8; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Credit Available</span>
                            @elseif(($row['status'] ?? '') === 'Pending' && ($row['is_overdue'] ?? false))
                                <span style="background-color: #dc3545; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Overdue</span>
                            @elseif(str_contains(strtolower($row['status'] ?? ''), 'partial'))
                                <span style="background-color: #fd7e14; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Partial</span>
                            @elseif(($row['status'] ?? '') === 'Paid' || ($row['status'] ?? '') === 'Fully Paid')
                                <span style="background-color: #28a745; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Paid</span>
                            @else
                                <span style="background-color: #007bff; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">{{ $row['status'] ?? 'Pending' }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $row['terms'] ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No pending transactions found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Show continuation message on all pages except the last -->
            @if($pageIndex < $totalPages - 1)
                <span style="font-size: 9px;">*Receipt continues on the next page...</span>
            @endif

            <!-- Total section and footer only on last page -->
            @if($pageIndex == $totalPages - 1)
                <div class="footer-section">
                <!-- Total section immediately after last table -->
                <div class="d-flex justify-content-end tablefooterDiv mt-3">
                    <div class="totalDiv">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th style="font-size: 12px;">Total Pending Records:</th>
                                    <td style="padding-left:20px; font-size: 12px; font-weight: bold;">{{ $totalRecords }}</td>
                                </tr>
                                @if($totalRecords > 0)
                                <tr>
                                    <th style="font-size: 12px;">Total Outstanding Amount:</th>
                                    <td style="padding-left:20px; font-size: 12px; font-weight: bold;">₱{{ number_format($pendingTransactions->sum('amount'), 2) }}</td>
                                </tr>
                                <tr>
                                    <th style="font-size: 12px;">Total Amount Paid:</th>
                                    <td style="padding-left:20px; font-size: 12px; font-weight: bold;">₱{{ number_format($pendingTransactions->sum('paid') + $pendingTransactions->sum('credit_memo'), 2) }}</td>
                                </tr>
                                <tr>
                                    <th style="font-size: 14px;">TOTAL AMOUNT DUE:</th>
                                    <td style="padding-left:20px; border-bottom: 3px double #000; font-size: 14px; font-weight: bold;">₱{{ number_format($pendingTransactions->sum('amount') - $pendingTransactions->sum('paid') - $pendingTransactions->sum('credit_memo'), 2) }}</td>
                                </tr>
                                @else
                                <tr>
                                    <th style="font-size: 14px;">STATUS:</th>
                                    <td style="padding-left:20px; border-bottom: 3px double #000; font-size: 14px; font-weight: bold; color: #28a745;">ALL TRANSACTIONS PAID</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                @php
                     $lastPageRecords = $pageData->count();
                     $remainingRows = $lastPageMaxSize - $lastPageRecords;
                     $spacerHeight = max(50, $remainingRows * 20); // Reduced spacing
                 @endphp
                 
                 <div style="height: {{ $spacerHeight }}px;"></div>

                <!-- Footer at bottom of last page -->
                <div class="d-flex justify-content-between signatory text-center mb-3" style="font-size: 12px; margin-top: 20px; page-break-inside: avoid;">
                    <div class="preparedDiv">
                        <div>Prepared by:</div>
                        <div class="preparer">{{ $user->name ?? 'System User' }}</div>
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