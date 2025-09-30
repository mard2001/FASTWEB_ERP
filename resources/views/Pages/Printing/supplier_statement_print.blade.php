<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Statement of Account - {{ $supplier->SupplierName ?? 'Supplier' }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </head>

    <style>
        /* Header (Company Name & Logo) */
        .statement-header {
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

        .statement-table * {
            font-size: 10px;
            border: none;
        }

        .statement-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            text-align: center;
            padding: .15rem .25rem;
            white-space: nowrap;
        }

        .statement-table td {
            padding: .15rem .25rem;
            text-align: center;
        }

        .statement-table .text-end {
            text-align: right !important;
        }

        .statement-table .text-start {
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

            .statement-table {
                page-break-inside: auto;
            }

            .statement-table tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            .statement-table thead {
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
            
            /* Enhanced print styles for transaction rows */
            .table-info { background-color: rgba(13, 202, 240, 0.1) !important; }
            .table-warning { background-color: rgba(255, 193, 7, 0.1) !important; }
            
            /* Ensure colors are visible in print */
            .text-success { color: #28a745 !important; }
            .text-danger { color: #dc3545 !important; }
            .text-info { color: #0dcaf0 !important; }
            .text-warning { color: #ffc107 !important; }
        }
        
        /* Additional styles for better visual consistency */
        .transaction-row {
            border-bottom: 1px solid #dee2e6;
        }
        
        .payment-row {
            font-style: italic;
            background-color: rgba(13, 202, 240, 0.05);
        }
        
        .credit-memo-row {
            background-color: rgba(255, 193, 7, 0.1);
            font-style: italic;
        }
        
        .auto-cm-row {
            background-color: rgba(255, 193, 7, 0.03);
        }
        
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            white-space: nowrap;
        }
        
        .amount-positive {
            color: #28a745;
            font-weight: 500;
        }
        
        .amount-negative {
            color: #dc3545;
            font-weight: 500;
        }
        
        .amount-neutral {
            color: #6c757d;
        }
    </style>

    <body>
        @php
            $totalRecords = $transactions->count();
            $regularPageSize = 25;
            $lastPageMaxSize = 15;
            
            // Calculate custom pagination
            $chunks = collect();
            $currentIndex = 0;
            
            while ($currentIndex < $totalRecords) {
                $remainingRecords = $totalRecords - $currentIndex;
                
                if ($remainingRecords > $regularPageSize && $remainingRecords <= $regularPageSize + $lastPageMaxSize) {
                    $chunkSize = $remainingRecords - $lastPageMaxSize;
                } else {
                    $chunkSize = min($regularPageSize, $remainingRecords);
                }
                
                $chunks->push($transactions->slice($currentIndex, $chunkSize));
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
                    <p class="text-nowrap text-center statement-header my-0" style="font-size: 16px;">Statement of Account</p>
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
                        <p class="mb-0" style="font-size: 12px;">Report Type: <strong>Statement of Account (All Transactions)</strong></p>
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
            <table class="table statement-table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 25%;">Description</th>
                        <th style="width: 15%;">RR No</th>
                        <th style="width: 12%;">Amount</th>
                        <th style="width: 12%;">Paid</th>
                        <th style="width: 12%;">Balance</th>
                        <th style="width: 9%;">Status</th>
                        <th style="width: 10%;">Terms</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pageData as $row)
                    @php
                        $isPayment = ($row['type'] ?? '') === 'payment';
                        $isCreditMemo = ($row['type'] ?? '') === 'credit_memo';
                        $hasAutoCreditMemo = isset($row['has_auto_credit_memo']) && $row['has_auto_credit_memo'];
                        $hasCreditMemo = isset($row['has_credit_memo']) && $row['has_credit_memo'];
                        
                        // Also check for auto credit memo by reference number pattern
                        $isAutoCreditMemo = $isPayment && isset($row['reference_number']) && strpos($row['reference_number'], 'AUTO-CM-') === 0;
                        
                        // Determine row styling to match table view
                        $rowStyle = '';
                        $rowClass = '';
                        
                        if ($isPayment) {
                            if ($isAutoCreditMemo) {
                                $rowStyle = 'background-color: rgba(255, 193, 7, 0.1); font-style: italic;';
                                $rowClass = 'table-warning';
                            } elseif ($hasCreditMemo) {
                                $rowStyle = 'background-color: rgba(255, 193, 7, 0.1); font-style: italic;';
                                $rowClass = 'table-warning';
                            } else {
                                $rowStyle = 'font-style: italic; background-color: rgba(13, 202, 240, 0.1);';
                                $rowClass = 'table-info';
                            }
                        } elseif ($isCreditMemo) {
                            $rowStyle = 'background-color: #fff3cd; font-style: italic;';
                        } elseif ($hasAutoCreditMemo) {
                            $rowStyle = 'background-color: rgba(255, 193, 7, 0.05);';
                        }
                        
                        // Format date with time if available
                        $formattedDate = 'N/A';
                        if (isset($row['date']) && $row['date']) {
                            $date = \Carbon\Carbon::parse($row['date']);
                            $formattedDate = $date->format('m/d/Y');
                            
                            // Add time if available in sort_date
                            if (isset($row['sort_date']) && $row['sort_date'] !== $row['date']) {
                                $sortDate = \Carbon\Carbon::parse($row['sort_date']);
                                $formattedDate .= ' ' . $sortDate->format('h:i A');
                            }
                        }
                        
                        // Use the description from the controller, which already handles transaction types correctly
                         $description = $row['description'] ?? 'N/A';
                        
                        // Determine amount display
                         $amountDisplay = '';
                         if ($isPayment) {
                             $amountDisplay = '-'; // No original amount for payments
                         } elseif ($isCreditMemo && isset($row['credit_memo']) && $row['credit_memo'] > 0) {
                             $amountDisplay = '₱' . number_format($row['credit_memo'], 2);
                         } elseif (isset($row['amount']) && $row['amount'] > 0) {
                             $amountDisplay = '₱' . number_format($row['amount'], 2);
                         } else {
                             $amountDisplay = '-';
                         }
                        
                        // Determine paid display
                         $paidDisplay = '';
                         $paidColor = '#28a745'; // Default green
                         
                         if ($isPayment) {
                             // For payment rows, show the payment amount
                             if ($isAutoCreditMemo) {
                                 // For auto credit memo applications, show the payment amount
                                 if (isset($row['paid']) && $row['paid'] > 0) {
                                     $paidDisplay = '₱' . number_format($row['paid'], 2);
                                     $paidColor = '#28a745';
                                 } else {
                                     $paidDisplay = '-';
                                 }
                             } elseif (isset($row['paid']) && $row['paid'] > 0) {
                                 $paidDisplay = '₱' . number_format($row['paid'], 2);
                                 $paidColor = $hasCreditMemo ? '#0dcaf0' : '#28a745';
                             } else {
                                 $paidDisplay = '-';
                             }
                         } elseif ($isCreditMemo) {
                             // Credit memos don't show paid amounts
                             $paidDisplay = '-';
                         } elseif ($hasAutoCreditMemo) {
                             // For auto credit memo applications, show the credit amount applied
                             if (isset($row['paid']) && $row['paid'] > 0) {
                                 $paidDisplay = '₱' . number_format($row['paid'], 2);
                                 $paidColor = '#28a745';
                             } elseif (isset($row['credit_memo']) && $row['credit_memo'] > 0) {
                                 $paidDisplay = '₱' . number_format($row['credit_memo'], 2);
                                 $paidColor = '#28a745';
                             } else {
                                 $paidDisplay = '-';
                             }
                         } elseif (isset($row['paid']) && $row['paid'] > 0) {
                             // For invoices that have been paid (partially or fully)
                             $paidDisplay = '₱' . number_format($row['paid'], 2);
                             $paidColor = '#28a745';
                         } else {
                             // No payment made
                             $paidDisplay = '-';
                         }
                        
                        // Determine balance display and color
                        $balance = $row['balance'] ?? 0;
                        $balanceColor = $balance > 0 ? '#dc3545' : '#28a745';
                        
                        // Handle negative balances correctly
                        if ($balance < 0) {
                            // For negative balances (credit memos, overpayments), show negative sign
                            $balanceDisplay = '-₱' . number_format(abs($balance), 2);
                            $balanceColor = '#28a745'; // Green for credit balances
                        } else {
                            $balanceDisplay = '₱' . number_format($balance, 2);
                        }
                    @endphp
                    
                    <tr style="{{ $rowStyle }}">
                        <td class="text-center">{{ $formattedDate }}</td>
                        <td class="text-start">{{ $description }}</td>
                        <td class="text-center">{{ $row['rr_number'] ?? 'N/A' }}</td>
                        <td class="text-end">{{ $amountDisplay }}</td>
                        <td class="text-end" style="color: {{ $paidColor }};">{{ $paidDisplay }}</td>
                        <td class="text-end" style="color: {{ $balanceColor }};">{{ $balanceDisplay }}</td>
                        <td class="text-center">
                            @if(($row['status'] ?? '') === 'Credit Available')
                                <span style="background-color: #17a2b8; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Credit Available</span>
                            @elseif(($row['status'] ?? '') === 'Fully Paid with CM')
                                <span style="background-color: #28a745; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Fully Paid with CM</span>
                            @elseif(($row['status'] ?? '') === 'Fully Paid by CM')
                                <span style="background-color: #28a745; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Fully Paid by CM</span>
                            @elseif(($row['status'] ?? '') === 'Credit Applied')
                                <span style="background-color: #ffc107; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Credit Applied</span>
                            @elseif(($row['status'] ?? '') === 'Payment Made')
                                <span style="background-color: #17a2b8; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Payment Made</span>
                            @elseif(($row['status'] ?? '') === 'Fully Paid')
                                <span style="background-color: #28a745; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Fully Paid</span>
                            @elseif(($row['status'] ?? '') === 'Paid')
                                <span style="background-color: #28a745; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Paid</span>
                            @elseif(($row['status'] ?? '') === 'Pending' && ($row['is_overdue'] ?? false))
                                <span style="background-color: #dc3545; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Overdue</span>
                            @elseif(str_contains(strtolower($row['status'] ?? ''), 'partial'))
                                <span style="background-color: #fd7e14; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Partial</span>
                            @else
                                <span style="background-color: #ffc107; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">{{ $row['status'] ?? 'Pending' }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $row['terms'] ?? 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No transaction data found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Show continuation message on all pages except the last -->
            @if($pageIndex < $totalPages - 1)
                <span style="font-size: 9px;">*Statement continues on the next page...</span>
            @endif

            <!-- Total section and footer only on last page -->
            @if($pageIndex == $totalPages - 1 && $transactions->count() > 0)
                <div class="footer-section">
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
                                    <th style="font-size: 12px;">Total Amount:</th>
                                    <td style="padding-left:20px; font-size: 12px; font-weight: bold;">₱{{ number_format($transactions->sum('amount'), 2) }}</td>
                                </tr>
                                <tr>
                                    <th style="font-size: 12px;">Total Paid:</th>
                                    <td style="padding-left:20px; font-size: 12px; font-weight: bold;">₱{{ number_format($transactions->sum('paid') + $transactions->sum('credit_memo'), 2) }}</td>
                                </tr>
                                <tr>
                                    <th style="font-size: 12px;">Total Balance Due:</th>
                                    <td style="padding-left:20px; border-bottom: 3px double #000; font-size: 12px; font-weight: bold;">₱{{ number_format($transactions->sum('amount') - $transactions->sum('paid') - $transactions->sum('credit_memo'), 2) }}</td>
                                </tr>
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