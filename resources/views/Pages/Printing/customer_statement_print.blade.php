<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Statement of Account - {{ $customer->Name ?? 'Customer' }}</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <!-- SheetJS for Excel export -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
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

        .page-divider {
            border-top: 2px solid #000 !important;
            margin-top: 8px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        /* Prevent totals/footer block from breaking across pages */
        .tablefooterDiv,
        .tablefooterDiv * {
            page-break-inside: avoid;
            break-inside: avoid;
        }
        @media print {
            .receipt-table { page-break-after: auto; }
            .tablefooterDiv { page-break-before: avoid; }
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

        .customer-info {
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

        /* Header Controls Styling */
        .header-controls {
            position: absolute;
            top: 10px;
            right: 15px;
            z-index: 1000;
        }

        .btn-print {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: background-color 0.2s;
            margin-bottom: 8px;
            display: block;
            width: 100%;
        }

        .btn-print:hover {
            background-color: #218838;
        }

        .btn-excel {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: background-color 0.2s;
            margin-bottom: 8px;
            display: block;
            width: 100%;
        }

        .btn-excel:hover {
            background-color: #0056b3;
        }

        .date-filter-dropdown {
            margin-top: 5px;
        }

        .date-select {
            padding: 4px 8px;
            font-size: 11px;
            border: 1px solid #ced4da;
            background-color: #fff;
            color: #495057;
            border-radius: 4px;
            cursor: pointer;
            min-width: 120px;
            max-width: 150px;
        }

        .date-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
            outline: 0;
        }

        .custom-date-range {
            margin-top: 8px;
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            background-color: #f8f9fa;
        }

        .date-inputs {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .date-input {
            padding: 3px 6px;
            font-size: 10px;
            border: 1px solid #ced4da;
            border-radius: 3px;
            width: 100%;
        }

        .btn-apply {
            padding: 4px 8px;
            font-size: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-apply:hover {
            background-color: #218838;
        }

        .filter-label {
            font-size: 11px;
            color: #6c757d;
            margin-bottom: 2px;
        }

        /* Make header relative for absolute positioning */
        header {
            position: relative;
        }

        @media print {
            .header-controls {
                display: none !important;
            }
        }
    </style>

    <body>
        <div id="screenContainer">
        @php
            $totalRecords = $transactions->count();
            $regularPageSize = 25;
            $lastPageMaxSize = 12; // Leave more space for totals/footer on last page
            
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
                    
                    $chunks->push($transactions->slice($currentIndex, $chunkSize));
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
                <!-- Print Controls in Header (Hidden during print) -->
                <div class="header-controls">
                    <button type="button" class="btn-print" onclick="printStatement()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button type="button" class="btn-excel" onclick="exportToExcel()">
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button>
                    <div class="date-filter-dropdown">
                        <div class="filter-label">Date Filter:</div>
                        <select id="dateFilterSelect" class="form-select date-select" onchange="handleFilterChange()">
                            <option value="all" selected>All</option>
                            <option value="today">Today</option>
                            <option value="last3days">Last 3 Days</option>
                            <option value="last7days">Last 7 Days</option>
                            <option value="lastweek">Last Week</option>
                            <option value="thismonth">This Month</option>
                            <option value="lastmonth">Last Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                        <div id="customDateRange" class="custom-date-range" style="display: none;">
                            <div class="date-inputs">
                                <input type="date" id="fromDate" class="form-control date-input" placeholder="From">
                                <input type="date" id="toDate" class="form-control date-input" placeholder="To">
                                <button type="button" class="btn-apply" onclick="applyCustomRange()">Apply</button>
                            </div>
                        </div>
                    </div>
                </div>
                
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
                    <p class="text-nowrap text-center receipt-header my-0" style="font-size: 16px;">Statement of Account</p>
                </div>

                <!-- Customer Information -->
                <div class="customer-info">
                    <div class="row">
                        <div class="col-6">
                            <p class="mb-1" style="font-size: 12px;"><strong>Customer Code:</strong> {{ $customer->Customer ?? 'N/A' }}</p>
                            <p class="mb-1" style="font-size: 12px;"><strong>Customer Name:</strong> {{ $customer->Name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-6">
                            <p class="mb-1" style="font-size: 12px;"><strong>Contact Person:</strong> {{ $customer->Contact ?? 'N/A' }}</p>
                            <p class="mb-1" style="font-size: 12px;"><strong>Contact Number:</strong> {{ $customer->Telephone ?? 'N/A' }}</p>
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
            <table class="table receipt-table table-bordered">
                <thead>
                    <tr>
                        <th style="width: 12%;">Date</th>
                        <th style="width: 15%;">Reference #</th>
                        <th style="width: 15%;">SO Number</th>
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
                        <td class="text-center">{{ $row['so_number'] ?? 'N/A' }}</td>
                        <td class="text-start">{{ $row['description'] ?? '' }}</td>
                        <td class="text-end">@if($row['amount']>0) ₱{{ number_format($row['amount'],2) }} @endif</td>
                        <td class="text-end">
                            @if($row['paid']>0)
                                @if($row['type'] === 'transaction' && str_contains($row['description'], 'CM Applied'))
                                    (₱-{{ number_format($row['paid'],2) }})
                                @else
                                    ₱{{ number_format($row['paid'],2) }}
                                @endif
                            @endif
                        </td>
                        <td class="text-end">₱{{ number_format($row['balance'] ?? 0,2) }}</td>
                        <td class="text-center">
                            @if(($row['status'] ?? '') === 'Credit Available')
                                <span style="background-color: #17a2b8; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Credit Available</span>
                            @elseif(($row['status'] ?? '') === 'Credit Applied')
                                <span style="background-color: #6f42c1; color: white; padding: 2px 6px; border-radius: 3px; font-size: 8px;">Credit Applied</span>
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
                        <td colspan="9" class="text-center">No transactions found</td>
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
                                    <td style="padding-left:20px; font-size: 12px; font-weight: bold;">₱{{ number_format($transactions->sum('amount'), 2) }}</td>
                                </tr>
                                <tr>
                                    <th style="font-size: 12px;">Total Amount Paid:</th>
                                    <td style="padding-left:20px; font-size: 12px; font-weight: bold;">₱{{ number_format($transactions->sum('paid') + $transactions->sum('credit_memo'), 2) }}</td>
                                </tr>
                                <tr>
                                    <th style="font-size: 12px;">Total Amount Due:</th>
                                    <td style="padding-left:20px; border-bottom: 3px double #000; font-size: 14px; font-weight: bold;">₱{{ number_format($transactions->sum('amount') - $transactions->sum('paid') - $transactions->sum('credit_memo'), 2) }}</td>
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
        </div>
        <div id="printContainer"></div>
    </body>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

        <script>
        (function(){
            var s = document.createElement('style');
            s.textContent = '#printContainer{display:none;}@media print{#screenContainer{display:none !important;}#printContainer{display:block !important;}}';
            document.head.appendChild(s);
        })();

        function buildPrintPages(){
            var container = document.getElementById('printContainer');
            if(!container) return;
            var headerEl = document.querySelector('header');
            var headerHtml = headerEl ? headerEl.outerHTML : '';
            var theadEl = document.querySelector('.receipt-table thead');
            var theadHtml = theadEl ? theadEl.outerHTML : '';
            var footerSectionEl = Array.from(document.querySelectorAll('.footer-section')).pop();
            var footerTotalsHtml = footerSectionEl ? footerSectionEl.outerHTML : '';
            var allRows = Array.from(document.querySelectorAll('.receipt-table tbody tr'));
            var rows = allRows.filter(function(r){ return r.style.display !== 'none'; });
            var pageSize = 10;
            var html = '';
            if(rows.length === 0){
                html = '<div>' + headerHtml + '<table class="table receipt-table table-bordered">' + theadHtml + '<tbody></tbody></table>' + footerTotalsHtml + '</div>';
                container.innerHTML = html;
                return;
            }
            for(var i=0;i<rows.length;i+=pageSize){
                var pageRows = rows.slice(i,i+pageSize);
                var rowsHtml = pageRows.map(function(r){return r.outerHTML;}).join('');
                var isLast = (i + pageSize) >= rows.length;
                html += '<div>' + headerHtml + '<table class="table receipt-table table-bordered">' + theadHtml + '<tbody>' + rowsHtml + '</tbody></table>' + (isLast ? footerTotalsHtml : '<div class="page-divider"></div>') + '</div>' + (isLast ? '' : '<div class="page-break"></div>');
            }
            container.innerHTML = html;
        }

        window.onbeforeprint = function(){ buildPrintPages(); };
        let allTableRows = [];
        let currentFilter = 'all';

        window.onload = function() {
            // Store all table rows for filtering
            const tables = document.querySelectorAll('.receipt-table tbody');
            tables.forEach(table => {
                const rows = Array.from(table.querySelectorAll('tr'));
                allTableRows = allTableRows.concat(rows);
            });

            // Auto-print functionality (commented out for testing)
            // window.print();
            // window.onafterprint = function() {
            //     window.close();
            // };
        };

        function printStatement() {
            window.print();
        }

        function handleFilterChange() {
            const select = document.getElementById('dateFilterSelect');
            const customRange = document.getElementById('customDateRange');
            const selectedValue = select.value;

            if (selectedValue === 'custom') {
                customRange.style.display = 'block';
            } else {
                customRange.style.display = 'none';
                filterByDateRange(selectedValue);
            }
        }

        function applyCustomRange() {
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;

            if (!fromDate || !toDate) {
                alert('Please select both start and end dates.');
                return;
            }

            if (new Date(fromDate) > new Date(toDate)) {
                alert('Start date cannot be later than end date.');
                return;
            }

            filterByDateRange('custom', fromDate, toDate);
        }

        function filterByDateRange(filterType, customFrom = null, customTo = null) {
            const today = new Date();
            let startDate, endDate;

            switch (filterType) {
                case 'today':
                    startDate = new Date(today);
                    endDate = new Date(today);
                    break;
                case 'last3days':
                    startDate = new Date(today);
                    startDate.setDate(today.getDate() - 2);
                    endDate = new Date(today);
                    break;
                case 'last7days':
                    startDate = new Date(today);
                    startDate.setDate(today.getDate() - 6);
                    endDate = new Date(today);
                    break;
                case 'lastweek':
                    const lastWeekStart = new Date(today);
                    lastWeekStart.setDate(today.getDate() - today.getDay() - 6);
                    const lastWeekEnd = new Date(lastWeekStart);
                    lastWeekEnd.setDate(lastWeekStart.getDate() + 6);
                    startDate = lastWeekStart;
                    endDate = lastWeekEnd;
                    break;
                case 'thismonth':
                    startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                    break;
                case 'lastmonth':
                    startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                    break;
                case 'custom':
                    startDate = new Date(customFrom);
                    endDate = new Date(customTo);
                    break;
                case 'all':
                default:
                    // Show all rows
                    allTableRows.forEach(row => {
                        row.style.display = '';
                    });
                    recalculateBalances();
                    return;
            }

            // Filter rows based on date range
            allTableRows.forEach(row => {
                const dateCell = row.cells[0]; // First column contains the date
                if (dateCell) {
                    const dateText = dateCell.textContent.trim();
                    if (dateText === 'N/A' || dateText === '') {
                        row.style.display = 'none';
                        return;
                    }

                    try {
                        // Parse the date (format: "MMM dd, yyyy")
                        const rowDate = new Date(dateText);
                        
                        if (isNaN(rowDate.getTime())) {
                            row.style.display = 'none';
                            return;
                        }

                        // Normalize dates for comparison (remove time component)
                        const normalizedRowDate = new Date(rowDate.getFullYear(), rowDate.getMonth(), rowDate.getDate());
                        const normalizedStartDate = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
                        const normalizedEndDate = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());

                        if (normalizedRowDate >= normalizedStartDate && normalizedRowDate <= normalizedEndDate) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    } catch (e) {
                        row.style.display = 'none';
                    }
                }
            });

            recalculateBalances();
            currentFilter = filterType;
        }

        function recalculateBalances() {
            // Find all visible rows and recalculate totals
            const visibleRows = allTableRows.filter(row => row.style.display !== 'none');
            
            let totalAmount = 0;
            let totalPaid = 0;
            let totalBalance = 0;

            visibleRows.forEach(row => {
                const amountCell = row.cells[4]; // Amount column
                const paidCell = row.cells[5];   // Paid column
                const balanceCell = row.cells[6]; // Balance column

                if (amountCell && paidCell && balanceCell) {
                    // Extract numeric values from formatted currency
                    const amount = parseFloat(amountCell.textContent.replace(/[₱,\s]/g, '')) || 0;
                    const paid = parseFloat(paidCell.textContent.replace(/[₱,\s()]/g, '')) || 0;
                    const balance = parseFloat(balanceCell.textContent.replace(/[₱,\s]/g, '')) || 0;

                    totalAmount += amount;
                    totalPaid += paid;
                    totalBalance += balance;
                }
            });

            // Update footer totals if they exist
            const footerRows = document.querySelectorAll('.table-footer tr');
            footerRows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                if (cells.length >= 2) {
                    const label = cells[0].textContent.trim();
                    if (label.includes('TOTAL AMOUNT')) {
                        cells[1].textContent = '₱' + totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    } else if (label.includes('TOTAL PAID')) {
                        cells[1].textContent = '₱' + totalPaid.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    } else if (label.includes('TOTAL AMOUNT DUE')) {
                        cells[1].textContent = '₱' + totalBalance.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            });
        }

        function exportToExcel() {
            // Create a new workbook
            const wb = XLSX.utils.book_new();

            // Customer information
            const customerName = '{{ $customer->Name ?? "Customer" }}';
            const customerCode = '{{ $customer->Customer ?? "N/A" }}';
            const customerContact = '{{ $customer->Telephone ?? "N/A" }}';
            const generatedDate = '{{ now()->format("Y-m-d H:i:s") }}';
            const generatedBy = '{{ $user->name ?? "System User" }}';
            const totalRecords = '{{ $totalRecords }}';

            // Create worksheet data array
            const wsData = [];

            // Add title
            wsData.push(['STATEMENT OF ACCOUNT']);
            wsData.push(['']); // Empty row

            // Add customer info
            wsData.push(['Customer:', customerName]);
            wsData.push(['Customer Code:', customerCode]);
            wsData.push(['Contact Number:', customerContact]);
            wsData.push(['Generated:', generatedDate]);
            wsData.push(['Generated By:', generatedBy]);
            wsData.push(['Total Records:', totalRecords]);
            wsData.push(['']); // Empty row

            // Get table headers
            const headerRow = document.querySelector('table thead tr');
            if (headerRow) {
                const headers = Array.from(headerRow.querySelectorAll('th')).map(th => th.textContent.trim());
                wsData.push(headers);
            }
            
            // Get visible table data
            const visibleRows = allTableRows.filter(row => row.style.display !== 'none');
            visibleRows.forEach(row => {
                const rowData = Array.from(row.cells).map(cell => cell.textContent.trim());
                wsData.push(rowData);
            });
            
            // Add totals from footer
            wsData.push(['']); // Empty row
            const footerRows = document.querySelectorAll('.table table-borderless tbody tr');
            footerRows.forEach(row => {
                const cells = row.querySelectorAll('th, td');
                if (cells.length >= 2) {
                    const label = cells[0].textContent.trim();
                    const value = cells[1].textContent.trim();
                    wsData.push([label, value]);
                }
            });
            
            // Create worksheet
            const ws = XLSX.utils.aoa_to_sheet(wsData);
            
            // Set column widths
            ws['!cols'] = [
                { wch: 12 }, // Date
                { wch: 15 }, // Reference #
                { wch: 15 }, // SO Number
                { wch: 20 }, // Description
                { wch: 10 }, // Amount
                { wch: 10 }, // Paid
                { wch: 10 }, // Balance
                { wch: 8 },  // Status
                { wch: 10 }  // Terms
            ];
            
            // Style the title row
            if (ws['A1']) {
                ws['A1'].s = {
                    font: { bold: true, sz: 16 },
                    alignment: { horizontal: 'center' }
                };
            }
            
            // Style the header row (find it dynamically)
            const headerRowIndex = wsData.findIndex(row => 
                Array.isArray(row) && row.some(cell => 
                    typeof cell === 'string' && 
                    (cell.includes('Date') || cell.includes('Reference') || cell.includes('Description'))
                )
            );
            
            if (headerRowIndex >= 0) {
                const headerRowNum = headerRowIndex + 1;
                for (let col = 0; col < 7; col++) {
                    const cellRef = XLSX.utils.encode_cell({ r: headerRowNum - 1, c: col });
                    if (ws[cellRef]) {
                        ws[cellRef].s = {
                            font: { bold: true },
                            fill: { fgColor: { rgb: "EEEEEE" } }
                        };
                    }
                }
            }
            
            // Add worksheet to workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Statement of Account');

            // Generate filename with current date
            const currentDate = new Date().toISOString().split('T')[0];
            const filename = `Customer_Statement_of_Account_${customerName.replace(/[^a-zA-Z0-9]/g, '_')}_${currentDate}.xlsx`;
            
            // Save the file
            XLSX.writeFile(wb, filename);
        }
    </script>
</html>
