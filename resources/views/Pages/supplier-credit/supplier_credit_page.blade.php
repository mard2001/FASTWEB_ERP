@extends('Layout.layout')

@section('html_title')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <title>Supplier Credit</title>
@endsection

@section('title_header')
    <x-header title="Supplier Credit" />
@endsection

@section('table')
    <div class="main-content buttons w-100 overflow-auto d-flex align-items-center px-2 py-2" style="font-size: 12px;">
        <div class="btn d-flex justify-content-around px-2 align-items-center me-1 actionBtn" id="csvDLBtn">
            <div class="btnImg me-2" id="dlImg">
            </div>
            <span>Download Report</span>
        </div>
    </div>

    <x-table id="supplierCreditTable">
        <x-slot:td>
            <td class="col">Supplier Code</td>
            <td class="col">Supplier Name</td>
            <td class="col">Total Credit</td>
            <td class="col">Paid</td>
            <td class="col">Balance</td>
            <td class="col">Credit Limit</td>
            <td class="col">Credit Balance</td>
        </x-slot:td>
    </x-table>
@endsection

@section('modal')
    <style>
        /* Force visibility and proper styling for DataTable */
        #supplierCreditTable {
            opacity: 1 !important;
            visibility: visible !important;
            display: table !important;
            width: 100% !important;
        }
        
        #supplierCreditTable thead {
            white-space: nowrap;
            opacity: 1 !important;
            visibility: visible !important;
            display: table-header-group !important;
        }
        
        #supplierCreditTable thead th {
            opacity: 1 !important;
            visibility: visible !important;
            display: table-cell !important;
            padding: 8px !important;
            background-color: #f8f9fa !important;
            border-left: none !important;
            border-right: none !important;
            border-top: 1px solid #dee2e6 !important;
            border-bottom: 1px solid #dee2e6 !important;
        }
        
        #supplierCreditTable tbody {
            opacity: 1 !important;
            visibility: visible !important;
            display: table-row-group !important;
        }
        
        #supplierCreditTable tbody tr {
            cursor: pointer;
            opacity: 1 !important;
            visibility: visible !important;
            display: table-row !important;
        }
        
        #supplierCreditTable tbody tr:hover {
            background-color: var(--hover-color, #f5f5f5) !important;
        }
        
        #supplierCreditTable tbody td {
            opacity: 1 !important;
            visibility: visible !important;
            display: table-cell !important;
            padding: 8px !important;
            border-left: none !important;
            border-right: none !important;
            border-top: none !important;
            border-bottom: 1px solid #dee2e6 !important;
            vertical-align: middle !important;
        }
        
        /* Override any Material Design Lite styling that might be hiding content */
        #supplierCreditTable.mdl-data-table tbody tr {
            height: auto !important;
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        #supplierCreditTable.mdl-data-table tbody td {
            height: auto !important;
            opacity: 1 !important;
            visibility: visible !important;
            color: #333 !important;
        }
        
        /* DataTable wrapper styling */
        .dataTables_wrapper {
            opacity: 1 !important;
            visibility: visible !important;
        }
        
        /* Force the table container to be visible */
        #dattableDiv {
            opacity: 1 !important;
        }
        
        /* Enhanced loading screen styling */
        .loadingScreen {
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(248,249,250,0.95) 100%);
            backdrop-filter: blur(2px);
            transition: opacity 0.3s ease-in-out;
        }

        .loadingScreen .loader {
            border-color: #5bc0de;
            border-bottom-color: #f7f7f7;
        }

        /* Enhanced modal loading animation */
        .spinner-border.text-primary {
            border-color: #5bc0de;
            border-right-color: transparent;
        }

        /* Smooth transitions for table visibility */
        #dattableDiv {
            transition: opacity 0.4s ease-in-out;
        }

        #dattableDiv.opacity-0 {
            opacity: 0 !important;
        }

        /* Allow loading screen to show initially */
        .loadingScreen {
            display: flex !important;
        }

        .transaction-table {
            font-size: 12px;
        }
        
        .transaction-table th {
            background-color: var(--primary-color, #0275d8);
            color: white;
            font-size: 11px;
            padding: 8px 6px;
        }
        
        .transaction-table td {
            padding: 6px;
            font-size: 11px;
        }

        .summary-card {
            background-color: var(--card-bg-color, #f8f9fa);
            border: 1px solid var(--border-color, #dee2e6);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .summary-label {
            font-weight: 600;
            color: var(--text-color, #333);
        }

        .summary-value {
            font-weight: 700;
        }

        .summary-value.positive {
            color: var(--success-color, #28a745);
        }

        .summary-value.negative {
            color: var(--danger-color, #dc3545);
        }

        .summary-value.warning {
            color: var(--warning-color, #ffc107);
        }

        /* Purchase Order Style for Modal */
        .supplierModalForm .row div div label {
            font-size: 0.53em;
            margin-bottom: 0;
            font-family: var(--body-font, "Inter", sans-serif);
            color: var(--text-color, #212529);
        }

        .supplierModalForm .row div div input,
        .supplierModalForm .row div div div {
            font-size: 0.68em;
            margin-bottom: 0;
            font-family: var(--body-font, "Inter", sans-serif);
            color: var(--text-color, #212529);
        }

        /* Make supplier modal data fields visible */
        .supplierModalForm .form-control {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            border-radius: 4px;
            min-height: 32px;
        }

        .supplierModalForm .form-control.border-0 {
            border: 1px solid #e9ecef !important;
            background-color: #f8f9fa;
        }

        .supplierSectionTitle {
            font-size: 0.68em;
            text-wrap: nowrap;
            color: var(--accent-color, #33336F);
            font-weight: 500;
            text-transform: uppercase;
            padding: 0 10px;
            font-family: var(--heading-font, "Inter", sans-serif);
        }

        /* Transaction History Display Styles */
        .table-info {
            background-color: rgba(13, 202, 240, 0.1) !important;
        }
    </style>

    <!-- Supplier Transactions Modal -->
    <div class="modal fade" id="supplierTransactionsModal" tabindex="-1" aria-labelledby="supplierTransactionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="supplierTransactionsModalLabel">
                        <span style='color: var(--primary-color, #0275d8);'>SUPPLIER TRANSACTIONS & CREDIT</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="supplierModalForm">
                        <!-- Supplier Information Section -->
                        <div id="supplierDetailsSection">
                            <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                                <div style="width:100%;"><hr></div>
                                <div class="supplierSectionTitle">SUPPLIER INFORMATION:</div>
                                <div style="width:100%;"><hr></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="">
                                        <label for="modalSupplierCode" class="form-label">SUPPLIER CODE</label>
                                        <input type="text" disabled id="modalSupplierCode" name="modalSupplierCode" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="">
                                        <label for="modalSupplierName" class="form-label">SUPPLIER NAME</label>
                                        <input type="text" disabled id="modalSupplierName" name="modalSupplierName" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="">
                                        <label for="modalContactPerson" class="form-label">CONTACT PERSON</label>
                                        <input type="text" disabled id="modalContactPerson" name="modalContactPerson" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="">
                                        <label for="modalContactNo" class="form-label">CONTACT NUMBER</label>
                                        <input type="text" disabled id="modalContactNo" name="modalContactNo" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Credit Summary Section -->
                        <div id="creditSummarySection">
                            <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                                <div style="width:100%;"><hr></div>
                                <div class="supplierSectionTitle">CREDIT SUMMARY:</div>
                                <div style="width:100%;"><hr></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="">
                                        <label for="totalDebt" class="form-label">TOTAL DEBT</label>
                                        <input type="text" disabled id="totalDebt" name="totalDebt" class="form-control text-danger fw-bold" readonly>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="">
                                        <label for="totalPaid" class="form-label">TOTAL PAID</label>
                                        <input type="text" disabled id="totalPaid" name="totalPaid" class="form-control text-success fw-bold" readonly>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="">
                                        <label for="balanceOwed" class="form-label">BALANCE OWED</label>
                                        <input type="text" disabled id="balanceOwed" name="balanceOwed" class="form-control fw-bold" readonly>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="">
                                        <label for="creditMemoBalance" class="form-label">CREDIT MEMO</label>
                                        <input type="text" disabled id="creditMemoBalance" name="creditMemoBalance" class="form-control text-info fw-bold" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Table Section -->
                    <div id="transactionHistorySection">
                        <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                            <div style="width:100%;"><hr></div>
                            <div class="supplierSectionTitle">TRANSACTION HISTORY:</div>
                            <div style="width:100%;"><hr></div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-striped transaction-table" id="transactionsTable">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>RR No</th>
                                            <th>Amount</th>
                                            <th>Paid</th>
                                            <th>Balance</th>
                                            <th>Status</th>
                                            <th>Terms</th>
                                        </tr>
                                    </thead>
                                    <tbody id="transactionsTableBody">
                                        <!-- Transactions will be populated here -->
                                    </tbody>
                                </table>
                            </div>
                                <div id="noTransactionsMessage" class="text-center py-4" style="display: none;">
                                    <p class="text-muted">No transactions found for this supplier.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="printStatementBtn">
                        <i class="mdi mdi-printer me-2"></i>View Statement of Account
                    </button>
                    <button type="button" class="btn btn-success" id="printCounterReceiptBtn">
                        <i class="mdi mdi-receipt me-2"></i>View Counter Receipt
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('pagejs')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js" integrity="sha512-dfX5uYVXzyU8+KHqj8bjo7UkOdg18PaOtpa48djpNbZHwExddghZ+ZmzWT06R5v6NSk3ZUfsH6FNEDepLx9hPQ==" crossorigin="anonymous" referreferrer="no-referrer"></script>
    <script src="{{ asset('assets/js/supplier-credit/supplier-credit.js') }}"></script>
@endsection