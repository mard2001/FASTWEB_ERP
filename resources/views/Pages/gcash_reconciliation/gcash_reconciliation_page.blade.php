@extends('Layout.layout')

@section('html_title')
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://unpkg.com/read-excel-file@5.x/bundle/read-excel-file.min.js"></script>
    <title>Gcash Reconciliation</title>
@endsection

@section('title_header')
    <x-header title="Gcash Reconciliation" />
@endsection

@section('table')

    <x-contentButtonDiv downloadFunc="true" uploadFunc="false"></x-contentButtonDiv>

    <x-table id="gcashReconTable">
        <x-slot:td>
            <td class="col">Account Name</td>
            <td class="col">Account Number</td>
            <td class="col">Beginning Balance</td>
            <td class="col">Total Inflows</td>
            <td class="col">Total Outflows</td>
            <td class="col">Available Balance</td>
            <td class="col">Last Reconciliation</td>
            <td class="col">Action</td>
        </x-slot:td>
    </x-table>
@endsection

@section('modal')
    <style>
        /* Override DataTables search input padding */
        div.dt-container div.dt-search input {
            margin-left: 0.5em;
            width: auto;
            background-color: whitesmoke;
            padding: 5px 10px;
            border-radius: 7px;
        }

        div.dt-container div.dt-layout-cell.dt-start {
            text-align: left;
            font-size: 11px;
        }

        div.dt-container div.dt-layout-cell.dt-end{
            text-align: right;
            font-size: 10px;
        }

        /* Gcash Reconciliation Modal Styling - matching Bank Reconciliation design */
        .gcashModalForm .row div div label {
            font-size: 0.53em;
            margin-bottom: 0;
            font-family: var(--body-font, "Inter", sans-serif);
            color: var(--text-color, #212529);
        }

        .gcashModalForm .row div div input,
        .gcashModalForm .row div div div {
            font-size: 0.68em;
            margin-bottom: 0;
            font-family: var(--body-font, "Inter", sans-serif);
            color: var(--text-color, #212529);
        }

        .gcashModalForm .form-control {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 8px 12px;
            border-radius: 4px;
            min-height: 32px;
        }

        .gcashModalForm .form-control.border-0 {
            border: 1px solid #e9ecef !important;
            background-color: #f8f9fa;
        }

        .gcashSectionTitle {
            font-size: 0.68em;
            text-wrap: nowrap;
            color: var(--accent-color, #33336F);
            font-weight: 500;
            text-transform: uppercase;
            padding: 0 10px;
            font-family: var(--heading-font, "Inter", sans-serif);
        }

        .transaction-table {
            font-size: 12px;
            white-space: nowrap;
        }
        
        .transaction-table th {
            background-color: var(--primary-color, #0275d8);
            color: white;
            font-size: 11px;
            padding: 8px 6px;
            white-space: nowrap;
        }
        
        .transaction-table td {
            padding: 6px;
            font-size: 11px;
            white-space: nowrap;
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>

    <!-- Beginning Balance Modal -->
    <x-mainModal mainModalTitle="beginningBalanceModal" modalDialogClass="" modalHeaderTitle="<span style='color: var(--primary-color, #0275d8);'>SET BEGINNING BALANCE</span>" modalSubHeaderTitle="Set the starting balance for this Gcash account">
        <x-slot:form_fields>
            <div id="beginningBalanceFields">
                <input type="hidden" id="BalanceGcashID">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="fw-bold">Gcash Account: <span id="balanceAccountName" class="text-primary"></span></label>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="BalanceReconciliationDate">Reconciliation Date <span class="text-danger">*</span></label>
                            <input type="date" id="BalanceReconciliationDate" name="BalanceReconciliationDate" class="form-control bg-white" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="BalanceBeginningBalance">Beginning Balance <span class="text-danger">*</span></label>
                            <input type="text" id="BalanceBeginningBalance" name="BalanceBeginningBalance" class="form-control bg-white" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="BalanceNotes">Notes</label>
                            <textarea id="BalanceNotes" name="BalanceNotes" class="form-control bg-white" rows="3" maxlength="500" placeholder="Optional notes"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </x-slot:form_fields>
        <x-slot:modalFooterBtns>
            <div></div>
            <div>
                <button type="button" class="btn btn-sm btn-primary text-white" id="saveBeginningBalanceBtn">Save Balance</button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </x-slot:modalFooterBtns>
    </x-mainModal>

    <!-- Gcash Details & Transaction History Modal -->
    <x-mainModal mainModalTitle="gcashDetailsModal" modalDialogClass="modal-xl" modalHeaderTitle="<span style='color: var(--primary-color, #0275d8);'>GCASH RECONCILIATION DETAILS</span>" modalSubHeaderTitle="">
        <x-slot:form_fields>
            <div class="gcashModalForm">
                <input type="hidden" id="DetailsGcashID">
                
                <!-- Gcash Information Section -->
                <div id="gcashDetailsSection">
                    <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                        <div style="width:100%;"><hr></div>
                        <div class="gcashSectionTitle">GCASH INFORMATION:</div>
                        <div style="width:100%;"><hr></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-12 col-sm-6 col-md-6">
                            <div class="">
                                <label for="detailsAccountName" class="form-label">ACCOUNT NAME</label>
                                <input type="text" disabled id="detailsAccountName" name="detailsAccountName" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6">
                            <div class="">
                                <label for="detailsAccountNumber" class="form-label">ACCOUNT NUMBER</label>
                                <input type="text" disabled id="detailsAccountNumber" name="detailsAccountNumber" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reconciliation Summary Section -->
                <div id="reconciliationSummarySection">
                    <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                        <div style="width:100%;"><hr></div>
                        <div class="gcashSectionTitle">RECONCILIATION SUMMARY:</div>
                        <div style="width:100%;"><hr></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="">
                                <label for="summaryBeginningBalance" class="form-label">BEGINNING BALANCE</label>
                                <input type="text" disabled id="summaryBeginningBalance" name="summaryBeginningBalance" class="form-control text-primary fw-bold" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="">
                                <label for="summaryTotalInflows" class="form-label">TOTAL INFLOWS</label>
                                <input type="text" disabled id="summaryTotalInflows" name="summaryTotalInflows" class="form-control text-success fw-bold" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="">
                                <label for="summaryTotalOutflows" class="form-label">TOTAL OUTFLOWS</label>
                                <input type="text" disabled id="summaryTotalOutflows" name="summaryTotalOutflows" class="form-control text-danger fw-bold" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="">
                                <label for="summaryAvailableBalance" class="form-label">AVAILABLE BALANCE</label>
                                <input type="text" disabled id="summaryAvailableBalance" name="summaryAvailableBalance" class="form-control text-info fw-bold" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History Section -->
            <div id="transactionHistorySection">
                <div class="d-flex align-items-center" style="margin-bottom:-10px;">
                    <div style="width:100%;"><hr></div>
                    <div class="gcashSectionTitle">TRANSACTION HISTORY:</div>
                    <div style="width:100%;"><hr></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped transaction-table" id="transactionHistoryTable" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Supplier/Customer</th>
                                <th>AP/AR Reference</th>
                                <th>Reference</th>
                                <th>Payment Type</th>
                                <th>Withdrawal</th>
                                <th>Deposit</th>
                                <th>Balance</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="transactionHistoryTableBody">
                            <!-- Transactions will be populated here -->
                        </tbody>
                    </table>
                </div>
                <div id="noTransactionsMessage" class="text-center py-4" style="display: none;">
                    <p class="text-muted">No transactions found for this Gcash account.</p>
                </div>
            </div>
        </x-slot:form_fields>
        <x-slot:modalFooterBtns>
            <div>
                <button type="button" class="btn btn-sm btn-success" id="manualDepositBtn">
                    <i class="mdi mdi-plus-circle"></i> Manual Deposit
                </button>
                <button type="button" class="btn btn-sm btn-danger" id="manualWithdrawBtn">
                    <i class="mdi mdi-minus-circle"></i> Manual Withdraw
                </button>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </x-slot:modalFooterBtns>
    </x-mainModal>

    <!-- Manual Transaction Modal (Deposit/Withdraw) -->
    <x-mainModal mainModalTitle="manualTransactionModal" modalDialogClass="" modalHeaderTitle="<span style='color: var(--primary-color, #0275d8);' id='manualTransactionTitle'>MANUAL TRANSACTION</span>" modalSubHeaderTitle="">
        <x-slot:form_fields>
            <div id="manualTransactionFields">
                <input type="hidden" id="ManualGcashID">
                <input type="hidden" id="ManualTransactionType">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="fw-bold">Gcash Account: <span id="manualAccountName" class="text-primary"></span></label>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="ManualTransactionDate">Transaction Date <span class="text-danger">*</span></label>
                            <input type="date" id="ManualTransactionDate" name="ManualTransactionDate" class="form-control bg-white" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-3">
                            <label for="ManualAmount" id="ManualAmountLabel">Amount <span class="text-danger">*</span></label>
                            <input type="text" id="ManualAmount" name="ManualAmount" class="form-control bg-white" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="ManualReference">Reference Number</label>
                            <input type="text" id="ManualReference" name="ManualReference" class="form-control bg-white" maxlength="100" placeholder="Optional reference">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="ManualRemarks">Remarks <span class="text-danger">*</span></label>
                            <textarea id="ManualRemarks" name="ManualRemarks" class="form-control bg-white" rows="3" maxlength="500" placeholder="Reason for manual transaction" required></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </x-slot:form_fields>
        <x-slot:modalFooterBtns>
            <div></div>
            <div>
                <button type="button" class="btn btn-sm btn-primary text-white" id="saveManualTransactionBtn">Save Transaction</button>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </x-slot:modalFooterBtns>
    </x-mainModal>
@endsection

@section('pagejs')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

    <script type="text/javascript" src="{{ asset('assets/js/vendor/virtual-select.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.4.1/papaparse.min.js" integrity="sha512-dfX5uYVXzyU8+KHqj8bjo7UkOdg18PaOtpa48djpNbZHwExddghZ+ZmzWT06R5v6NSk3ZUfsH6FNEDepLx9hPQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('assets/js/gcash_reconciliation/gcash_reconciliation.js') }}"></script>
@endsection